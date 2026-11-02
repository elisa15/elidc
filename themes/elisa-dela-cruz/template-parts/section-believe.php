<?php
/**
 * "What I believe" editorial statement.
 *
 * @package elisa-dela-cruz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<section class="believe" data-reveal="up">
  <div class="wrap">
    <p class="believe-text"><?php echo wp_kses_post( edc_opt( 'believe_text', 'Good websites are beautiful.<br>Great websites solve problems.<br>The best websites do <em class="grad-text" style="font-style:italic;">both</em>.' ) ); ?></p>
  </div>
</section>
