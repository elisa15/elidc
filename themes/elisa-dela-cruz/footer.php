<?php
/**
 * Footer template.
 *
 * @package elisa-dela-cruz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$edc_footer_links = edc_footer_nav_items();
$edc_elsewhere    = edc_footer_elsewhere_links();
?>

<footer class="site-footer">
  <div class="wrap">
    <span>&copy; <?php echo esc_html( wp_date( 'Y' ) . ' ' . edc_profile( 'profile_name', 'Elisa A. Dela Cruz' ) ); ?></span>
    <?php if ( $edc_footer_links || $edc_elsewhere ) : ?>
    <nav aria-label="Footer">
      <?php foreach ( $edc_footer_links as $edc_link ) : ?>
      <a href="<?php echo esc_url( $edc_link['url'] ); ?>"<?php echo $edc_link['current'] ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $edc_link['label'] ); ?></a>
      <?php endforeach; ?>
      <?php foreach ( $edc_elsewhere as $edc_link ) : ?>
      <a href="<?php echo esc_url( $edc_link['url'] ); ?>"<?php echo $edc_link['attrs']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from fixed strings in edc_footer_elsewhere_links(). ?>><?php echo esc_html( $edc_link['label'] ); ?></a>
      <?php endforeach; ?>
    </nav>
    <?php endif; ?>
  </div>
</footer>

<?php
// The Contact page has the form inline, so it skips the modal (one form, unique field IDs).
if ( edc_should_show_section( 'show_contact_modal' ) && ! is_page_template( 'page-templates/contact.php' ) ) {
	get_template_part( 'template-parts/modal-contact' );
}
?>

<a href="#main" class="back-to-top" id="backToTop" aria-label="Back to top" data-ga-event="back_to_top" data-ga-label="Back to top">
  <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M12 5l-7 7h4.5v7h5v-7H19z"/></svg>
</a>

<?php wp_footer(); ?>
</body>
</html>
