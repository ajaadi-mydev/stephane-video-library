<?php
/**
 * Asset registration.
 *
 * @package StephaneVideoLibrary
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class STVL_Assets {

	/**
	 * Registers frontend assets.
	 *
	 * @return void
	 */
	public function register_frontend() {
		wp_register_style( 'stvl-frontend', STVL_URL . 'assets/css/frontend.css', array(), STVL_VERSION );
		wp_register_script( 'stvl-search-filter', STVL_URL . 'assets/js/search-filter.js', array(), STVL_VERSION, true );
		wp_register_script( 'stvl-modal-video', STVL_URL . 'assets/js/modal-video.js', array(), STVL_VERSION, true );
		wp_register_script( 'stvl-frontend', STVL_URL . 'assets/js/frontend.js', array( 'stvl-search-filter', 'stvl-modal-video' ), STVL_VERSION, true );
	}

	/**
	 * Registers admin assets.
	 *
	 * @return void
	 */
	public function register_admin() {
		wp_register_style( 'stvl-admin', STVL_URL . 'assets/css/admin.css', array(), STVL_VERSION );
		wp_register_script( 'stvl-admin', STVL_URL . 'assets/js/elementor-editor.js', array( 'media-editor' ), STVL_VERSION, true );
	}

	/**
	 * Enqueues admin assets when needed.
	 *
	 * @param string $hook Hook suffix.
	 * @return void
	 */
	public function admin_enqueue( $hook ) {
		if ( 'post.php' === $hook || 'post-new.php' === $hook || 'st_video_page_stvl-import' === $hook ) {
			wp_enqueue_media();
			wp_enqueue_style( 'stvl-admin' );
			wp_enqueue_script( 'stvl-admin' );
		}
	}

	/**
	 * Enqueues editor assets.
	 *
	 * @return void
	 */
	public function elementor_editor_assets() {
		wp_register_style( 'stvl-elementor-editor', STVL_URL . 'assets/css/elementor-editor.css', array(), STVL_VERSION );
		wp_register_script( 'stvl-elementor-editor', STVL_URL . 'assets/js/elementor-editor.js', array(), STVL_VERSION, true );
		wp_enqueue_style( 'stvl-elementor-editor' );
		wp_enqueue_script( 'stvl-elementor-editor' );
	}

	/**
	 * Frontend dependency enqueue.
	 *
	 * @return void
	 */
	public static function enqueue_frontend() {
		wp_enqueue_style( 'stvl-frontend' );
		wp_enqueue_script( 'stvl-frontend' );
	}
}
