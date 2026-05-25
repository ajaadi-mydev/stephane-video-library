<?php
/**
 * Meta registration and edit screen.
 *
 * @package StephaneVideoLibrary
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class STVL_Meta_Fields {

	/**
	 * Meta schema.
	 *
	 * @var array
	 */
	private $fields = array();

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->fields = array(
			'_stvl_video_url'               => array( 'type' => 'string', 'sanitize_callback' => 'esc_url_raw' ),
			'_stvl_video_provider'          => array( 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ),
			'_stvl_video_id'                => array( 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ),
			'_stvl_video_duration'          => array( 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ),
			'_stvl_video_date'              => array( 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ),
			'_stvl_video_source_name'       => array( 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ),
			'_stvl_video_button_text'       => array( 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ),
			'_stvl_video_custom_thumbnail'  => array( 'type' => 'integer', 'sanitize_callback' => 'absint' ),
			'_stvl_video_featured'          => array( 'type' => 'boolean', 'sanitize_callback' => 'rest_sanitize_boolean' ),
			'_stvl_video_external_url'      => array( 'type' => 'string', 'sanitize_callback' => 'esc_url_raw' ),
			'_stvl_video_open_behavior'     => array( 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ),
			'_stvl_video_short_description' => array( 'type' => 'string', 'sanitize_callback' => 'sanitize_textarea_field' ),
			'_stvl_video_transcript'        => array( 'type' => 'string', 'sanitize_callback' => 'sanitize_textarea_field' ),
			'_stvl_video_priority'          => array( 'type' => 'integer', 'sanitize_callback' => 'absint' ),
		);
	}

	/**
	 * Registers meta.
	 *
	 * @return void
	 */
	public function register_meta() {
		foreach ( $this->fields as $key => $args ) {
			register_post_meta(
				STVL_CPT,
				$key,
				array(
					'type'              => $args['type'],
					'single'            => true,
					'show_in_rest'      => true,
					'sanitize_callback' => $args['sanitize_callback'],
					'auth_callback'     => static function() {
						return current_user_can( 'edit_posts' );
					},
				)
			);
		}
	}

	/**
	 * Adds meta box.
	 *
	 * @return void
	 */
	public function add_meta_box() {
		add_meta_box(
			'stvl-video-details',
			__( 'Details de la video', 'stephane-video-library' ),
			array( $this, 'render_meta_box' ),
			STVL_CPT,
			'normal',
			'high'
		);
	}

	/**
	 * Renders meta box.
	 *
	 * @param WP_Post $post Post.
	 * @return void
	 */
	public function render_meta_box( $post ) {
		wp_nonce_field( 'stvl_save_video_meta', 'stvl_video_meta_nonce' );

		$fields = array(
			'video_url'              => get_post_meta( $post->ID, '_stvl_video_url', true ),
			'video_provider'         => get_post_meta( $post->ID, '_stvl_video_provider', true ),
			'video_id'               => get_post_meta( $post->ID, '_stvl_video_id', true ),
			'video_duration'         => get_post_meta( $post->ID, '_stvl_video_duration', true ),
			'video_source_name'      => get_post_meta( $post->ID, '_stvl_video_source_name', true ),
			'video_date'             => get_post_meta( $post->ID, '_stvl_video_date', true ),
			'video_button_text'      => get_post_meta( $post->ID, '_stvl_video_button_text', true ),
			'video_external_url'     => get_post_meta( $post->ID, '_stvl_video_external_url', true ),
			'video_open_behavior'    => get_post_meta( $post->ID, '_stvl_video_open_behavior', true ),
			'video_custom_thumbnail' => (int) get_post_meta( $post->ID, '_stvl_video_custom_thumbnail', true ),
			'video_featured'         => get_post_meta( $post->ID, '_stvl_video_featured', true ),
			'video_priority'         => get_post_meta( $post->ID, '_stvl_video_priority', true ),
			'video_short_description'=> get_post_meta( $post->ID, '_stvl_video_short_description', true ),
			'video_transcript'       => get_post_meta( $post->ID, '_stvl_video_transcript', true ),
		);

		$thumbnail = $fields['video_custom_thumbnail'] ? wp_get_attachment_image_url( $fields['video_custom_thumbnail'], 'medium' ) : STVL_Helpers::get_thumbnail_url( $post->ID );
		?>
		<div class="stvl-admin-grid">
			<p>
				<label for="stvl_video_url"><strong><?php esc_html_e( 'Video URL', 'stephane-video-library' ); ?></strong></label><br />
				<input type="url" class="widefat" id="stvl_video_url" name="stvl_video_url" value="<?php echo esc_attr( $fields['video_url'] ); ?>" />
				<small><?php esc_html_e( 'For YouTube, paste the normal video URL. The plugin will generate the embed automatically.', 'stephane-video-library' ); ?></small>
			</p>
			<p>
				<label for="stvl_video_provider"><strong><?php esc_html_e( 'Provider', 'stephane-video-library' ); ?></strong></label><br />
				<select class="widefat" id="stvl_video_provider" name="stvl_video_provider">
					<?php foreach ( array( 'youtube', 'vimeo', 'external', 'self_hosted', 'unknown' ) as $provider ) : ?>
						<option value="<?php echo esc_attr( $provider ); ?>" <?php selected( $fields['video_provider'], $provider ); ?>><?php echo esc_html( ucfirst( str_replace( '_', ' ', $provider ) ) ); ?></option>
					<?php endforeach; ?>
				</select>
			</p>
			<p>
				<label for="stvl_video_id"><strong><?php esc_html_e( 'Video ID', 'stephane-video-library' ); ?></strong></label><br />
				<input type="text" class="widefat" id="stvl_video_id" name="stvl_video_id" value="<?php echo esc_attr( $fields['video_id'] ); ?>" />
			</p>
			<p>
				<label for="stvl_video_duration"><strong><?php esc_html_e( 'Duration', 'stephane-video-library' ); ?></strong></label><br />
				<input type="text" class="widefat" id="stvl_video_duration" name="stvl_video_duration" value="<?php echo esc_attr( $fields['video_duration'] ); ?>" placeholder="1:04:22" />
			</p>
			<p>
				<label for="stvl_video_source_name"><strong><?php esc_html_e( 'Source / Media name', 'stephane-video-library' ); ?></strong></label><br />
				<input type="text" class="widefat" id="stvl_video_source_name" name="stvl_video_source_name" value="<?php echo esc_attr( $fields['video_source_name'] ); ?>" />
			</p>
			<p>
				<label for="stvl_video_date"><strong><?php esc_html_e( 'Publication date', 'stephane-video-library' ); ?></strong></label><br />
				<input type="date" class="widefat" id="stvl_video_date" name="stvl_video_date" value="<?php echo esc_attr( $fields['video_date'] ); ?>" />
			</p>
			<p>
				<label for="stvl_video_button_text"><strong><?php esc_html_e( 'Button text', 'stephane-video-library' ); ?></strong></label><br />
				<input type="text" class="widefat" id="stvl_video_button_text" name="stvl_video_button_text" value="<?php echo esc_attr( $fields['video_button_text'] ); ?>" />
			</p>
			<p>
				<label for="stvl_video_open_behavior"><strong><?php esc_html_e( 'Open behavior', 'stephane-video-library' ); ?></strong></label><br />
				<select class="widefat" id="stvl_video_open_behavior" name="stvl_video_open_behavior">
					<?php foreach ( array( 'modal', 'new_tab', 'same_tab', 'inline_embed' ) as $behavior ) : ?>
						<option value="<?php echo esc_attr( $behavior ); ?>" <?php selected( $fields['video_open_behavior'], $behavior ); ?>><?php echo esc_html( ucfirst( str_replace( '_', ' ', $behavior ) ) ); ?></option>
					<?php endforeach; ?>
				</select>
			</p>
			<p>
				<label for="stvl_video_external_url"><strong><?php esc_html_e( 'External CTA URL', 'stephane-video-library' ); ?></strong></label><br />
				<input type="url" class="widefat" id="stvl_video_external_url" name="stvl_video_external_url" value="<?php echo esc_attr( $fields['video_external_url'] ); ?>" />
			</p>
			<p>
				<label for="stvl_video_priority"><strong><?php esc_html_e( 'Priority', 'stephane-video-library' ); ?></strong></label><br />
				<input type="number" class="widefat" id="stvl_video_priority" name="stvl_video_priority" value="<?php echo esc_attr( (string) $fields['video_priority'] ); ?>" />
			</p>
			<p>
				<label>
					<input type="checkbox" name="stvl_video_featured" value="1" <?php checked( ! empty( $fields['video_featured'] ) ); ?> />
					<?php esc_html_e( 'Featured video', 'stephane-video-library' ); ?>
				</label>
			</p>
			<p class="stvl-admin-thumb-field">
				<label for="stvl_video_custom_thumbnail"><strong><?php esc_html_e( 'Custom thumbnail', 'stephane-video-library' ); ?></strong></label><br />
				<input type="hidden" id="stvl_video_custom_thumbnail" name="stvl_video_custom_thumbnail" value="<?php echo esc_attr( (string) $fields['video_custom_thumbnail'] ); ?>" />
				<button type="button" class="button stvl-select-image"><?php esc_html_e( 'Choose image', 'stephane-video-library' ); ?></button>
				<button type="button" class="button stvl-remove-image"><?php esc_html_e( 'Remove', 'stephane-video-library' ); ?></button>
				<div class="stvl-image-preview"><?php if ( $thumbnail ) : ?><img src="<?php echo esc_url( $thumbnail ); ?>" alt="" /><?php endif; ?></div>
			</p>
			<p>
				<label for="stvl_video_short_description"><strong><?php esc_html_e( 'Short card description', 'stephane-video-library' ); ?></strong></label><br />
				<textarea class="widefat" rows="4" id="stvl_video_short_description" name="stvl_video_short_description"><?php echo esc_textarea( $fields['video_short_description'] ); ?></textarea>
			</p>
			<p>
				<label for="stvl_video_transcript"><strong><?php esc_html_e( 'Transcript / Notes', 'stephane-video-library' ); ?></strong></label><br />
				<textarea class="widefat" rows="5" id="stvl_video_transcript" name="stvl_video_transcript"><?php echo esc_textarea( $fields['video_transcript'] ); ?></textarea>
			</p>
		</div>
		<?php
	}

	/**
	 * Saves meta box.
	 *
	 * @param int $post_id Post ID.
	 * @return void
	 */
	public function save( $post_id ) {
		if ( ! isset( $_POST['stvl_video_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['stvl_video_meta_nonce'] ) ), 'stvl_save_video_meta' ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( STVL_CPT !== get_post_type( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$url      = isset( $_POST['stvl_video_url'] ) ? esc_url_raw( wp_unslash( $_POST['stvl_video_url'] ) ) : '';
		$provider = isset( $_POST['stvl_video_provider'] ) ? sanitize_text_field( wp_unslash( $_POST['stvl_video_provider'] ) ) : '';
		$video_id = isset( $_POST['stvl_video_id'] ) ? sanitize_text_field( wp_unslash( $_POST['stvl_video_id'] ) ) : '';

		if ( $url ) {
			$detected_provider = STVL_Helpers::get_video_provider( $url );
			if ( empty( $provider ) || 'unknown' === $provider ) {
				$provider = $detected_provider;
			}
			if ( empty( $video_id ) && 'youtube' === $provider ) {
				$video_id = STVL_Helpers::extract_youtube_id( $url );
			}
			if ( empty( $video_id ) && 'vimeo' === $provider ) {
				$video_id = STVL_Helpers::extract_vimeo_id( $url );
			}
		}

		$map = array(
			'_stvl_video_url'               => $url,
			'_stvl_video_provider'          => $provider,
			'_stvl_video_id'                => $video_id,
			'_stvl_video_duration'          => isset( $_POST['stvl_video_duration'] ) ? sanitize_text_field( wp_unslash( $_POST['stvl_video_duration'] ) ) : '',
			'_stvl_video_date'              => isset( $_POST['stvl_video_date'] ) ? sanitize_text_field( wp_unslash( $_POST['stvl_video_date'] ) ) : '',
			'_stvl_video_source_name'       => isset( $_POST['stvl_video_source_name'] ) ? sanitize_text_field( wp_unslash( $_POST['stvl_video_source_name'] ) ) : '',
			'_stvl_video_button_text'       => isset( $_POST['stvl_video_button_text'] ) ? sanitize_text_field( wp_unslash( $_POST['stvl_video_button_text'] ) ) : '',
			'_stvl_video_custom_thumbnail'  => isset( $_POST['stvl_video_custom_thumbnail'] ) ? absint( $_POST['stvl_video_custom_thumbnail'] ) : 0,
			'_stvl_video_featured'          => isset( $_POST['stvl_video_featured'] ) ? 1 : 0,
			'_stvl_video_external_url'      => isset( $_POST['stvl_video_external_url'] ) ? esc_url_raw( wp_unslash( $_POST['stvl_video_external_url'] ) ) : '',
			'_stvl_video_open_behavior'     => isset( $_POST['stvl_video_open_behavior'] ) ? sanitize_text_field( wp_unslash( $_POST['stvl_video_open_behavior'] ) ) : 'modal',
			'_stvl_video_short_description' => isset( $_POST['stvl_video_short_description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['stvl_video_short_description'] ) ) : '',
			'_stvl_video_transcript'        => isset( $_POST['stvl_video_transcript'] ) ? sanitize_textarea_field( wp_unslash( $_POST['stvl_video_transcript'] ) ) : '',
			'_stvl_video_priority'          => isset( $_POST['stvl_video_priority'] ) ? absint( $_POST['stvl_video_priority'] ) : 0,
		);

		foreach ( $map as $key => $value ) {
			update_post_meta( $post_id, $key, $value );
		}
	}
}
