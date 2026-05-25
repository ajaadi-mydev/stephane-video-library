<?php
/**
 * Shortcodes.
 *
 * @package StephaneVideoLibrary
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class STVL_Shortcodes {

	/**
	 * Registers shortcodes.
	 *
	 * @return void
	 */
	public function register() {
		add_shortcode( 'stvl_video_gallery', array( $this, 'render_gallery' ) );
	}

	/**
	 * Renders gallery shortcode.
	 *
	 * @param array $atts Attributes.
	 * @return string
	 */
	public function render_gallery( $atts ) {
		$atts = shortcode_atts(
			array(
				'ids'                => '',
				'category'           => '',
				'topic'              => '',
				'limit'              => 9,
				'columns'            => 3,
				'layout'             => 'grid',
				'show_search'        => 'yes',
				'show_count'         => 'yes',
				'orderby'            => 'date',
				'order'              => 'DESC',
				'description_source' => 'excerpt',
				'description_length' => 24,
				'global_button_text' => __( 'Voir la video', 'stephane-video-library' ),
				'button_text_source' => 'global',
				'show_category_chip' => 'yes',
				'show_duration'      => 'yes',
				'show_index'         => 'yes',
				'index_format'       => '01',
				'show_title'         => 'yes',
				'show_description'   => 'yes',
				'show_cta'           => 'yes',
				'link_behavior'      => 'individual',
				'privacy_mode'       => 'yes',
				'autoplay_modal'     => '',
				'search_placeholder' => __( 'Rechercher une interview, un media, un theme…', 'stephane-video-library' ),
				'count_suffix'       => __( 'videos', 'stephane-video-library' ),
				'empty_state_text'   => __( 'Aucune video ne correspond a cette recherche.', 'stephane-video-library' ),
				'show_empty_state'   => 'yes',
				'show_category_filters' => 'yes',
				'custom_class'       => '',
			),
			$atts,
			'stvl_video_gallery'
		);

		$query_args = array(
			'post_type'      => STVL_CPT,
			'post_status'    => 'publish',
			'posts_per_page' => (int) $atts['limit'],
			'orderby'        => sanitize_key( $atts['orderby'] ),
			'order'          => 'ASC' === strtoupper( $atts['order'] ) ? 'ASC' : 'DESC',
		);

		if ( ! empty( $atts['ids'] ) ) {
			$ids                    = array_filter( array_map( 'absint', explode( ',', $atts['ids'] ) ) );
			$query_args['post__in'] = $ids;
			$query_args['orderby']  = 'post__in';
		}

		$tax_query = array();

		if ( ! empty( $atts['category'] ) ) {
			$tax_query[] = array(
				'taxonomy' => STVL_TAX_CATEGORY,
				'field'    => 'slug',
				'terms'    => array_map( 'sanitize_title', explode( ',', $atts['category'] ) ),
			);
		}

		if ( ! empty( $atts['topic'] ) ) {
			$tax_query[] = array(
				'taxonomy' => STVL_TAX_TOPIC,
				'field'    => 'slug',
				'terms'    => array_map( 'sanitize_title', explode( ',', $atts['topic'] ) ),
			);
		}

		if ( $tax_query ) {
			$query_args['tax_query'] = $tax_query;
		}

		if ( 'priority_meta' === $atts['orderby'] ) {
			$query_args['meta_key'] = '_stvl_video_priority';
			$query_args['orderby']  = 'meta_value_num';
		}

		if ( 'menu_order' === $atts['orderby'] ) {
			$query_args['orderby'] = 'menu_order';
		}

		if ( 'random' === $atts['orderby'] ) {
			$query_args['orderby'] = 'rand';
		}

		$query = new WP_Query( $query_args );

		STVL_Assets::enqueue_frontend();

		return STVL_Helpers::render_template(
			'video-grid.php',
			array(
				'query'    => $query,
				'settings' => $atts,
			)
		);
	}
}
