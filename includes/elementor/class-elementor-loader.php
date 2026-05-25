<?php
/**
 * Elementor integration.
 *
 * @package StephaneVideoLibrary
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class STVL_Elementor_Loader {

	/**
	 * Boots Elementor integration when available.
	 *
	 * @return void
	 */
	public function maybe_boot() {
		if ( ! did_action( 'elementor/loaded' ) ) {
			return;
		}

		add_action( 'elementor/elements/categories_registered', array( $this, 'register_category' ) );
		add_action( 'elementor/widgets/register', array( $this, 'register_widgets' ) );
	}

	/**
	 * Registers Elementor category.
	 *
	 * @param \Elementor\Elements_Manager $elements_manager Manager.
	 * @return void
	 */
	public function register_category( $elements_manager ) {
		$elements_manager->add_category(
			'stephane-widgets',
			array(
				'title' => __( 'Stephane Widgets', 'stephane-video-library' ),
				'icon'  => 'fa fa-plug',
			)
		);
	}

	/**
	 * Registers widgets.
	 *
	 * @param \Elementor\Widgets_Manager $widgets_manager Manager.
	 * @return void
	 */
	public function register_widgets( $widgets_manager ) {
		require_once STVL_PATH . 'includes/elementor/class-widget-video-gallery.php';
		require_once STVL_PATH . 'includes/elementor/class-widget-featured-video.php';

		$widgets_manager->register( new STVL_Widget_Video_Gallery() );
		$widgets_manager->register( new STVL_Widget_Featured_Video() );
	}

	/**
	 * Admin notice when Elementor is missing.
	 *
	 * @return void
	 */
	public function admin_notice() {
		if ( did_action( 'elementor/loaded' ) ) {
			return;
		}

		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

		if ( ! $screen || false === strpos( $screen->id, STVL_CPT ) ) {
			return;
		}

		echo '<div class="notice notice-warning"><p>' . esc_html__( 'Stephane Video Library: the video content type works without Elementor, but gallery widgets require Elementor to be active.', 'stephane-video-library' ) . '</p></div>';
	}
}
