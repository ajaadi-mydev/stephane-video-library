<?php
/**
 * Helper utilities.
 *
 * @package StephaneVideoLibrary
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class STVL_Helpers {

	/**
	 * Detect video provider from URL.
	 *
	 * @param string $url Video URL.
	 * @return string
	 */
	public static function get_video_provider( $url ) {
		$url = trim( (string) $url );

		if ( empty( $url ) ) {
			return 'unknown';
		}

		$host = wp_parse_url( $url, PHP_URL_HOST );
		$path = wp_parse_url( $url, PHP_URL_PATH );

		if ( preg_match( '#(youtube\.com|youtu\.be|youtube-nocookie\.com)#i', (string) $host ) ) {
			return 'youtube';
		}

		if ( preg_match( '#vimeo\.com#i', (string) $host ) ) {
			return 'vimeo';
		}

		if ( preg_match( '#\.(mp4|webm|ogg)$#i', (string) $path ) ) {
			return 'self_hosted';
		}

		if ( filter_var( $url, FILTER_VALIDATE_URL ) ) {
			return 'external';
		}

		return 'unknown';
	}

	/**
	 * Extracts a YouTube ID from URL.
	 *
	 * @param string $url Video URL.
	 * @return string
	 */
	public static function extract_youtube_id( $url ) {
		$patterns = array(
			'#youtube\.com/watch\?v=([a-zA-Z0-9_-]{11})#',
			'#youtu\.be/([a-zA-Z0-9_-]{11})#',
			'#youtube\.com/embed/([a-zA-Z0-9_-]{11})#',
			'#youtube\.com/shorts/([a-zA-Z0-9_-]{11})#',
		);

		foreach ( $patterns as $pattern ) {
			if ( preg_match( $pattern, $url, $matches ) ) {
				return $matches[1];
			}
		}

		$query = wp_parse_url( $url, PHP_URL_QUERY );

		if ( $query ) {
			parse_str( $query, $params );
			if ( ! empty( $params['v'] ) ) {
				return sanitize_text_field( $params['v'] );
			}
		}

		return '';
	}

	/**
	 * Extracts a Vimeo ID from URL.
	 *
	 * @param string $url Video URL.
	 * @return string
	 */
	public static function extract_vimeo_id( $url ) {
		if ( preg_match( '#vimeo\.com/(?:video/)?([0-9]+)#', $url, $matches ) ) {
			return $matches[1];
		}

		return '';
	}

	/**
	 * Returns embed URL.
	 *
	 * @param string $provider Provider.
	 * @param string $video_id Video ID.
	 * @param bool   $privacy_mode Privacy mode.
	 * @param bool   $autoplay Autoplay.
	 * @return string
	 */
	public static function get_embed_url( $provider, $video_id, $privacy_mode = true, $autoplay = false ) {
		$autoplay_arg = $autoplay ? '1' : '0';

		if ( 'youtube' === $provider && ! empty( $video_id ) ) {
			$domain = $privacy_mode ? 'https://www.youtube-nocookie.com' : 'https://www.youtube.com';
			return $domain . '/embed/' . rawurlencode( $video_id ) . '?rel=0&autoplay=' . $autoplay_arg;
		}

		if ( 'vimeo' === $provider && ! empty( $video_id ) ) {
			return 'https://player.vimeo.com/video/' . rawurlencode( $video_id ) . '?autoplay=' . $autoplay_arg;
		}

		return '';
	}

	/**
	 * Resolves thumbnail URL.
	 *
	 * @param int $post_id Post ID.
	 * @return string
	 */
	public static function get_thumbnail_url( $post_id ) {
		$custom_thumbnail_id = (int) get_post_meta( $post_id, '_stvl_video_custom_thumbnail', true );

		if ( $custom_thumbnail_id ) {
			$image = wp_get_attachment_image_url( $custom_thumbnail_id, 'large' );
			if ( $image ) {
				return $image;
			}
		}

		if ( has_post_thumbnail( $post_id ) ) {
			$image = get_the_post_thumbnail_url( $post_id, 'large' );
			if ( $image ) {
				return $image;
			}
		}

		$provider = get_post_meta( $post_id, '_stvl_video_provider', true );
		$video_id  = get_post_meta( $post_id, '_stvl_video_id', true );

		if ( 'youtube' === $provider && ! empty( $video_id ) ) {
			return 'https://i.ytimg.com/vi/' . rawurlencode( $video_id ) . '/maxresdefault.jpg';
		}

		return '';
	}

	/**
	 * Gets the card description.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $source Description source.
	 * @param int    $length Word length.
	 * @return string
	 */
	public static function get_description( $post_id, $source = 'excerpt', $length = 24 ) {
		$text = '';

		switch ( $source ) {
			case 'short_meta':
				$text = get_post_meta( $post_id, '_stvl_video_short_description', true );
				break;
			case 'content':
				$text = get_post_field( 'post_content', $post_id );
				break;
			case 'none':
				$text = '';
				break;
			case 'excerpt':
			default:
				$text = get_the_excerpt( $post_id );
				break;
		}

		$text = wp_strip_all_tags( (string) $text );

		if ( empty( $text ) ) {
			return '';
		}

		return wp_trim_words( $text, $length, '…' );
	}

	/**
	 * Renders template file.
	 *
	 * @param string $template Template filename.
	 * @param array  $data Data.
	 * @return string
	 */
	public static function render_template( $template, $data = array() ) {
		$path = STVL_PATH . 'templates/' . ltrim( $template, '/' );

		if ( ! file_exists( $path ) ) {
			return '';
		}

		ob_start();
		extract( $data, EXTR_SKIP );
		include $path;
		return (string) ob_get_clean();
	}

	/**
	 * Builds a normalized video object.
	 *
	 * @param int   $post_id Post ID.
	 * @param array $settings Display settings.
	 * @param int   $index Display index.
	 * @return array
	 */
	public static function get_video_data( $post_id, $settings = array(), $index = 0 ) {
		$provider      = get_post_meta( $post_id, '_stvl_video_provider', true );
		$video_id      = get_post_meta( $post_id, '_stvl_video_id', true );
		$video_url     = get_post_meta( $post_id, '_stvl_video_url', true );
		$external_url  = get_post_meta( $post_id, '_stvl_video_external_url', true );
		$button_text   = get_post_meta( $post_id, '_stvl_video_button_text', true );
		$open_behavior = get_post_meta( $post_id, '_stvl_video_open_behavior', true );
		$duration      = get_post_meta( $post_id, '_stvl_video_duration', true );
		$source_name   = get_post_meta( $post_id, '_stvl_video_source_name', true );
		$category      = get_the_terms( $post_id, STVL_TAX_CATEGORY );
		$topic         = get_the_terms( $post_id, STVL_TAX_TOPIC );
		$privacy_mode  = isset( $settings['privacy_mode'] ) ? 'yes' === $settings['privacy_mode'] : true;
		$autoplay      = isset( $settings['autoplay_modal'] ) ? 'yes' === $settings['autoplay_modal'] : false;

		$link_behavior = ! empty( $settings['link_behavior'] ) ? $settings['link_behavior'] : 'individual';

		if ( 'individual' === $link_behavior ) {
			$link_behavior = $open_behavior ? $open_behavior : 'modal';
		}

		if ( empty( $video_url ) && ! empty( $external_url ) && 'modal' === $link_behavior ) {
			$link_behavior = 'new_tab';
		}

		$category_names = wp_list_pluck( is_array( $category ) ? $category : array(), 'name' );
		$topic_names    = wp_list_pluck( is_array( $topic ) ? $topic : array(), 'name' );
		$category_label = ! empty( $category_names[0] ) ? $category_names[0] : '';
		$embed_url      = self::get_embed_url( $provider, $video_id, $privacy_mode, $autoplay );

		return array(
			'id'              => $post_id,
			'title'           => get_the_title( $post_id ),
			'permalink'       => get_permalink( $post_id ),
			'description'     => self::get_description(
				$post_id,
				isset( $settings['description_source'] ) ? $settings['description_source'] : 'excerpt',
				isset( $settings['description_length'] ) ? (int) $settings['description_length'] : 24
			),
			'thumbnail'       => self::get_thumbnail_url( $post_id ),
			'provider'        => $provider ? $provider : 'unknown',
			'video_id'        => $video_id,
			'video_url'       => $video_url,
			'external_url'    => $external_url,
			'embed_url'       => $embed_url,
			'duration'        => $duration,
			'source_name'     => $source_name,
			'category_label'  => $category_label,
			'category_names'  => $category_names,
			'topic_names'     => $topic_names,
			'index'           => $index,
			'button_text'     => ! empty( $settings['global_button_text'] ) && 'global' === ( $settings['button_text_source'] ?? 'global' ) ? $settings['global_button_text'] : ( $button_text ? $button_text : __( 'Voir la video', 'stephane-video-library' ) ),
			'open_behavior'   => $link_behavior,
			'is_featured'     => (bool) get_post_meta( $post_id, '_stvl_video_featured', true ),
			'aria_label'      => sprintf( __( 'Lire la video : %s', 'stephane-video-library' ), get_the_title( $post_id ) ),
		);
	}

	/**
	 * Formats visible index.
	 *
	 * @param int    $index Index.
	 * @param string $format Format.
	 * @return string
	 */
	public static function format_index( $index, $format ) {
		$display = (int) $index;

		switch ( $format ) {
			case '#01':
				return '#' . str_pad( (string) $display, 2, '0', STR_PAD_LEFT );
			case '1':
				return (string) $display;
			case 'none':
				return '';
			case '01':
			default:
				return str_pad( (string) $display, 2, '0', STR_PAD_LEFT );
		}
	}

	/**
	 * Builds a fallback SVG data URI.
	 *
	 * @return string
	 */
	public static function get_fallback_thumbnail() {
		$svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 720"><defs><linearGradient id="g" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" stop-color="#0A0A0A"/><stop offset="100%" stop-color="#FF6C00"/></linearGradient></defs><rect width="1200" height="720" rx="40" fill="url(#g)"/><circle cx="600" cy="360" r="84" fill="rgba(255,255,255,.14)"/><polygon points="575,315 575,405 660,360" fill="#FFFFFF"/></svg>';
		return 'data:image/svg+xml;charset=utf-8,' . rawurlencode( $svg );
	}
}
