<?php
/**
 * Case study. Only reached when has_case_study is on: inc/projects-query.php
 * redirects every other project to its card on /projects/.
 * See docs/redesign/04-build-spec.md §3.
 *
 * @package elisa-dela-cruz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<main id="main">
<?php
while ( have_posts() ) :
	the_post();

	$id      = get_the_ID();
	$project = edc_project( $id );
	if ( ! $project ) {
		continue;
	}
	$archive = get_post_type_archive_link( 'project' );

	$headline = trim( (string) edc_field( 'cs_headline', '', $id ) );
	$lede     = trim( (string) edc_field( 'cs_lede', '', $id ) );
	$glance   = edc_filled_rows( edc_repeater( 'at_a_glance', array(), $id ), array( 'label', 'value' ) );
	$approach = edc_filled_rows( edc_repeater( 'approach', array(), $id ), array( 'step' ) );
	$learned  = trim( (string) edc_field( 'learned', '', $id ) );
	$quote    = edc_testimonial( (int) edc_field( 'cs_testimonial', 0, $id ) );

	$rich = function ( $name ) use ( $id ) {
		$value = edc_field( $name, '', $id );
		return is_string( $value ) && '' !== trim( wp_strip_all_tags( $value ) ) ? wp_kses_post( $value ) : '';
	};
	$situation = $rich( 'situation' );
	$tricky    = $rich( 'tricky' );
	$technical = $rich( 'technical_breakdown' );
	$result    = $rich( 'result' );

	$next = edc_next_case_study( $id );

	$contact_url = edc_contact_url();
	$has_modal   = edc_should_show_section( 'show_contact_modal' );
	?>
<section class="cs-hero">
  <div class="wrap">
    <p class="crumbs"><a href="<?php echo esc_url( $archive ); ?>">~/elisa/work</a> / <?php echo esc_html( $project['slug'] ); ?></p>
    <?php if ( $project['kinds'] || $project['platforms'] || $project['chips'] ) : ?>
    <div class="tags">
      <?php foreach ( $project['kinds'] as $kind ) : ?><span class="tag <?php echo esc_attr( $kind['slug'] ); ?>"><?php echo esc_html( $kind['name'] ); ?></span><?php endforeach; ?>
      <?php foreach ( $project['platforms'] as $platform ) : ?><span class="tag"><?php echo esc_html( $platform['name'] ); ?></span><?php endforeach; ?>
      <?php foreach ( $project['chips'] as $chip ) : ?><span class="tag"><?php echo esc_html( $chip ); ?></span><?php endforeach; ?>
    </div>
    <?php endif; ?>
    <h1><?php echo '' !== $headline ? edc_inline_html( $headline ) : esc_html( $project['title'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_kses. ?></h1>
    <?php if ( '' !== $lede ) : ?>
    <p class="lede"><?php echo esc_html( $lede ); ?></p>
    <?php endif; ?>
  </div>
</section>

<section class="sec cs-main">
  <div class="wrap">
    <?php if ( 'none' !== $project['visual']['mode'] ) : ?>
    <figure class="cs-shot">
      <div class="shot">
        <div class="shot-bar" aria-hidden="true"><i></i><i></i><i></i><?php if ( $project['visual']['browser_label'] ) : ?><span><?php echo esc_html( $project['visual']['browser_label'] ); ?></span><?php endif; ?></div>
        <?php get_template_part( 'template-parts/project/visual', null, array( 'project' => $project ) ); ?>
      </div>
    </figure>
    <?php endif; ?>

    <div class="cs-layout<?php echo $glance ? '' : ' no-glance'; ?>">
      <?php if ( $glance ) : ?>
      <aside class="glance" aria-labelledby="glance-h">
        <h2 id="glance-h">At a glance</h2>
        <dl>
          <?php foreach ( $glance as $row ) : ?>
          <div><dt><?php echo esc_html( rtrim( trim( $row['label'] ), ':' ) ); ?>:</dt><dd><?php echo esc_html( $row['value'] ); ?></dd></div>
          <?php endforeach; ?>
        </dl>
      </aside>
      <?php endif; ?>

      <article class="cs-body">
        <?php if ( $situation ) : ?>
        <h2>The situation</h2>
        <?php echo $situation; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_kses_post. ?>
        <?php endif; ?>

        <?php if ( $tricky ) : ?>
        <h2>What made it tricky</h2>
        <?php echo $tricky; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_kses_post. ?>
        <?php endif; ?>

        <?php if ( $approach ) : ?>
        <h2>What I did</h2>
        <ol>
          <?php foreach ( $approach as $row ) : ?>
          <li><?php echo edc_inline_html( nl2br( trim( $row['step'] ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_kses. ?></li>
          <?php endforeach; ?>
        </ol>
        <?php endif; ?>

        <?php if ( $technical ) : ?>
        <details class="tech">
          <summary>▸ Technical breakdown</summary>
          <div class="in"><?php echo $technical; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_kses_post. ?></div>
        </details>
        <?php endif; ?>

        <?php if ( $result ) : ?>
        <h2>The result</h2>
        <?php echo $result; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_kses_post. ?>
        <?php endif; ?>

        <?php if ( '' !== $learned ) : ?>
        <h2>What I'd tell the next client</h2>
        <p class="voice-person"><?php echo esc_html( $learned ); ?></p>
        <?php endif; ?>

        <?php if ( $quote ) : ?>
        <div class="cs-quote">
          <?php get_template_part( 'template-parts/common/testimonial', null, array( 'testimonial' => $quote ) ); ?>
        </div>
        <?php endif; ?>

        <?php if ( $next || $contact_url || $has_modal ) : ?>
        <div class="cs-next">
          <?php if ( $next ) : ?>
          <a href="<?php echo esc_url( get_permalink( $next ) ); ?>" data-ga-event="case_study_open" data-ga-label="Next <?php echo esc_attr( get_the_title( $next ) ); ?>"><small>Next case study</small><strong><?php echo esc_html( get_the_title( $next ) ); ?> →</strong></a>
          <?php endif; ?>
          <?php if ( $contact_url || $has_modal ) : ?>
          <a class="<?php echo $has_modal ? 'js-open-contact' : ''; ?>" href="<?php echo esc_url( $contact_url ? $contact_url : home_url( '/' ) ); ?>" data-ga-event="cta_start_project" data-ga-label="Case study Start a project"><small>Got one like this?</small><strong>Start a project →</strong></a>
          <?php endif; ?>
        </div>
        <?php endif; ?>
      </article>
    </div>
  </div>
</section>
	<?php
endwhile;
?>
</main>
<?php
get_footer();
