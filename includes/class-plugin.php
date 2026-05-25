<?php
/**
 * Main plugin orchestrator.
 *
 * @package StephaneVideoLibrary
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class STVL_Plugin {

	/**
	 * Singleton instance.
	 *
	 * @var STVL_Plugin|null
	 */
	private static $instance = null;

	/**
	 * Loader.
	 *
	 * @var STVL_Loader
	 */
	private $loader;

	/**
	 * Returns instance.
	 *
	 * @return STVL_Plugin
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		$this->loader = new STVL_Loader();
		$this->includes();
		$this->hooks();
	}

	/**
	 * Includes class files.
	 *
	 * @return void
	 */
	private function includes() {
		require_once STVL_PATH . 'includes/class-cpt-video.php';
		require_once STVL_PATH . 'includes/class-taxonomies.php';
		require_once STVL_PATH . 'includes/class-meta-fields.php';
		require_once STVL_PATH . 'includes/class-assets.php';
		require_once STVL_PATH . 'includes/class-shortcodes.php';
		require_once STVL_PATH . 'includes/class-admin.php';
		require_once STVL_PATH . 'includes/elementor/class-elementor-loader.php';
	}

	/**
	 * Registers plugin hooks.
	 *
	 * @return void
	 */
	private function hooks() {
		$cpt         = new STVL_CPT_Video();
		$taxonomies  = new STVL_Taxonomies();
		$meta_fields = new STVL_Meta_Fields();
		$assets      = new STVL_Assets();
		$shortcodes  = new STVL_Shortcodes();
		$admin       = new STVL_Admin();
		$elementor   = new STVL_Elementor_Loader();

		$this->loader->add_action( 'plugins_loaded', $this, 'load_textdomain' );
		$this->loader->add_action( 'init', $cpt, 'register' );
		$this->loader->add_action( 'init', $taxonomies, 'register' );
		$this->loader->add_action( 'init', $meta_fields, 'register_meta' );
		$this->loader->add_action( 'init', $shortcodes, 'register' );
		$this->loader->add_action( 'add_meta_boxes', $meta_fields, 'add_meta_box' );
		$this->loader->add_action( 'save_post', $meta_fields, 'save' );
		$this->loader->add_action( 'wp_enqueue_scripts', $assets, 'register_frontend' );
		$this->loader->add_action( 'admin_enqueue_scripts', $assets, 'register_admin' );
		$this->loader->add_action( 'admin_enqueue_scripts', $assets, 'admin_enqueue' );
		$this->loader->add_action( 'elementor/editor/after_enqueue_scripts', $assets, 'elementor_editor_assets' );
		$this->loader->add_action( 'admin_menu', $admin, 'menu' );
		$this->loader->add_action( 'manage_' . STVL_CPT . '_posts_custom_column', $cpt, 'column_content', 10, 2 );
		$this->loader->add_filter( 'manage_' . STVL_CPT . '_posts_columns', $cpt, 'columns' );
		$this->loader->add_filter( 'manage_edit-' . STVL_CPT . '_sortable_columns', $cpt, 'sortable_columns' );
		$this->loader->add_action( 'pre_get_posts', $cpt, 'admin_query' );
		$this->loader->add_action( 'restrict_manage_posts', $cpt, 'filters' );
		$this->loader->add_action( 'init', $elementor, 'maybe_boot' );
		$this->loader->add_action( 'admin_notices', $elementor, 'admin_notice' );
	}

	/**
	 * Loads translations.
	 *
	 * @return void
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'stephane-video-library', false, dirname( STVL_BASENAME ) . '/languages' );
	}

	/**
	 * Activation hook.
	 *
	 * @return void
	 */
	public static function activate() {
		$cpt = new STVL_CPT_Video();
		$tax = new STVL_Taxonomies();
		$cpt->register();
		$tax->register();
		flush_rewrite_rules();
	}

	/**
	 * Deactivation hook.
	 *
	 * @return void
	 */
	public static function deactivate() {
		flush_rewrite_rules();
	}
}
