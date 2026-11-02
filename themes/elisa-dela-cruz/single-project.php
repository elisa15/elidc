<?php
/**
 * Single project (case study) template.
 *
 * @package elisa-dela-cruz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();

	$industry_badge = edc_field( 'industry_badge', '' );
	$platform_badge = edc_field( 'platform_badge', '' );
	$role           = edc_field( 'role', '' );
	$gradient_start = edc_field( 'gradient_start', '' );
	$gradient_end   = edc_field( 'gradient_end', '' );
	$thumb_url      = get_the_post_thumbnail_url( get_the_ID(), 'edc-project-hero' );

	if ( $thumb_url ) {
		$visual_style = 'background-image:url(' . esc_url( $thumb_url ) . ');';
	} elseif ( $gradient_start && $gradient_end ) {
		$visual_style = 'background:linear-gradient(135deg,' . esc_attr( $gradient_start ) . ',' . esc_attr( $gradient_end ) . ');';
	} else {
		$visual_style = 'background:linear-gradient(135deg,var(--magenta),var(--violet));';
	}
	?>
	<section class="single-project-hero">
		<div class="wrap" style="max-width:900px;">
			<a href="<?php echo esc_url( home_url( '/#work' ) ); ?>" class="single-project-back" data-reveal="up">
				<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg>
				Back to work
			</a>
			<div class="case-badges" data-reveal="up">
				<?php if ( $industry_badge ) : ?><span class="p-badge"><?php echo esc_html( $industry_badge ); ?></span><?php endif; ?>
				<?php if ( $platform_badge ) : ?><span class="p-badge alt"><?php echo esc_html( $platform_badge ); ?></span><?php endif; ?>
			</div>
			<h1 class="case-title" style="font-size:clamp(32px,5vw,54px);" data-reveal="up"><?php the_title(); ?></h1>
			<?php if ( $role ) : ?><div class="case-role" data-reveal="up"><?php echo esc_html( $role ); ?></div><?php endif; ?>

			<div class="single-project-visual" style="<?php echo esc_attr( $visual_style ); ?>" data-reveal="scale">
				<?php if ( ! $thumb_url ) : ?>
					<div class="img-placeholder" style="position:static;height:100%;"><svg viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg><span>Add project screenshot</span></div>
				<?php endif; ?>
			</div>

			<?php if ( $industry_badge || $platform_badge || $role ) : ?>
			<div class="single-project-meta" data-reveal="up">
				<?php if ( $industry_badge ) : ?><div><span>Industry</span><strong><?php echo esc_html( $industry_badge ); ?></strong></div><?php endif; ?>
				<?php if ( $platform_badge ) : ?><div><span>Platform</span><strong><?php echo esc_html( $platform_badge ); ?></strong></div><?php endif; ?>
				<?php if ( $role ) : ?><div><span>Role</span><strong><?php echo esc_html( $role ); ?></strong></div><?php endif; ?>
			</div>
			<?php endif; ?>

			<div class="single-project-body" data-reveal="up">
				<?php the_content(); ?>
			</div>

			<div style="text-align:center;margin-top:56px;">
				<a href="#contact" class="btn btn-fill js-open-contact">Start a project like this</a>
			</div>
		</div>
	</section>
	<?php
endwhile;

get_footer();
