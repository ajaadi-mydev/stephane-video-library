<?php
/**
 * Video CPT.
 *
 * @package StephaneVideoLibrary
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class STVL_CPT_Video {

	/**
	 * Registers post type.
	 *
	 * @return void
	 */
	public function register() {
		$labels = array(
			'name'               => __( 'Videos', 'stephane-video-library' ),
			'singular_name'      => __( 'Video', 'stephane-video-library' ),
			'menu_name'          => __( 'Videos Stephane', 'stephane-video-library' ),
			'add_new_item'       => __( 'Ajouter une video', 'stephane-video-library' ),
			'edit_item'          => __( 'Modifier la video', 'stephane-video-library' ),
			'new_item'           => __( 'Nouvelle video', 'stephane-video-library' ),
			'view_item'          => __( 'Voir la video', 'stephane-video-library' ),
			'search_items'       => __( 'Rechercher des videos', 'stephane-video-library' ),
			'not_found'          => __( 'Aucune video trouvee.', 'stephane-video-library' ),
			'not_found_in_trash' => __( 'Aucune video dans la corbeille.', 'stephane-video-library' ),
		);

		register_post_type(
			STVL_CPT,
			array(
				'labels'             => $labels,
				'public'             => true,
				'show_ui'            => true,
				'show_in_menu'       => true,
				'show_in_rest'       => true,
				'menu_icon'          => 'dashicons-video-alt3',
				'has_archive'        => (bool) apply_filters( 'stvl_has_archive', false ),
				'rewrite'            => array( 'slug' => 'videos' ),
				'supports'           => array( 'title', 'editor', 'excerpt', 'thumbnail', 'page-attributes', 'revisions' ),
				'menu_position'      => 25,
				'publicly_queryable' => true,
			)
		);
	}

	/**
	 * Adds admin columns.
	 *
	 * @param array $columns Columns.
	 * @return array
	 */
	public function columns( $columns ) {
		$columns['stvl_thumb']     = __( 'Thumbnail', 'stephane-video-library' );
		$columns['stvl_provider']  = __( 'Provider', 'stephane-video-library' );
		$columns['stvl_duration']  = __( 'Duration', 'stephane-video-library' );
		$columns['taxonomy-' . STVL_TAX_CATEGORY] = __( 'Category', 'stephane-video-library' );
		$columns['stvl_featured']  = __( 'Featured', 'stephane-video-library' );
		$columns['stvl_shortcode'] = __( 'Shortcode / ID', 'stephane-video-library' );
		return $columns;
	}

	/**
	 * Renders admin columns.
	 *
	 * @param string $column Column key.
	 * @param int    $post_id Post ID.
	 * @return void
	 */
	public function column_content( $column, $post_id ) {
		switch ( $column ) {
			case 'stvl_thumb':
				$thumb = STVL_Helpers::get_thumbnail_url( $post_id );
				if ( $thumb ) {
					echo '<img src="' . esc_url( $thumb ) . '" alt="" style="width:72px;height:auto;border-radius:8px;" />';
				} else {
					echo '—';
				}
				break;
			case 'stvl_provider':
				echo esc_html( get_post_meta( $post_id, '_stvl_video_provider', true ) ?: '—' );
				break;
			case 'stvl_duration':
				echo esc_html( get_post_meta( $post_id, '_stvl_video_duration', true ) ?: '—' );
				break;
			case 'stvl_featured':
				echo get_post_meta( $post_id, '_stvl_video_featured', true ) ? esc_html__( 'Yes', 'stephane-video-library' ) : '—';
				break;
			case 'stvl_shortcode':
				echo '<code>[stvl_video_gallery ids="' . esc_html( (string) $post_id ) . '"]</code><br /><small>#' . esc_html( (string) $post_id ) . '</small>';
				break;
		}
	}

	/**
	 * Makes columns sortable.
	 *
	 * @param array $columns Sortable columns.
	 * @return array
	 */
	public function sortable_columns( $columns ) {
		$columns['title'] = 'title';
		$columns['date']  = 'date';
		return $columns;
	}

	/**
	 * Applies admin sorting and filtering.
	 *
	 * @param WP_Query $query Query.
	 * @return void
	 */
	public function admin_query( $query ) {
		if ( ! is_admin() || ! $query->is_main_query() || STVL_CPT !== $query->get( 'post_type' ) ) {
			return;
		}

		if ( isset( $_GET['stvl_provider_filter'] ) && '' !== $_GET['stvl_provider_filter'] ) {
			$query->set(
				'meta_query',
				array_merge(
					(array) $query->get( 'meta_query' ),
					array(
						array(
							'key'   => '_stvl_video_provider',
							'value' => sanitize_text_field( wp_unslash( $_GET['stvl_provider_filter'] ) ),
						),
					)
				)
			);
		}

		if ( isset( $_GET['stvl_featured_filter'] ) && '' !== $_GET['stvl_featured_filter'] ) {
			$query->set(
				'meta_query',
				array_merge(
					(array) $query->get( 'meta_query' ),
					array(
						array(
							'key'   => '_stvl_video_featured',
							'value' => 'yes' === sanitize_text_field( wp_unslash( $_GET['stvl_featured_filter'] ) ) ? '1' : '0',
						),
					)
				)
			);
		}

		if ( 'priority_meta' === $query->get( 'orderby' ) ) {
			$query->set( 'meta_key', '_stvl_video_priority' );
			$query->set( 'orderby', 'meta_value_num' );
		}
	}

	/**
	 * Adds filters dropdowns.
	 *
	 * @return void
	 */
	public function filters() {
		global $typenow;

		if ( STVL_CPT !== $typenow ) {
			return;
		}

		wp_dropdown_categories(
			array(
				'show_option_all' => __( 'All categories', 'stephane-video-library' ),
				'taxonomy'        => STVL_TAX_CATEGORY,
				'name'            => STVL_TAX_CATEGORY,
				'orderby'         => 'name',
				'selected'        => isset( $_GET[ STVL_TAX_CATEGORY ] ) ? absint( $_GET[ STVL_TAX_CATEGORY ] ) : 0,
				'hide_empty'      => false,
			)
		);

		$providers = array( 'youtube', 'vimeo', 'external', 'self_hosted', 'unknown' );

		echo '<select name="stvl_provider_filter"><option value="">' . esc_html__( 'All providers', 'stephane-video-library' ) . '</option>';
		foreach ( $providers as $provider ) {
			echo '<option value="' . esc_attr( $provider ) . '" ' . selected( isset( $_GET['stvl_provider_filter'] ) ? sanitize_text_field( wp_unslash( $_GET['stvl_provider_filter'] ) ) : '', $provider, false ) . '>' . esc_html( ucfirst( str_replace( '_', ' ', $provider ) ) ) . '</option>';
		}
		echo '</select>';

		echo '<select name="stvl_featured_filter"><option value="">' . esc_html__( 'Featured: all', 'stephane-video-library' ) . '</option>';
		echo '<option value="yes" ' . selected( isset( $_GET['stvl_featured_filter'] ) ? sanitize_text_field( wp_unslash( $_GET['stvl_featured_filter'] ) ) : '', 'yes', false ) . '>' . esc_html__( 'Featured only', 'stephane-video-library' ) . '</option>';
		echo '<option value="no" ' . selected( isset( $_GET['stvl_featured_filter'] ) ? sanitize_text_field( wp_unslash( $_GET['stvl_featured_filter'] ) ) : '', 'no', false ) . '>' . esc_html__( 'Not featured', 'stephane-video-library' ) . '</option>';
		echo '</select>';
	}
}
