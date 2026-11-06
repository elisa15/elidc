<?php
/**
 * "Recent projects" horizontal case-study carousel.
 * Pulls from the 'project' custom post type via WP_Query.
 * Renders nothing until at least one project is published: the section
 * never shows placeholder projects or invented outcomes.
 *
 * @package elisa-dela-cruz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$projects_query = new WP_Query( array(
	'post_type'      => 'project',
	'posts_per_page' => 8,
	'orderby'        => 'menu_order date',
	'order'          => 'ASC',
) );


if ( ! $projects_query->have_posts() ) {
	return;
}
$slide_count = $projects_query->post_count;
?>
<section id="work">
  <div class="wrap">
    <div class="section-head" data-reveal="up">
      <div class="eyebrow"><?php echo esc_html( edc_opt( 'work_eyebrow', 'Selected work' ) ); ?></div>
      <h2><?php echo esc_html( edc_opt( 'work_heading', 'Recent projects' ) ); ?></h2>
    </div>

    <div class="case-carousel" data-reveal="up">
      <div class="case-track" id="caseTrack">
          <?php while ( $projects_query->have_posts() ) : $projects_query->the_post();
            $industry_badge = edc_field( 'industry_badge', '' );
            $platform_badge = edc_field( 'platform_badge', '' );
            $role           = edc_field( 'role', '' );
            $summary        = edc_field( 'summary', get_the_excerpt() ?: '' );
            $thumb_url      = get_the_post_thumbnail_url( get_the_ID(), 'edc-case-card' );
            $gradient_start = edc_field( 'gradient_start', '' );
            $gradient_end   = edc_field( 'gradient_end', '' );
            if ( $thumb_url ) {
              $visual_style = 'background-image:url(' . esc_url( $thumb_url ) . ');';
            } elseif ( $gradient_start && $gradient_end ) {
              $visual_style = 'background:linear-gradient(135deg,' . esc_attr( $gradient_start ) . ',' . esc_attr( $gradient_end ) . ');';
            } else {
              $visual_style = 'background:linear-gradient(135deg,var(--magenta),var(--violet));';
            }
          ?>
            <article class="case-slide">
              <a href="<?php the_permalink(); ?>" class="case-visual" style="<?php echo esc_attr( $visual_style ); ?>">
                <?php if ( ! $thumb_url ) : ?>
                  <div class="img-placeholder"><svg viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg><span>Add project screenshot</span></div>
                <?php endif; ?>
              </a>
              <div class="case-text">
                <div class="case-badges">
                  <?php if ( $industry_badge ) : ?><span class="p-badge"><?php echo esc_html( $industry_badge ); ?></span><?php endif; ?>
                  <?php if ( $platform_badge ) : ?><span class="p-badge alt"><?php echo esc_html( $platform_badge ); ?></span><?php endif; ?>
                </div>
                <h3 class="case-title"><?php the_title(); ?></h3>
                <?php if ( $role ) : ?><div class="case-role"><?php echo esc_html( $role ); ?></div><?php endif; ?>
                <p class="case-summary"><?php echo esc_html( $summary ); ?></p>
                <a href="<?php the_permalink(); ?>" class="work-link" data-ga-event="view_case_study" data-ga-label="View case study">View case study <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
              </div>
            </article>
          <?php endwhile; wp_reset_postdata(); ?>
      </div>

      <div class="case-controls">
        <button class="case-arrow" id="casePrev" aria-label="Previous project"><svg viewBox="0 0 24 24"><path d="M15 18l-6-6 6-6"/></svg></button>
        <div class="case-dots" id="caseDots">
          <?php for ( $i = 0; $i < $slide_count; $i++ ) : ?>
            <span class="case-dot<?php echo ( 0 === $i ) ? ' active' : ''; ?>" data-i="<?php echo intval( $i ); ?>"></span>
          <?php endfor; ?>
        </div>
        <button class="case-arrow" id="caseNext" aria-label="Next project"><svg viewBox="0 0 24 24"><path d="M9 18l6-6-6-6"/></svg></button>
      </div>
    </div>
  </div>
</section>
