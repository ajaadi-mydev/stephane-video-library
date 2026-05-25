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
				$term = term_exists( $item['category'], STVL_TAX_CATEGORY );
				if ( ! $term ) {
					$term = wp_insert_term( sanitize_text_field( $item['category'] ), STVL_TAX_CATEGORY );
				}
				if ( ! is_wp_error( $term ) ) {
					wp_set_post_terms( $post_id, array( (int) $term['term_id'] ), STVL_TAX_CATEGORY, false );
				}
			}

			update_post_meta( $post_id, '_stvl_video_url', $url );
			update_post_meta( $post_id, '_stvl_video_duration', isset( $item['duration'] ) ? sanitize_text_field( $item['duration'] ) : '' );
			update_post_meta( $post_id, '_stvl_video_source_name', isset( $item['source'] ) ? sanitize_text_field( $item['source'] ) : '' );
			update_post_meta( $post_id, '_stvl_video_priority', isset( $item['order'] ) ? absint( $item['order'] ) : 0 );
			update_post_meta( $post_id, '_stvl_import_hash', $hash );
			++$imported;
		}

		return array(
			'imported' => $imported,
			'skipped'  => $skipped,
		);
	}
}
