<?php 
// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/*  Actions performed to add dynamic css
*  @since   1.0.0
*  @author  Wbcom Designs
*/

function bgr_dynamic_rating_method() {
	global $bgr;
    $rating_color = $bgr['rating_color'];  
        $custom_css = "
                .bgr-filled-bars {
                        background: {$rating_color};
						border: 1px solid {$rating_color};	
                }
				.bgr-bars {
						border: 1px solid {$rating_color};
				}
				.bgr-half-filled-bars {
						border: 1px solid {$rating_color};
						background: -webkit-linear-gradient( left, {$rating_color} 50%, #fff 50% ); /* Chrome10-25, Saf5.1-Saf6, iOS -6.1, Android -4.3 */
						background:    -moz-linear-gradient( left, {$rating_color} 50%, #fff 50% ); /* Fx3.6-15 */
						background:      -o-linear-gradient( left, {$rating_color} 50%, #fff 50% ); /* Op11.10-12.02 */
						background:         linear-gradient( left, {$rating_color} 50%, #fff 50% );
				}
				.bgr-numbers {
						border: 2px solid {$rating_color};
						color: {$rating_color};
				}
				.bgr-filled-numbers {
						border: 2px solid {$rating_color};
						background-color: {$rating_color};		
						color: #fff;	
				}
				.bgr-half-filled-numbers {
						border: 2px solid {$rating_color};						
						color: {$rating_color};												
						background: -webkit-linear-gradient( left, {$rating_color} 50%, #fff 50% ); /* Chrome10-25, Saf5.1-Saf6, iOS -6.1, Android -4.3 */
						background:    -moz-linear-gradient( left, {$rating_color} 50%, #fff 50% ); /* Fx3.6-15 */
						background:      -o-linear-gradient( left, {$rating_color} 50%, #fff 50% ); /* Op11.10-12.02 */
						background:         linear-gradient( left, {$rating_color} 50%, #fff 50% );
				}
				.bgr-half-filled-numbers span{
						color: #fff;
                                                text-shadow: -1px 0 {$rating_color}, 0 1px {$rating_color}, 1px 0 {$rating_color}, 0 -1px {$rating_color};
                                }
				";
        wp_add_inline_style( 'bgr-ratings-css', $custom_css );
}
add_action( 'wp_enqueue_scripts', 'bgr_dynamic_rating_method' );