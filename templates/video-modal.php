<?php
/**
 * Modal template.
 *
 * @var string $gallery_id
 *
 * @package StephaneVideoLibrary
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="stvl-modal" id="<?php echo esc_attr( $gallery_id ); ?>-modal" hidden>
	<div class="stvl-modal-overlay" data-stvl-close-modal></div>
	<div class="stvl-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="<?php echo esc_attr( $gallery_id ); ?>-modal-title">
		<button type="button" class="stvl-modal-close" aria-label="<?php esc_attr_e( 'Fermer la video', 'stephane-video-library' ); ?>" data-stvl-close-modal>×</button>
		<h3 id="<?php echo esc_attr( $gallery_id ); ?>-modal-title" class="stvl-modal-title"></h3>
		<div class="stvl-modal-frame" data-stvl-modal-frame></div>
		<div class="stvl-modal-description" data-stvl-modal-description></div>
	</div>
</div>
