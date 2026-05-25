<?php
/**
 * Elementor featured video widget.
 *
 * @package StephaneVideoLibrary
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Controls_Manager as STVL_Controls_Manager;
use Elementor\Widget_Base as STVL_Widget_Base;

class STVL_Widget_Featured_Video extends STVL_Widget_Base {

	/**
	 * Name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'stvl-featured-video';
	}

	/**
	 * Title.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'Stephane Featured Video', 'stephane-video-library' );
	}

	/**
	 * Icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-play';
	}

	/**
	 * Categories.
	 *
	 * @return array
	 */
	public function get_categories() {
		return array( 'stephane-widgets' );
	}

	/**
	 * Style depends.
	 *
	 * @return array
	 */
	public function get_style_depends() {
		return array( 'stvl-frontend' );
	}

	/**
	 * Script depends.
	 *
	 * @return array
	 */
	public function get_script_depends() {
		return array( 'stvl-frontend' );
	}

	/**
	 * Controls.
	 *
	 * @return void
	 */
	protected function register_controls() {
		$this->start_controls_section(
			'section_featured',
			array(
				'label' => __( 'Featured Video', 'stephane-video-library' ),
			)
		);

		$options = array();
		foreach ( get_posts( array( 'post_type' => STVL_CPT, 'posts_per_page' => 200 ) ) as $post ) {
			$options[ $post->ID ] = $post->post_title;
		}

		$this->add_control(
			'video_id',
			array(
				'label'   => __( 'Select video', 'stephane-video-library' ),
				'type'    => STVL_Controls_Manager::SELECT2,
				'options' => $options,
			)
		);

		$this->add_control(
			'layout',
			array(
				'label'   => __( 'Layout', 'stephane-video-library' ),
				'type'    => STVL_Controls_Manager::SELECT,
				'default' => 'thumb-left',
				'options' => array(
					'thumb-left'     => __( 'Thumbnail left / content right', 'stephane-video-library' ),
					'thumb-right'    => __( 'Content left / thumbnail right', 'stephane-video-library' ),
					'background'     => __( 'Background cover', 'stephane-video-library' ),
					'centered-card'  => __( 'Centered card', 'stephane-video-library' ),
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Render.
	 *
	 * @return void
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();
		$post_id  = ! empty( $settings['video_id'] ) ? absint( $settings['video_id'] ) : 0;

		if ( ! $post_id ) {
			return;
		}

		STVL_Assets::enqueue_frontend();

		$video = STVL_Helpers::get_video_data(
			$post_id,
			array(
				'show_category_chip' => 'yes',
				'show_duration'      => 'yes',
				'show_title'         => 'yes',
				'show_description'   => 'yes',
				'show_cta'           => 'yes',
				'global_button_text' => __( 'Voir la video', 'stephane-video-library' ),
				'button_text_source' => 'individual',
				'link_behavior'      => 'individual',
			),
			1
		);

		echo '<div class="stvl-featured-wrap stvl-featured-layout-' . esc_attr( $settings['layout'] ) . '">';
		echo STVL_Helpers::render_template(
			'video-card.php',
			array(
				'video'    => $video,
				'settings' => array(
					'show_thumbnail'     => 'yes',
					'show_play_icon'     => 'yes',
					'show_video_index'   => '',
					'show_duration'      => 'yes',
					'show_category_chip' => 'yes',
					'show_source_name'   => 'yes',
					'show_title'         => 'yes',
					'title_tag'          => 'h2',
					'show_description'   => 'yes',
					'show_cta'           => 'yes',
					'index_format'       => '01',
				),
			)
		);
		echo '</div>';
	}
}
