<?php
/**
 * Taxonomy registration.
 *
 * @package StephaneVideoLibrary
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class STVL_Taxonomies {

	/**
	 * Registers taxonomies.
	 *
	 * @return void
	 */
	public function register() {
		register_taxonomy(
			STVL_TAX_CATEGORY,
			STVL_CPT,
			array(
				'label'        => __( 'Video Categories', 'stephane-video-library' ),
				'public'       => true,
				'hierarchical' => true,
				'show_in_rest' => true,
				'rewrite'      => array( 'slug' => 'video-category' ),
			)
		);

		register_taxonomy(
			STVL_TAX_TOPIC,
			STVL_CPT,
			array(
				'label'        => __( 'Video Topics', 'stephane-video-library' ),
				'public'       => true,
				'hierarchical' => false,
				'show_in_rest' => true,
				'rewrite'      => array( 'slug' => 'video-topic' ),
			)
		);
	}
}
