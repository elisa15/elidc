<?php
/**
 * Homepage Toolbox: the stack in three orbits, the roles on a project and core skills.
 * The orbit is decoration (aria-hidden); the lists next to it carry the same content.
 *
 * @package elisa-dela-cruz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$groups = array(
	'platform'  => array( 'label' => 'Platform', 'tools' => array() ),
	'interface' => array( 'label' => 'Interface', 'tools' => array() ),
	'build'     => array( 'label' => 'Build & ship', 'tools' => array() ),
);
foreach ( edc_filled_rows( edc_repeater( 'home_tools', array() ), array( 'name' ) ) as $row ) {
	$group = isset( $row['group'], $groups[ $row['group'] ] ) ? $row['group'] : 'platform';
	$groups[ $group ]['tools'][] = trim( $row['name'] );
}
$groups = array_filter( $groups, function ( $g ) {
	return ! empty( $g['tools'] );
} );
$roles  = edc_filled_rows( edc_repeater( 'home_roles', array() ), array( 'title' ) );
$skills = edc_filled_rows( edc_repeater( 'home_skills', array() ), array( 'name' ) );

if ( ! $groups && ! $roles && ! $skills ) {
	return;
}

$heading        = edc_opt( 'home_tools_heading' );
$intro          = edc_opt( 'home_tools_intro' );
$roles_heading  = edc_opt( 'home_roles_heading' );
$skills_heading = edc_opt( 'home_skills_heading' );
$ring           = 0;
?>
<section class="sec toolbox" id="stack">
  <div class="wrap">
    <div class="tool-top">
      <div data-reveal>
        <?php get_template_part( 'template-parts/common/path-eyebrow', null, array( 'path' => 'stack' ) ); ?>
        <?php if ( $heading ) : ?>
        <h2><?php echo edc_inline_html( $heading ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_kses. ?></h2>
        <?php endif; ?>
        <?php if ( $intro ) : ?>
        <p class="sub"><?php echo esc_html( $intro ); ?></p>
        <?php endif; ?>
        <?php if ( $groups ) : ?>
        <div class="tool-groups">
          <?php foreach ( $groups as $key => $group ) : ?>
          <div class="tool-group">
            <h3><?php echo esc_html( $group['label'] ); ?></h3>
            <ul class="chips">
              <?php foreach ( $group['tools'] as $tool ) : ?><li><?php echo esc_html( $tool ); ?></li><?php endforeach; ?>
            </ul>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>
      <?php if ( $groups ) : ?>
      <div class="orbit" aria-hidden="true" data-reveal>
        <div class="core"><span>{%}</span><b>elisa.os</b></div>
        <?php
        foreach ( $groups as $group ) :
          $ring++;
          $count = count( $group['tools'] );
          ?>
        <div class="ring r<?php echo (int) $ring; ?>">
          <?php foreach ( $group['tools'] as $i => $tool ) : ?>
          <span class="sat" style="--a:<?php echo esc_attr( round( 360 / $count * $i + $ring * 23 ) ); ?>deg"><i><?php echo esc_html( $tool ); ?></i></span>
          <?php endforeach; ?>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>

    <?php if ( $roles ) : ?>
    <div class="roles-block">
      <?php if ( $roles_heading ) : ?>
      <h3 class="block-h" data-reveal><?php echo esc_html( $roles_heading ); ?></h3>
      <?php endif; ?>
      <ol class="roles">
        <?php foreach ( $roles as $i => $row ) : ?>
        <li data-reveal><span class="n"><?php echo esc_html( sprintf( '%02d', $i + 1 ) ); ?></span><b><?php echo esc_html( trim( $row['title'] ) ); ?></b><?php if ( ! empty( $row['role'] ) ) : ?><i><?php echo esc_html( trim( $row['role'] ) ); ?></i><?php endif; ?></li>
        <?php endforeach; ?>
      </ol>
    </div>
    <?php endif; ?>

    <?php if ( $skills ) : ?>
    <div class="skills-block" data-reveal>
      <?php if ( $skills_heading ) : ?>
      <h3 class="block-h"><?php echo esc_html( $skills_heading ); ?></h3>
      <?php endif; ?>
      <ul class="chips skills">
        <?php foreach ( $skills as $row ) : ?><li><?php echo esc_html( trim( $row['name'] ) ); ?></li><?php endforeach; ?>
      </ul>
    </div>
    <?php endif; ?>
  </div>
</section>
