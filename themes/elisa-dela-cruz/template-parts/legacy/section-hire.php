<?php
/**
 * "Why clients keep coming back" — tabbed section.
 *
 * @package elisa-dela-cruz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$hire_photo = edc_opt( 'hire_photo', '' );

$default_story = array(
	"I don't just build websites.",
	'I deal with the messy stuff that shows up after launch.',
	"WooCommerce to Shopify moves, hacked sites, slow pages, custom features. I like the hard technical problems that actually help a business grow.",
);
$story_raw = edc_opt( 'story_paragraphs', '' );
$story_paragraphs = $story_raw ? array_filter( array_map( 'trim', explode( "\n", $story_raw ) ) ) : $default_story;

$default_stats = array(
	array( 'number' => '13+', 'label' => "Years solving real\nbusiness problems" ),
	array( 'number' => '150+', 'label' => "Projects\ndelivered" ),
	array( 'number' => '4', 'label' => "Countries\nserved" ),
);
$stats = edc_repeater( 'numbers_stats', $default_stats );
$numbers_note = edc_opt( 'numbers_note', 'I work with clients in Australia, the US, the UK and South Africa. Quick replies, and I stick around after the project ships.' );

$default_specialise = array(
	'Shopify development', 'WordPress', 'WooCommerce migration', 'Website rescue',
	'Performance', 'Malware removal', 'Technical SEO', 'Ongoing support',
);
$specialise_rows = edc_repeater( 'specialise_list', array() );
$specialise = array();
if ( ! empty( $specialise_rows ) ) {
	foreach ( $specialise_rows as $row ) {
		$specialise[] = isset( $row['item'] ) ? $row['item'] : '';
	}
} else {
	$specialise = $default_specialise;
}
?>
<section class="light" id="hire">
  <div class="wrap">
    <div class="hire-grid">
      <div data-reveal="scale">
        <div class="blob-shape">
          <div class="core"></div>
          <?php if ( $hire_photo ) : ?>
            <img src="<?php echo esc_url( $hire_photo ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
          <?php else : ?>
            <div class="img-placeholder"><svg viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg><span>Add photo</span></div>
          <?php endif; ?>
        </div>
      </div>
      <div>
        <div class="eyebrow" data-reveal="up"><?php echo esc_html( edc_opt( 'hire_eyebrow', 'A bit about me' ) ); ?></div>
        <h2 class="section-head serif" style="margin-bottom:22px;--i:1" data-reveal="up"><?php echo wp_kses_post( edc_opt( 'hire_heading', 'Why clients keep<br>coming back.' ) ); ?></h2>

        <div class="hire-tabs" data-reveal="up" style="--i:2">
          <div class="tab-nav" role="tablist">
            <button class="tab-btn active" data-tab="story" type="button">My story</button>
            <button class="tab-btn" data-tab="numbers" type="button">By the numbers</button>
            <button class="tab-btn" data-tab="specialise" type="button">What I specialise in</button>
          </div>

          <div class="tab-panel active" data-panel="story">
            <?php foreach ( $story_paragraphs as $i => $paragraph ) : ?>
              <p style="color:#5c5c66;font-size:17px;line-height:1.8;max-width:560px;<?php echo ( $i < count( $story_paragraphs ) - 1 ) ? 'margin-bottom:16px;' : ''; ?>"><?php echo esc_html( $paragraph ); ?></p>
            <?php endforeach; ?>
          </div>

          <div class="tab-panel" data-panel="numbers">
            <div class="stat-row">
              <?php foreach ( $stats as $stat ) :
                $number = isset( $stat['number'] ) ? $stat['number'] : '';
                $label  = isset( $stat['label'] ) ? $stat['label'] : '';
              ?>
                <div class="stat-inline"><div class="num"><?php echo esc_html( $number ); ?></div><div class="lbl"><?php echo wp_kses_post( nl2br( esc_html( $label ) ) ); ?></div></div>
              <?php endforeach; ?>
            </div>
            <p style="color:#8a8a92;font-size:14px;"><?php echo esc_html( $numbers_note ); ?></p>
          </div>

          <div class="tab-panel" data-panel="specialise">
            <div class="specialise-list">
              <?php foreach ( $specialise as $item ) : ?>
                <div><svg viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg><?php echo esc_html( $item ); ?></div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
