<?php
// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Class to serve Rating Display
 */

if( !class_exists( 'BGR_Rating_Display' ) ) {
	class BGR_Rating_Display{

		/**
		* Constructor for Group Rating Display
		*  @since   1.0.0
		*  @author  Wbcom Designs
		*/
            
		function __construct() {			
			add_action( 'bgr_display_ratings', array( $this, 'bgr_select_rating_type' ) );
			add_action( 'bgr_display_widget_average_ratings', array( $this, 'bgr_widget_average_ratings' ) );
			add_action( 'bgr_display_group_average_ratings', array( $this, 'bgr_group_average_ratings' ) );					
		}

		/**
		*  Actions performed for rating display in Review , Manage Review & Single Review Page
		*  @since   1.0.0
		*  @author  Wbcom Designs
		*/
                
		function bgr_select_rating_type( $post_id ) {
			global $bgr;
			$rating_type 			    = $bgr['rating_type'];
			$review_rating_fields  		= $bgr['review_rating_fields'];
			$review_ratings    	   		= get_post_meta( $post_id, 'review_star_rating',false);
			switch( $rating_type ) {
				
				case 'star'			: 	$this->bgr_display_star_rating( $review_rating_fields, $review_ratings );
											break;
				case 'bar'			: 	$this->bgr_display_bar_rating( $review_rating_fields, $review_ratings );
											break;
				case 'numbers'		: 	$this->bgr_display_number_rating( $review_rating_fields, $review_ratings );
											break;
			}
		}
                
		/**
		*  Actions performed for rating display type : Star
		*  @since   1.0.0
		*  @author  Wbcom Designs
		*/ 
                
		function bgr_display_star_rating( $review_rating_fields, $review_ratings ) {
				if( !empty( $review_rating_fields ) && !empty( $review_ratings[0] ) ) :                                                  
					foreach( $review_rating_fields as $review_field ) {
						if( array_key_exists( $review_field, $review_ratings[0] ) ) {
							?>
							<div class="bgr-col-6"> 
								<?php _e( $review_field.' : ', BGR_DOMAIN );
								/*** Ratings *****/
								$stars_on  = $review_ratings[0][ $review_field ];
								$stars_off = 5 - $stars_on; 
								for( $i = 1; $i <= $stars_on; $i++ ){
									?><img class="stars" src="<?php _e( BGR_PLUGIN_URL."assets/images/star.png", BGR_DOMAIN );?>" alt="<?php _e('star', BGR_DOMAIN ); ?>"><?php
								}
								for( $i = 1; $i <= $stars_off; $i++ ){
									?><img class="stars" src="<?php _e( BGR_PLUGIN_URL."assets/images/star_off.png", BGR_DOMAIN );?>" alt="<?php _e('star', BGR_DOMAIN ); ?>"><?php
								}
								?>
							</div>
							<?php                                                                    
						}
					}
				endif;  
		}
                
		/**
		*  Actions performed for rating display type : Bar
		*  @since   1.0.0
		*  @author  Wbcom Designs
		*/ 
                
		function bgr_display_bar_rating( $review_rating_fields, $review_ratings ) {
			if( !empty( $review_rating_fields ) && !empty( $review_ratings[0] ) ) :                                                  
				foreach( $review_rating_fields as $review_field ) {
					if( array_key_exists( $review_field, $review_ratings[0] ) ) {
						?>
						<div class="bgr-col-12">
							<div class="bgr-col-6">							
								<?php _e( $review_field.' : ', BGR_DOMAIN ); ?>
							</div>
							<div class="bgr-col-6 bgr-bar-div">							
								<?php
									/*** Ratings *****/
									$stars_on  = $review_ratings[0][ $review_field ];
									$stars_off = 5 - $stars_on; 
									for( $i = 1; $i <= $stars_on; $i++ ) {
										?> <a class="bgr-filled-bars" alt="<?php _e( 'bar', BGR_DOMAIN ); ?>"></a><?php
									}
									for( $i = 1; $i <= $stars_off; $i++ ) {
										?> <a class="bgr-bars <?php echo $i;?>" alt="<?php _e( 'bar', BGR_DOMAIN ); ?>" ></a><?php
									}
								?>
							</div>
						</div>
						<?php                                                                    
					}
				}
			endif;
		}
                
		/**
		*  Actions performed for rating type : Numbers
		*  @since   1.0.0
		*  @author  Wbcom Designs
		*/ 
                
		function bgr_display_number_rating( $review_rating_fields, $review_ratings ) {
			if( !empty( $review_rating_fields ) && !empty( $review_ratings[0] ) ) :                                                  
				foreach( $review_rating_fields as $review_field ) {
					if( array_key_exists( $review_field, $review_ratings[0] ) ) {
						?>
						<div class="bgr-col-12">
							<div class="bgr-col-6">							
								<?php _e( $review_field.' : ', BGR_DOMAIN ); ?>
							</div>
							<div class="bgr-col-6 bgr-num-div">							
								<?php
									/*** Ratings *****/
									$stars_on  = $review_ratings[0][ $review_field ];
									$stars_off = 5 - $stars_on; 									
									$counter   = 1;
									for( $i = 1; $i <= $stars_on; $i++ ) {
											?><a class="bgr-filled-numbers" alt="<?php _e( 'bar', BGR_DOMAIN ); ?>"><?php _e( $counter, BGR_DOMAIN ); ?></a><?php
										$counter ++; 
									}
									for( $i = 1; $i <= $stars_off; $i++ ) {
											?><a class="bgr-numbers" alt="<?php _e( 'bar', BGR_DOMAIN ); ?>"><?php _e( $counter, BGR_DOMAIN ); ?></a><?php
											$counter ++; 
									} 
								?>
							</div>
						</div>
						<?php                                                                    
					}
				}
			endif;
		}
                    
		
		/**
		*  Actions performed for rating display in  Widgets
		*  @since   1.0.0
		*  @author  Wbcom Designs
		*/
                
		function bgr_widget_average_ratings( $review_groups ) {
			global $bgr;
			$rating_type 			= $bgr['rating_type'];
			$group_avg_rating = $review_groups;
			$remaining = $group_avg_rating - (int) $group_avg_rating;

			if( $remaining > 0 ) {
					$stars_on = intval( $group_avg_rating );					
					$stars_half = 1;						
					$stars_off = 5 - ( $stars_on + $stars_half );
			} else {
					$stars_on = $group_avg_rating;
					$stars_off = 5 - $group_avg_rating;
					$stars_half = 0;
			}
			
			switch( $rating_type ) {
				
				case 'star'			: 	$this->bgr_average_star_rating( $stars_on, $stars_half, $stars_off );
											break;
				case 'bar'			: 	$this->bgr_average_bar_rating( $stars_on, $stars_half, $stars_off );
											break;
				case 'numbers'		: 	$this->bgr_average_number_rating( $stars_on, $stars_half, $stars_off );
											break;	
			}
			
			$group_avg_rating = round( $group_avg_rating, 2 );
			_e("</div><div>Rating :  (".$group_avg_rating.") ", BGR_DOMAIN ); 
		}
		
		/**
		*  Actions performed for rating display in  Group Header
		*  @since   1.0.0
		*  @author  Wbcom Designs
		*/
                
		function bgr_group_average_ratings( $avg_rating ) {
			global $bgr;
			$rating_type 			= $bgr['rating_type'];
			$type = gettype($avg_rating);
			$var = (int)$avg_rating - $avg_rating; 
			if( $var == 0 ) {
					$stars_on = $avg_rating;
					$stars_off = 5 - $avg_rating;
					$stars_half = 0;
			}
			else {	
					$stars_on = intval( $avg_rating );
					$stars_half = 1;
					$stars_off = 5 - ( $stars_on + $stars_half );
			}
			
			switch( $rating_type ) {
				
				case 'star'			: 	$this->bgr_average_star_rating( $stars_on, $stars_half, $stars_off );
											break;
				case 'bar'			: 	$this->bgr_average_bar_rating( $stars_on, $stars_half, $stars_off );
											break;
				case 'numbers'		: 	$this->bgr_average_number_rating( $stars_on, $stars_half, $stars_off );
											break;
			}
			
		}
		
		/**
		*  Actions performed for Widget rating display type : Star
		*  @since   1.0.0
		*  @author  Wbcom Designs
		*/ 
                
		function bgr_average_star_rating( $stars_on, $stars_half, $stars_off ) {
				for( $i = 1; $i <= $stars_on; $i++ ) {
						?><img class="stars" src="<?php _e( BGR_PLUGIN_URL."assets/images/star.png", BGR_DOMAIN );?>" alt="<?php _e('star', BGR_DOMAIN );?>"><?php
				}

				for( $i = 1; $i <= $stars_half; $i++ ) {
						?><img class="stars" src="<?php _e( BGR_PLUGIN_URL."assets/images/star_half.png", BGR_DOMAIN );?>" alt="<?php _e('star', BGR_DOMAIN );?>"><?php
				}

				for( $i = 1; $i <= $stars_off; $i++ ) {
						?><img class="stars" src="<?php _e( BGR_PLUGIN_URL."assets/images/star_off.png", BGR_DOMAIN );?>" alt="<?php _e('star', BGR_DOMAIN );?>"><?php
				} 
		}
                
		/**
		*  Actions performed for Widget rating display type : Bar
		*  @since   1.0.0
		*  @author  Wbcom Designs
		*/ 
                
		function bgr_average_bar_rating( $stars_on, $stars_half, $stars_off ) {
				for( $i = 1; $i <= $stars_on; $i++ ) {
						?><a class="bgr-filled-bars" alt="<?php _e( 'bar', BGR_DOMAIN ); ?>"></a><?php
				}

				for( $i = 1; $i <= $stars_half; $i++ ) {
						?><a class="bgr-half-filled-bars" alt="<?php _e( 'bar', BGR_DOMAIN ); ?>"></a><?php
				}

				for( $i = 1; $i <= $stars_off; $i++ ) {
						?><a class="bgr-bars" alt="<?php _e( 'bar', BGR_DOMAIN ); ?>"></a><?php
				} 
		}
                
		/**
		*  Actions performed for Widget rating type : Numbers
		*  @since   1.0.0
		*  @author  Wbcom Designs
		*/ 
                
		function bgr_average_number_rating( $stars_on, $stars_half, $stars_off ) {
				$counter = 1;
				for( $i = 1; $i <= $stars_on; $i++ ) {
						?><a class="bgr-filled-numbers" alt="<?php _e( 'bar', BGR_DOMAIN ); ?>"><span><?php _e( $counter, BGR_DOMAIN ); ?></span></a><?php
				    $counter ++; 
				}

				for( $i = 1; $i <= $stars_half; $i++ ) {
						?><a class="bgr-half-filled-numbers" alt="<?php _e( 'bar', BGR_DOMAIN ); ?>"><span><?php _e( $counter, BGR_DOMAIN ); ?></span></a><?php
					$counter ++; 
				}

				for( $i = 1; $i <= $stars_off; $i++ ) {
						?><a class="bgr-numbers" alt="<?php _e( 'bar', BGR_DOMAIN ); ?>"><span><?php _e( $counter, BGR_DOMAIN ); ?></span></a><?php
						$counter ++; 
				} 
		}
         
	}
	new BGR_Rating_Display();
}