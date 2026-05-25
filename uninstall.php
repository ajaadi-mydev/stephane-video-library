<?php
/**
 * Uninstall routine.
 *
 * @package StephaneVideoLibrary
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

$option = get_option( 'stvl_settings', array() );

if ( empty( $option['delete_data_on_uninstall'] ) ) {
	return;
}

$posts = get_posts(
	array(
		'post_type'      => 'st_video',
		'post_status'    => 'any',
		'numberposts'    => -1,
		'fields'         => 'ids',
		'suppress_filters' => false,
	)
);

foreach ( $posts as $post_id ) {
	wp_delete_post( $post_id, true );
}

delete_option( 'stvl_settings' );
