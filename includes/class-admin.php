<?php
/**
 * Admin helpers and importer.
 *
 * @package StephaneVideoLibrary
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class STVL_Admin {

	/**
	 * Supported open behaviors.
	 *
	 * @var string[]
	 */
	private $open_behaviors = array( 'modal', 'new_tab', 'same_tab', 'inline_embed' );

	/**
	 * Adds admin submenu.
	 *
	 * @return void
	 */
	public function menu() {
		add_submenu_page(
			'edit.php?post_type=' . STVL_CPT,
			__( 'Importer', 'stephane-video-library' ),
			__( 'Importer', 'stephane-video-library' ),
			'manage_options',
			'stvl-import',
			array( $this, 'render_import_page' )
		);
	}

	/**
	 * Renders import page.
	 *
	 * @return void
	 */
	public function render_import_page() {
		$message = '';

		if ( isset( $_POST['stvl_import_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['stvl_import_nonce'] ) ), 'stvl_import_videos' ) && current_user_can( 'manage_options' ) ) {
			$data   = isset( $_POST['stvl_import_payload'] ) ? wp_unslash( $_POST['stvl_import_payload'] ) : '';
			$status = isset( $_POST['stvl_import_status'] ) ? sanitize_key( $_POST['stvl_import_status'] ) : 'draft';
			$result = $this->import_json( $data, $status );
			$message = sprintf( __( '%1$d imported, %2$d skipped.', 'stephane-video-library' ), $result['imported'], $result['skipped'] );
		}

		$seed_path = STVL_PATH . 'data/seed-videos.json';
		$seed_data = file_exists( $seed_path ) ? file_get_contents( $seed_path ) : '[]';
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Importer des videos', 'stephane-video-library' ); ?></h1>
			<?php if ( $message ) : ?>
				<div class="notice notice-success"><p><?php echo esc_html( $message ); ?></p></div>
			<?php endif; ?>
			<form method="post">
				<?php wp_nonce_field( 'stvl_import_videos', 'stvl_import_nonce' ); ?>
				<p><?php esc_html_e( 'Import starter videos from JSON. Existing titles are skipped.', 'stephane-video-library' ); ?></p>
				<p>
					<label for="stvl_import_status"><?php esc_html_e( 'Imported post status', 'stephane-video-library' ); ?></label><br />
					<select name="stvl_import_status" id="stvl_import_status">
						<option value="draft"><?php esc_html_e( 'Draft', 'stephane-video-library' ); ?></option>
						<option value="publish"><?php esc_html_e( 'Publish', 'stephane-video-library' ); ?></option>
					</select>
				</p>
				<textarea class="large-text code" rows="22" name="stvl_import_payload"><?php echo esc_textarea( (string) $seed_data ); ?></textarea>
				<p><button type="submit" class="button button-primary"><?php esc_html_e( 'Import videos', 'stephane-video-library' ); ?></button></p>
			</form>
		</div>
		<?php
	}

	/**
	 * Imports JSON payload.
	 *
	 * @param string $payload JSON payload.
	 * @param string $status Post status.
	 * @return array
	 */
	private function import_json( $payload, $status ) {
		$items = json_decode( $payload, true );

		if ( ! is_array( $items ) ) {
			return array( 'imported' => 0, 'skipped' => 0 );
		}

		$imported = 0;
		$skipped  = 0;

		foreach ( $items as $item ) {
			$title = isset( $item['title'] ) ? sanitize_text_field( $item['title'] ) : '';
			$url   = isset( $item['url'] ) ? esc_url_raw( $item['url'] ) : '';
			$hash  = md5( strtolower( $title . '|' . $url ) );

			$existing = get_posts(
				array(
					'post_type'      => STVL_CPT,
					'post_status'    => array( 'publish', 'draft', 'pending', 'private', 'future' ),
					'posts_per_page' => 1,
					'fields'         => 'ids',
					'meta_query'     => array(
						array(
							'key'   => '_stvl_import_hash',
							'value' => $hash,
						),
					),
				)
			);

			if ( empty( $title ) || ! empty( $existing ) ) {
				++$skipped;
				continue;
			}

			$post_id = wp_insert_post(
				array(
					'post_type'    => STVL_CPT,
					'post_title'   => $title,
					'post_excerpt' => isset( $item['description'] ) ? sanitize_textarea_field( $item['description'] ) : '',
					'post_status'  => 'publish' === $status ? 'publish' : 'draft',
					'menu_order'   => isset( $item['order'] ) ? absint( $item['order'] ) : 0,
				)
			);

			if ( is_wp_error( $post_id ) || ! $post_id ) {
				++$skipped;
				continue;
			}

			if ( ! empty( $item['category'] ) ) {
				$this->assign_terms( $post_id, STVL_TAX_CATEGORY, $item['category'] );
			}

			if ( ! empty( $item['topic'] ) ) {
				$this->assign_terms( $post_id, STVL_TAX_TOPIC, $item['topic'] );
			}

			$provider = isset( $item['provider'] ) ? sanitize_key( $item['provider'] ) : '';
			$provider = $provider ? $provider : STVL_Helpers::get_video_provider( $url );
			$video_id = isset( $item['video_id'] ) ? sanitize_text_field( $item['video_id'] ) : '';

			if ( empty( $video_id ) && 'youtube' === $provider ) {
				$video_id = STVL_Helpers::extract_youtube_id( $url );
			}

			if ( empty( $video_id ) && 'vimeo' === $provider ) {
				$video_id = STVL_Helpers::extract_vimeo_id( $url );
			}

			update_post_meta( $post_id, '_stvl_video_url', $url );
			update_post_meta( $post_id, '_stvl_video_provider', $provider );
			update_post_meta( $post_id, '_stvl_video_id', $video_id );
			update_post_meta( $post_id, '_stvl_video_duration', isset( $item['duration'] ) ? sanitize_text_field( $item['duration'] ) : '' );
			update_post_meta( $post_id, '_stvl_video_date', $this->normalize_date( isset( $item['publication_date'] ) ? $item['publication_date'] : '' ) );
			update_post_meta( $post_id, '_stvl_video_source_name', isset( $item['source'] ) ? sanitize_text_field( $item['source'] ) : '' );
			update_post_meta( $post_id, '_stvl_video_button_text', isset( $item['button_text'] ) ? sanitize_text_field( $item['button_text'] ) : '' );
			update_post_meta( $post_id, '_stvl_video_external_url', isset( $item['external_cta_url'] ) ? esc_url_raw( $item['external_cta_url'] ) : $url );
			update_post_meta( $post_id, '_stvl_video_open_behavior', $this->sanitize_open_behavior( isset( $item['open_behavior'] ) ? $item['open_behavior'] : 'modal' ) );
			update_post_meta( $post_id, '_stvl_video_short_description', isset( $item['short_card_description'] ) ? sanitize_textarea_field( $item['short_card_description'] ) : '' );
			update_post_meta( $post_id, '_stvl_video_transcript', isset( $item['transcript_notes'] ) ? sanitize_textarea_field( $item['transcript_notes'] ) : '' );
			update_post_meta( $post_id, '_stvl_video_featured', ! empty( $item['featured'] ) ? 1 : 0 );
			update_post_meta( $post_id, '_stvl_video_priority', isset( $item['priority'] ) ? absint( $item['priority'] ) : ( isset( $item['order'] ) ? absint( $item['order'] ) : 0 ) );
			update_post_meta( $post_id, '_stvl_import_hash', $hash );
			++$imported;
		}

		return array(
			'imported' => $imported,
			'skipped'  => $skipped,
		);
	}

	/**
	 * Assigns taxonomy terms, creating them when missing.
	 *
	 * @param int          $post_id Post ID.
	 * @param string       $taxonomy Taxonomy key.
	 * @param string|array $raw_terms Raw terms.
	 * @return void
	 */
	private function assign_terms( $post_id, $taxonomy, $raw_terms ) {
		$raw_terms = is_array( $raw_terms ) ? $raw_terms : explode( '|', (string) $raw_terms );
		$term_ids  = array();

		foreach ( $raw_terms as $raw_term ) {
			$term_name = sanitize_text_field( trim( (string) $raw_term ) );

			if ( '' === $term_name ) {
				continue;
			}

			$term = term_exists( $term_name, $taxonomy );

			if ( ! $term ) {
				$term = wp_insert_term( $term_name, $taxonomy );
			}

			if ( is_wp_error( $term ) ) {
				continue;
			}

			$term_ids[] = (int) $term['term_id'];
		}

		if ( $term_ids ) {
			wp_set_post_terms( $post_id, $term_ids, $taxonomy, false );
		}
	}

	/**
	 * Normalizes imported dates to Y-m-d for the date field.
	 *
	 * @param string $raw_date Raw imported date.
	 * @return string
	 */
	private function normalize_date( $raw_date ) {
		$raw_date = trim( (string) $raw_date );

		if ( '' === $raw_date ) {
			return '';
		}

		$formats = array( 'd/m/Y', 'Y-m-d', 'd-m-Y' );

		foreach ( $formats as $format ) {
			$date = DateTime::createFromFormat( $format, $raw_date );
			if ( $date instanceof DateTime ) {
				return $date->format( 'Y-m-d' );
			}
		}

		return sanitize_text_field( $raw_date );
	}

	/**
	 * Sanitizes open behavior.
	 *
	 * @param string $behavior Behavior.
	 * @return string
	 */
	private function sanitize_open_behavior( $behavior ) {
		$behavior = sanitize_key( (string) $behavior );

		if ( in_array( $behavior, $this->open_behaviors, true ) ) {
			return $behavior;
		}

		return 'modal';
	}
}
