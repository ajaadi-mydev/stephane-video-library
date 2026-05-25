<?php
/**
 * Elementor gallery widget.
 *
 * @package StephaneVideoLibrary
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Widget_Base;

class STVL_Widget_Video_Gallery extends Widget_Base {

	/**
	 * Widget slug.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'stvl-video-gallery';
	}

	/**
	 * Widget title.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'Stephane Video Gallery', 'stephane-video-library' );
	}

	/**
	 * Widget icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-video-camera';
	}

	/**
	 * Widget categories.
	 *
	 * @return array
	 */
	public function get_categories() {
		return array( 'stephane-widgets' );
	}

	/**
	 * Style dependencies.
	 *
	 * @return array
	 */
	public function get_style_depends() {
		return array( 'stvl-frontend' );
	}

	/**
	 * Script dependencies.
	 *
	 * @return array
	 */
	public function get_script_depends() {
		return array( 'stvl-frontend' );
	}

	/**
	 * Registers controls.
	 *
	 * @return void
	 */
	protected function register_controls() {
		$this->register_content_controls();
		$this->register_layout_controls();
		$this->register_card_controls();
		$this->register_behavior_controls();
		$this->register_style_controls();
	}

	/**
	 * Content query controls.
	 *
	 * @return void
	 */
	private function register_content_controls() {
		$this->start_controls_section(
			'section_content_source',
			array(
				'label' => __( 'Content Source', 'stephane-video-library' ),
			)
		);

		$this->add_control(
			'source',
			array(
				'label'   => __( 'Source', 'stephane-video-library' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'latest',
				'options' => array(
					'latest'   => __( 'Latest videos', 'stephane-video-library' ),
					'manual'   => __( 'Manual selection', 'stephane-video-library' ),
					'category' => __( 'By category', 'stephane-video-library' ),
					'topic'    => __( 'By topic', 'stephane-video-library' ),
					'featured' => __( 'Featured only', 'stephane-video-library' ),
					'custom'   => __( 'Custom query', 'stephane-video-library' ),
				),
			)
		);

		$this->add_control(
			'manual_ids',
			array(
				'label'       => __( 'Manual videos', 'stephane-video-library' ),
				'type'        => Controls_Manager::SELECT2,
				'options'     => $this->get_video_options(),
				'multiple'    => true,
				'label_block' => true,
				'condition'   => array( 'source' => 'manual' ),
			)
		);

		$this->add_control(
			'category_terms',
			array(
				'label'       => __( 'Categories', 'stephane-video-library' ),
				'type'        => Controls_Manager::SELECT2,
				'options'     => $this->get_taxonomy_options( STVL_TAX_CATEGORY ),
				'multiple'    => true,
				'label_block' => true,
				'condition'   => array( 'source' => array( 'category', 'custom' ) ),
			)
		);

		$this->add_control(
			'topic_terms',
			array(
				'label'       => __( 'Topics', 'stephane-video-library' ),
				'type'        => Controls_Manager::SELECT2,
				'options'     => $this->get_taxonomy_options( STVL_TAX_TOPIC ),
				'multiple'    => true,
				'label_block' => true,
				'condition'   => array( 'source' => array( 'topic', 'custom' ) ),
			)
		);

		$this->add_control(
			'exclude_ids',
			array(
				'label'       => __( 'Exclude videos', 'stephane-video-library' ),
				'type'        => Controls_Manager::SELECT2,
				'options'     => $this->get_video_options(),
				'multiple'    => true,
				'label_block' => true,
			)
		);

		$this->add_control(
			'number',
			array(
				'label'   => __( 'Number of videos', 'stephane-video-library' ),
				'type'    => Controls_Manager::NUMBER,
				'default' => 9,
				'min'     => -1,
			)
		);

		$this->add_control(
			'orderby',
			array(
				'label'   => __( 'Order by', 'stephane-video-library' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'date',
				'options' => array(
					'date'          => __( 'Date', 'stephane-video-library' ),
					'title'         => __( 'Title', 'stephane-video-library' ),
					'menu_order'    => __( 'Menu order', 'stephane-video-library' ),
					'priority_meta' => __( 'Priority meta', 'stephane-video-library' ),
					'rand'          => __( 'Random', 'stephane-video-library' ),
				),
			)
		);

		$this->add_control(
			'order',
			array(
				'label'   => __( 'Order', 'stephane-video-library' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'DESC',
				'options' => array(
					'ASC'  => 'ASC',
					'DESC' => 'DESC',
				),
			)
		);

		$this->add_control(
			'offset',
			array(
				'label'   => __( 'Offset', 'stephane-video-library' ),
				'type'    => Controls_Manager::NUMBER,
				'default' => 0,
				'min'     => 0,
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Layout controls.
	 *
	 * @return void
	 */
	private function register_layout_controls() {
		$this->start_controls_section(
			'section_layout',
			array(
				'label' => __( 'Layout', 'stephane-video-library' ),
			)
		);

		$this->add_control(
			'layout_style',
			array(
				'label'   => __( 'Layout style', 'stephane-video-library' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'grid',
				'options' => array(
					'grid'            => __( 'Grid', 'stephane-video-library' ),
					'list'            => __( 'List', 'stephane-video-library' ),
					'featured_grid'   => __( 'Featured first + grid', 'stephane-video-library' ),
					'compact'         => __( 'Compact cards', 'stephane-video-library' ),
				),
			)
		);

		$this->add_responsive_control(
			'columns',
			array(
				'label'          => __( 'Columns', 'stephane-video-library' ),
				'type'           => Controls_Manager::SELECT,
				'default'        => '3',
				'tablet_default' => '2',
				'mobile_default' => '1',
				'options'        => array(
					'1' => '1',
					'2' => '2',
					'3' => '3',
					'4' => '4',
					'5' => '5',
					'6' => '6',
				),
				'selectors'      => array(
					'{{WRAPPER}} .stvl-video-grid-inner' => 'grid-template-columns: repeat({{VALUE}}, minmax(0, 1fr));',
				),
			)
		);

		$this->add_responsive_control(
			'gap',
			array(
				'label'      => __( 'Gap', 'stephane-video-library' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'default'    => array(
					'unit' => 'px',
					'size' => 24,
				),
				'selectors'  => array(
					'{{WRAPPER}} .stvl-video-grid-inner' => 'gap: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'show_search',
			array(
				'label'        => __( 'Show search bar', 'stephane-video-library' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Yes', 'stephane-video-library' ),
				'label_off'    => __( 'No', 'stephane-video-library' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'search_placeholder',
			array(
				'label'   => __( 'Search placeholder', 'stephane-video-library' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'Rechercher une interview, un media, un theme…', 'stephane-video-library' ),
				'condition' => array( 'show_search' => 'yes' ),
			)
		);

		$this->add_control(
			'show_count',
			array(
				'label'        => __( 'Show video count', 'stephane-video-library' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'count_suffix',
			array(
				'label'   => __( 'Count suffix', 'stephane-video-library' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'videos', 'stephane-video-library' ),
			)
		);

		$this->add_control(
			'show_empty_state',
			array(
				'label'        => __( 'Show empty state', 'stephane-video-library' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'empty_state_text',
			array(
				'label'   => __( 'Empty state text', 'stephane-video-library' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'Aucune video ne correspond a cette recherche.', 'stephane-video-library' ),
			)
		);

		$this->add_control(
			'show_category_filters',
			array(
				'label'        => __( 'Show category filters', 'stephane-video-library' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Card controls.
	 *
	 * @return void
	 */
	private function register_card_controls() {
		$this->start_controls_section(
			'section_card_elements',
			array(
				'label' => __( 'Card Elements', 'stephane-video-library' ),
			)
		);

		$switchers = array(
			'show_thumbnail'     => 'yes',
			'show_play_icon'     => 'yes',
			'show_video_index'   => 'yes',
			'show_duration'      => 'yes',
			'show_category_chip' => 'yes',
			'show_source_name'   => 'yes',
			'show_title'         => 'yes',
			'show_description'   => 'yes',
			'show_cta'           => 'yes',
		);

		foreach ( $switchers as $key => $default ) {
			$this->add_control(
				$key,
				array(
					'label'        => ucwords( str_replace( '_', ' ', $key ) ),
					'type'         => Controls_Manager::SWITCHER,
					'return_value' => 'yes',
					'default'      => $default,
				)
			);
		}

		$this->add_control(
			'index_format',
			array(
				'label'   => __( 'Index format', 'stephane-video-library' ),
				'type'    => Controls_Manager::SELECT,
				'default' => '01',
				'options' => array(
					'01'  => '01',
					'1'   => '1',
					'#01' => '#01',
					'none'=> __( 'None', 'stephane-video-library' ),
				),
			)
		);

		$this->add_control(
			'title_tag',
			array(
				'label'   => __( 'Title HTML tag', 'stephane-video-library' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'h3',
				'options' => array(
					'h2'  => 'h2',
					'h3'  => 'h3',
					'h4'  => 'h4',
					'div' => 'div',
				),
			)
		);

		$this->add_control(
			'description_source',
			array(
				'label'   => __( 'Description source', 'stephane-video-library' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'excerpt',
				'options' => array(
					'excerpt'    => __( 'Excerpt', 'stephane-video-library' ),
					'short_meta' => __( 'Short card description meta', 'stephane-video-library' ),
					'content'    => __( 'Editor content trimmed', 'stephane-video-library' ),
					'none'       => __( 'None', 'stephane-video-library' ),
				),
			)
		);

		$this->add_control(
			'description_length',
			array(
				'label'   => __( 'Description length', 'stephane-video-library' ),
				'type'    => Controls_Manager::NUMBER,
				'default' => 24,
				'min'     => 0,
			)
		);

		$this->add_control(
			'button_text_source',
			array(
				'label'   => __( 'Button text source', 'stephane-video-library' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'global',
				'options' => array(
					'global'     => __( 'Global widget text', 'stephane-video-library' ),
					'individual' => __( 'Individual video meta', 'stephane-video-library' ),
				),
			)
		);

		$this->add_control(
			'global_button_text',
			array(
				'label'   => __( 'Global button text', 'stephane-video-library' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'Voir la video', 'stephane-video-library' ),
			)
		);

		$this->add_control(
			'link_behavior',
			array(
				'label'   => __( 'Link behavior', 'stephane-video-library' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'individual',
				'options' => array(
					'individual'   => __( 'Use individual video setting', 'stephane-video-library' ),
					'modal'        => __( 'Open modal', 'stephane-video-library' ),
					'new_tab'      => __( 'Open new tab', 'stephane-video-library' ),
					'same_tab'     => __( 'Same tab', 'stephane-video-library' ),
					'inline_embed' => __( 'Inline embed', 'stephane-video-library' ),
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Behavior controls.
	 *
	 * @return void
	 */
	private function register_behavior_controls() {
		$this->start_controls_section(
			'section_video_behavior',
			array(
				'label' => __( 'Video Behavior', 'stephane-video-library' ),
			)
		);

		$this->add_control(
			'enable_video_modal',
			array(
				'label'        => __( 'Enable video modal', 'stephane-video-library' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'autoplay_modal',
			array(
				'label'        => __( 'Autoplay on modal open', 'stephane-video-library' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => '',
			)
		);

		$this->add_control(
			'privacy_mode',
			array(
				'label'        => __( 'Privacy enhanced YouTube', 'stephane-video-library' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'accessibility_play_label',
			array(
				'label'   => __( 'Play button aria label text', 'stephane-video-library' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'Lire la video', 'stephane-video-library' ),
			)
		);

		$this->add_control(
			'accessibility_search_label',
			array(
				'label'   => __( 'Search input label text', 'stephane-video-library' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'Rechercher des videos', 'stephane-video-library' ),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Style controls.
	 *
	 * @return void
	 */
	private function register_style_controls() {
		$this->start_controls_section(
			'section_style_card',
			array(
				'label' => __( 'Card Style', 'stephane-video-library' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'card_background',
			array(
				'label'     => __( 'Background', 'stephane-video-library' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#FFFFFF',
				'selectors' => array(
					'{{WRAPPER}} .stvl-video-card' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'card_border_color',
			array(
				'label'     => __( 'Border color', 'stephane-video-library' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => 'rgba(10,10,10,.12)',
				'selectors' => array(
					'{{WRAPPER}} .stvl-video-card' => 'border-color: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'card_radius',
			array(
				'label'      => __( 'Border radius', 'stephane-video-library' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'default'    => array(
					'unit' => 'px',
					'size' => 28,
				),
				'selectors'  => array(
					'{{WRAPPER}} .stvl-video-card, {{WRAPPER}} .stvl-video-thumb' => 'border-radius: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'card_padding',
			array(
				'label'      => __( 'Padding', 'stephane-video-library' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .stvl-video-body' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'card_border',
				'selector' => '{{WRAPPER}} .stvl-video-card',
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'card_shadow',
				'selector' => '{{WRAPPER}} .stvl-video-card',
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_title',
			array(
				'label' => __( 'Title Style', 'stephane-video-library' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'title_color',
			array(
				'label'     => __( 'Color', 'stephane-video-library' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#0A0A0A',
				'selectors' => array(
					'{{WRAPPER}} .stvl-video-title' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'title_typography',
				'selector' => '{{WRAPPER}} .stvl-video-title',
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Renders widget.
	 *
	 * @return void
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();
		$query    = new WP_Query( $this->build_query_args( $settings ) );

		STVL_Assets::enqueue_frontend();

		echo STVL_Helpers::render_template(
			'video-grid.php',
			array(
				'query'    => $query,
				'settings' => $settings,
			)
		);
	}

	/**
	 * Query args builder.
	 *
	 * @param array $settings Widget settings.
	 * @return array
	 */
	private function build_query_args( $settings ) {
		$args = array(
			'post_type'      => STVL_CPT,
			'post_status'    => 'publish',
			'posts_per_page' => isset( $settings['number'] ) ? (int) $settings['number'] : 9,
			'offset'         => isset( $settings['offset'] ) ? (int) $settings['offset'] : 0,
			'orderby'        => isset( $settings['orderby'] ) ? $settings['orderby'] : 'date',
			'order'          => isset( $settings['order'] ) ? $settings['order'] : 'DESC',
			'post__not_in'   => ! empty( $settings['exclude_ids'] ) ? array_map( 'absint', (array) $settings['exclude_ids'] ) : array(),
		);

		if ( 'priority_meta' === $args['orderby'] ) {
			$args['meta_key'] = '_stvl_video_priority';
			$args['orderby']  = 'meta_value_num';
		}

		$tax_query = array();

		if ( 'manual' === $settings['source'] && ! empty( $settings['manual_ids'] ) ) {
			$args['post__in'] = array_map( 'absint', (array) $settings['manual_ids'] );
			$args['orderby']  = 'post__in';
		}

		if ( in_array( $settings['source'], array( 'category', 'custom' ), true ) && ! empty( $settings['category_terms'] ) ) {
			$tax_query[] = array(
				'taxonomy' => STVL_TAX_CATEGORY,
				'field'    => 'term_id',
				'terms'    => array_map( 'absint', (array) $settings['category_terms'] ),
			);
		}

		if ( in_array( $settings['source'], array( 'topic', 'custom' ), true ) && ! empty( $settings['topic_terms'] ) ) {
			$tax_query[] = array(
				'taxonomy' => STVL_TAX_TOPIC,
				'field'    => 'term_id',
				'terms'    => array_map( 'absint', (array) $settings['topic_terms'] ),
			);
		}

		if ( $tax_query ) {
			$args['tax_query'] = $tax_query;
		}

		if ( 'featured' === $settings['source'] ) {
			$args['meta_query'] = array(
				array(
					'key'   => '_stvl_video_featured',
					'value' => '1',
				),
			);
		}

		return $args;
	}

	/**
	 * Video options for select2.
	 *
	 * @return array
	 */
	private function get_video_options() {
		$posts   = get_posts(
			array(
				'post_type'      => STVL_CPT,
				'post_status'    => array( 'publish', 'draft' ),
				'posts_per_page' => 200,
			)
		);
		$options = array();

		foreach ( $posts as $post ) {
			$options[ $post->ID ] = $post->post_title;
		}

		return $options;
	}

	/**
	 * Taxonomy options.
	 *
	 * @param string $taxonomy Taxonomy.
	 * @return array
	 */
	private function get_taxonomy_options( $taxonomy ) {
		$terms   = get_terms(
			array(
				'taxonomy'   => $taxonomy,
				'hide_empty' => false,
			)
		);
		$options = array();

		if ( is_wp_error( $terms ) ) {
			return $options;
		}

		foreach ( $terms as $term ) {
			$options[ $term->term_id ] = $term->name;
		}

		return $options;
	}
}
