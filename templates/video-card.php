<?php
/**
 * Video card template.
 *
 * @var array $video
 * @var array $settings
 *
 * @package StephaneVideoLibrary
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$title_tag      = ! empty( $settings['title_tag'] ) ? $settings['title_tag'] : 'h3';
$index_display  = STVL_Helpers::format_index( (int) $video['index'], $settings['index_format'] ?? '01' );
$thumb_url      = $video['thumbnail'] ? $video['thumbnail'] : STVL_Helpers::get_fallback_thumbnail();
$category_slugs = array_map( 'sanitize_title', (array) $video['category_names'] );
$topic_slugs    = array_map( 'sanitize_title', (array) $video['topic_names'] );
$action_url     = $video['external_url'] ? $video['external_url'] : ( $video['video_url'] ? $video['video_url'] : $video['permalink'] );
?>
<article
	class="stvl-video-card"
	data-title="<?php echo esc_attr( strtolower( $video['title'] ) ); ?>"
	data-description="<?php echo esc_attr( strtolower( $video['description'] ) ); ?>"
	data-category="<?php echo esc_attr( strtolower( implode( ' ', $category_slugs ) ) ); ?>"
	data-topic="<?php echo esc_attr( strtolower( implode( ' ', $topic_slugs ) ) ); ?>"
	data-source="<?php echo esc_attr( strtolower( (string) $video['source_name'] ) ); ?>"
	data-video-id="<?php echo esc_attr( $video['video_id'] ); ?>"
	data-provider="<?php echo esc_attr( $video['provider'] ); ?>"
	data-url="<?php echo esc_attr( $video['video_url'] ); ?>"
>
	<?php if ( 'yes' === ( $settings['show_thumbnail'] ?? 'yes' ) ) : ?>
		<div class="stvl-video-thumb">
			<img src="<?php echo esc_url( $thumb_url ); ?>" alt="<?php echo esc_attr( $video['title'] ); ?>" loading="lazy" />
			<div class="stvl-thumb-overlay"></div>
			<?php if ( 'yes' === ( $settings['show_video_index'] ?? 'yes' ) && $index_display ) : ?>
				<span class="stvl-video-index"><?php echo esc_html( $index_display ); ?></span>
			<?php endif; ?>
			<?php if ( 'yes' === ( $settings['show_duration'] ?? 'yes' ) && ! empty( $video['duration'] ) ) : ?>
				<span class="stvl-video-duration"><?php echo esc_html( $video['duration'] ); ?></span>
			<?php endif; ?>
			<?php if ( 'yes' === ( $settings['show_play_icon'] ?? 'yes' ) ) : ?>
				<button
					type="button"
					class="stvl-play"
					aria-label="<?php echo esc_attr( $video['aria_label'] ); ?>"
					data-stvl-open-video
					data-behavior="<?php echo esc_attr( $video['open_behavior'] ); ?>"
					data-embed-url="<?php echo esc_attr( $video['embed_url'] ); ?>"
					data-video-url="<?php echo esc_attr( $video['video_url'] ); ?>"
					data-action-url="<?php echo esc_attr( $action_url ); ?>"
					data-title="<?php echo esc_attr( $video['title'] ); ?>"
					data-description="<?php echo esc_attr( $video['description'] ); ?>"
				>
					<span class="stvl-play-icon" aria-hidden="true"></span>
				</button>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<div class="stvl-video-body">
		<?php if ( 'yes' === ( $settings['show_category_chip'] ?? 'yes' ) && ! empty( $video['category_label'] ) ) : ?>
			<span class="stvl-chip"><?php echo esc_html( $video['category_label'] ); ?></span>
		<?php endif; ?>

		<?php if ( 'yes' === ( $settings['show_source_name'] ?? 'yes' ) && ! empty( $video['source_name'] ) ) : ?>
			<div class="stvl-video-source"><?php echo esc_html( $video['source_name'] ); ?></div>
		<?php endif; ?>

		<?php if ( 'yes' === ( $settings['show_title'] ?? 'yes' ) ) : ?>
			<<?php echo tag_escape( $title_tag ); ?> class="stvl-video-title"><?php echo esc_html( $video['title'] ); ?></<?php echo tag_escape( $title_tag ); ?>>
		<?php endif; ?>

		<?php if ( 'yes' === ( $settings['show_description'] ?? 'yes' ) && ! empty( $video['description'] ) ) : ?>
			<p class="stvl-video-description"><?php echo esc_html( $video['description'] ); ?></p>
		<?php endif; ?>

		<?php if ( 'yes' === ( $settings['show_cta'] ?? 'yes' ) ) : ?>
			<div class="stvl-video-actions">
				<button
					type="button"
					class="stvl-card-link"
					data-stvl-open-video
					data-behavior="<?php echo esc_attr( $video['open_behavior'] ); ?>"
					data-embed-url="<?php echo esc_attr( $video['embed_url'] ); ?>"
					data-video-url="<?php echo esc_attr( $video['video_url'] ); ?>"
					data-action-url="<?php echo esc_attr( $action_url ); ?>"
					data-title="<?php echo esc_attr( $video['title'] ); ?>"
					data-description="<?php echo esc_attr( $video['description'] ); ?>"
				>
					<?php echo esc_html( $video['button_text'] ); ?>
				</button>
				<?php if ( ! empty( $video['duration'] ) ) : ?>
					<span class="stvl-action-duration"><?php echo esc_html( $video['duration'] ); ?></span>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
</article>
