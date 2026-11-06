<?php
/**
 * Template Name: Fixed-price fixes (shop)
 *
 * WooCommerce products from the "fixes" category as cards. "Buy now" goes straight to
 * checkout; "Add to order" adds to the cart without leaving the page (Store API).
 * Without WooCommerce the page falls back to a plain "Start a project" message.
 *
 * @package elisa-dela-cruz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$has_shop    = function_exists( 'edc_fix_products' );
$products    = $has_shop ? array_map( 'edc_fix_product', edc_fix_products() ) : array();
$summary     = $has_shop ? edc_cart_summary() : array( 'count' => 0, 'total' => '' );
$terms       = get_page_by_path( 'service-terms' );
$terms_url   = ( $terms && 'publish' === $terms->post_status ) ? get_permalink( $terms ) : '';
$contact_url = edc_contact_url();

get_header();
?>
<main id="main">
<?php while ( have_posts() ) : the_post(); ?>
<section class="page-hero fixes-hero">
  <div class="wrap">
    <?php get_template_part( 'template-parts/common/path-eyebrow', null, array( 'path' => 'fixes' ) ); ?>
    <h1><?php the_title(); ?></h1>
    <p class="lede">Clearly scoped jobs you can buy straight away. Pay, tell me the site, and I'll take it from there. No calls needed.</p>
    <ol class="fix-steps" aria-label="How it works">
      <li><b>01</b> Pick a fix and pay</li>
      <li><b>02</b> I email you within one working day for access</li>
      <li><b>03</b> I do the work and send a short report</li>
    </ol>
  </div>
</section>

<section class="sec page-sec fixes-sec">
  <div class="wrap">
  <?php if ( $products ) : ?>
    <div class="fixes">
      <?php
      foreach ( $products as $p ) :
        $multi = $p['max_qty'] > 1;
        ?>
      <article class="package fix" id="fix-<?php echo esc_attr( $p['slug'] ); ?>" data-reveal>
        <h2 class="h3"><?php echo esc_html( $p['name'] ); ?></h2>
        <p class="price"><b><?php echo wp_kses_post( $p['price_html'] ); ?></b><?php if ( $p['unit'] ) : ?> <span><?php echo esc_html( $p['unit'] ); ?></span><?php endif; ?></p>
        <?php if ( $p['summary'] ) : ?><p class="summary"><?php echo esc_html( $p['summary'] ); ?></p><?php endif; ?>
        <?php if ( $p['note'] ) : ?><p class="note"><?php echo esc_html( $p['note'] ); ?></p><?php endif; ?>
        <?php if ( $p['included'] ) : ?>
        <ul class="inc"><?php foreach ( $p['included'] as $item ) : ?><li><?php echo esc_html( $item ); ?></li><?php endforeach; ?></ul>
        <?php endif; ?>
        <?php if ( $p['excluded'] ) : ?>
        <p class="exc"><b>Not included:</b> <?php echo esc_html( implode( ' · ', $p['excluded'] ) ); ?></p>
        <?php endif; ?>
        <?php if ( $p['turnaround'] ) : ?><p class="turn"><?php echo esc_html( $p['turnaround'] ); ?></p><?php endif; ?>

        <?php if ( $p['buyable'] ) : ?>
        <form class="fix-buy" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" method="get" data-product="<?php echo (int) $p['id']; ?>" data-price="<?php echo esc_attr( $p['price'] ); ?>">
          <input type="hidden" name="add-to-cart" value="<?php echo (int) $p['id']; ?>">
          <input type="hidden" name="edc_buy" value="1">
          <?php if ( $multi ) : ?>
          <label class="qty"><span><?php echo 'per hour' === $p['unit'] ? 'Hours' : 'Quantity'; ?></span>
            <select name="quantity">
              <?php for ( $n = 1; $n <= $p['max_qty']; $n++ ) : ?><option value="<?php echo (int) $n; ?>"><?php echo (int) $n; ?></option><?php endfor; ?>
            </select>
          </label>
          <?php else : ?>
          <input type="hidden" name="quantity" value="1">
          <?php endif; ?>
          <button class="btn btn-primary" type="submit" data-ga-event="fix_buy_now" data-ga-label="<?php echo esc_attr( $p['name'] ); ?>">Buy now <span class="arr" aria-hidden="true">→</span><span class="sr-only"> (<?php echo esc_html( $p['name'] ); ?>)</span></button>
          <button class="btn btn-ghost add-to-order" type="button" hidden data-ga-event="fix_add_to_order" data-ga-label="<?php echo esc_attr( $p['name'] ); ?>">Add to order<span class="sr-only"> (<?php echo esc_html( $p['name'] ); ?>)</span></button>
        </form>
        <?php elseif ( $p['external'] ) : ?>
          <?php $href = $p['ext_url'] ? $p['ext_url'] : ( $contact_url ? add_query_arg( 'package', $p['slug'], $contact_url ) : '' ); ?>
          <?php if ( $href ) : ?>
        <a class="btn btn-primary" href="<?php echo esc_url( $href ); ?>"<?php echo $p['ext_url'] ? ' rel="noopener"' : ''; ?> data-ga-event="fix_buy_now" data-ga-label="<?php echo esc_attr( $p['name'] ); ?>"><?php echo esc_html( $p['button'] ? $p['button'] : 'Buy now' ); ?> <span class="arr" aria-hidden="true">→</span></a>
          <?php endif; ?>
        <?php endif; ?>
      </article>
      <?php endforeach; ?>
    </div>

    <p class="pkg-terms">
      Prices in USD. Fixed prices cover the scope listed.
      <?php if ( $terms_url ) : ?><a href="<?php echo esc_url( $terms_url ); ?>">Read the service terms</a>.<?php endif; ?>
      Bigger work, like migrations or a full theme, <?php if ( $contact_url ) : ?><a href="<?php echo esc_url( $contact_url ); ?>">starts with a conversation</a><?php else : ?>starts with a conversation<?php endif; ?>.
    </p>
  <?php else : ?>
    <p class="empty-note">The shop is closed for now. <?php if ( $contact_url ) : ?><a class="link-arrow" href="<?php echo esc_url( $contact_url ); ?>">Tell me what you need <span class="arr" aria-hidden="true">→</span></a><?php endif; ?></p>
  <?php endif; ?>
  </div>
</section>

<?php if ( $products && $has_shop ) : ?>
<div class="order-bar" role="region" aria-label="Your order"<?php echo $summary['count'] ? '' : ' hidden'; ?>>
  <p class="order-sum" aria-live="polite"><span class="order-count"><?php echo (int) $summary['count']; ?></span> <span class="order-noun"><?php echo 1 === $summary['count'] ? 'item' : 'items'; ?></span> · <b class="order-total"><?php echo esc_html( $summary['total'] ); ?></b></p>
  <a class="btn btn-primary" href="<?php echo esc_url( wc_get_checkout_url() ); ?>" data-ga-event="fix_checkout">Checkout <span class="arr" aria-hidden="true">→</span></a>
</div>
<?php endif; ?>
<?php endwhile; ?>
</main>
<?php
get_footer();
