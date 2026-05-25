<?php
/**
 * Gallery grid template.
 *
 * @var WP_Query $query
 * @var array    $settings
 *
 * @package StephaneVideoLibrary
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$settings = wp_parse_args(
	$settings,
	array(
		'layout_style'           => 'grid',
		'columns'                => 3,
		'show_search'            => 'yes',
		'show_count'             => 'yes',
		'show_empty_state'       => 'yes',
		'show_category_filters'  => 'yes',
		'search_placeholder'     => __( 'Rechercher une interview, un media, un theme…', 'stephane-video-library' ),
		'count_suffix'           => __( 'videos', 'stephane-video-library' ),
		'empty_state_text'       => __( 'Aucune video ne correspond a cette recherche.', 'stephane-video-library' ),
		'custom_class'           => '',
		'accessibility_search_label' => __( 'Rechercher des videos', 'stephane-video-library' ),
	)
);

$gallery_id  = 'stvl-gallery-' . wp_rand( 1000, 99999 );
$videos      = array();
$categories  = array();
$display_idx = 1;

if ( $query->have_posts() ) {
	while ( $query->have_posts() ) {
		$query->the_post();
		$videos[] = STVL_Helpers::get_video_data( get_the_ID(), $settings, $display_idx );
		$terms    = get_the_terms( get_the_ID(), STVL_TAX_CATEGORY );
		if ( is_array( $terms ) ) {
			foreach ( $terms as $term ) {
				$categories[ $term->slug ] = $term->name;
			}
		}
		++$display_idx;
	}
	wp_reset_postdata();
}
?>
<section
	class="stvl-video-gallery stvl-layout-<?php echo esc_attr( $settings['layout_style'] ); ?> <?php echo esc_attr( $settings['custom_class'] ); ?>"
	id="<?php echo esc_attr( $gallery_id ); ?>"
	data-count-suffix="<?php echo esc_attr( $settings['count_suffix'] ); ?>"
	data-empty-text="<?php echo esc_attr( $settings['empty_state_text'] ); ?>"
>
	<?php if ( 'yes' === $settings['show_search'] || 'yes' === $settings['show_count'] || ( 'yes' === $settings['show_category_filters'] && ! empty( $categories ) ) ) : ?>
		<div class="stvl-toolbar">
			<?php if ( 'yes' === $settings['show_search'] ) : ?>
				<div class="stvl-search-wrap">
					<label class="screen-reader-text" for="<?php echo esc_attr( $gallery_id ); ?>-search"><?php echo esc_html( $settings['accessibility_search_label'] ); ?></label>
					<input
						id="<?php echo esc_attr( $gallery_id ); ?>-search"
						class="stvl-search"
						type="search"
						placeholder="<?php echo esc_attr( $settings['search_placeholder'] ); ?>"
						data-stvl-search
					/>
				</div>
			<?php endif; ?>

			<?php if ( 'yes' === $settings['show_category_filters'] && ! empty( $categories ) ) : ?>
				<div class="stvl-filters" role="tablist" aria-label="<?php esc_attr_e( 'Filtrer par categorie', 'stephane-video-library' ); ?>">
					<button type="button" class="stvl-filter is-active" data-filter="all"><?php esc_html_e( 'Toutes', 'stephane-video-library' ); ?></button>
					<?php foreach ( $categories as $slug => $label ) : ?>
						<button type="button" class="stvl-filter" data-filter="<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $label ); ?></button>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<?php if ( 'yes' === $settings['show_count'] ) : ?>
				<div class="stvl-count" data-stvl-count>
					<?php echo esc_html( count( $videos ) . ' ' . $settings['count_suffix'] ); ?>
				</div>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<div class="stvl-video-grid-inner" data-stvl-grid>
		<?php if ( ! empty( $videos ) ) : ?>
			<?php foreach ( $videos as $video ) : ?>
				<?php
				echo STVL_Helpers::render_template(
					'video-card.php',
					array(
						'video'    => $video,
						'settings' => $settings,
					)
				);
				?>
			<?php endforeach; ?>
		<?php endif; ?>
	</div>

	<?php if ( 'yes' === $settings['show_empty_state'] ) : ?>
		<div class="stvl-empty<?php echo ! empty( $videos ) ? '' : ' is-visible'; ?>" data-stvl-empty>
			<?php echo esc_html( $settings['empty_state_text'] ); ?>
		</div>
	<?php endif; ?>

	<?php echo STVL_Helpers::render_template( 'video-modal.php', array( 'gallery_id' => $gallery_id ) ); ?>
</section>
