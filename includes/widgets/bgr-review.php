<?php 
class bgr_review_widget extends WP_Widget {
           
        /** Constructor
         *  @since   1.0.0
         *  @access  public
         *  @author  Wbcom Designs
         */
	function __construct() {
		$widget_ops = array('classname' => 'widget_bp_group_review_tab', 'description' => __('Display Group Reviews.', BGR_DOMAIN));
		$control_ops = array('width' => 200, 'height' => 350);
		parent::__construct('bgr_group_review_widget', __('BP Group Review Widget', BGR_DOMAIN), $widget_ops, $control_ops);
        }	
    
        /** Form display for widget in admin panel
        *  @since   1.0.0
        *  @access  public
        *  @author  Wbcom Designs
        */	
	function form( $instance ) {        
                if(empty($instance)){
                    $instance = wp_parse_args( (array) $instance, array( 
                    'widget_title' => '',
                    'listing_cat' => 'topreviewed',  
                    'post_num' => '5', 
                    'thumbnail' => 'show' 
                    ));      
                }
                extract($instance);              
        ?>
            <div class="bgr_review_listing_form">
                <p>
                    <label for="<?php _e($this->get_field_id( 'widget_title' ),BGR_DOMAIN); ?>"><?php _e( 'Title:',BGR_DOMAIN); ?></label> 
                    <input class="widefat" id="<?php _e($this->get_field_id( 'widget_title' ),BGR_DOMAIN); ?>" name="<?php _e($this->get_field_name( 'widget_title' ),BGR_DOMAIN); ?>" type="text" value="<?php _e(esc_attr( $widget_title ),BGR_DOMAIN); ?>" />
                </p>
                <h4><?php _e('Select Listing Type', BGR_DOMAIN); ?></h4>
                <div class="bgr_review_select_listing_type">
                    <label class="alignleft" style="display: block; width: 50%; margin-bottom: 7px;" for="listing_toprated">
                            <input type="radio" class="" id="<?php _e($this->get_field_id("listing_cat"),BGR_DOMAIN); ?>" name="<?php _e($this->get_field_name("listing_cat"),BGR_DOMAIN); ?>" value="<?php _e('toprated',BGR_DOMAIN); ?>" <?php if ($listing_cat=='toprated') { _e('checked',BGR_DOMAIN);} ?> />
                            <?php _e( 'Top Rated', BGR_DOMAIN); ?>
                    </label>
                    <label class="alignleft" style="display: block; width: 50%; margin-bottom: 7px;" for="listing_topreviewed">
                            <input type="radio" class="" id="<?php _e($this->get_field_id("listing_cat"),BGR_DOMAIN); ?>" name="<?php _e($this->get_field_name("listing_cat"),BGR_DOMAIN); ?>" value="<?php _e('topreviewed',BGR_DOMAIN); ?>" <?php if ($listing_cat=='topreviewed') { _e('checked',BGR_DOMAIN); } ?> />		
                            <?php _e( 'Most Reviewed', BGR_DOMAIN); ?>
                    </label>			
                </div>
                <div class="clear"></div>        
                <div class="bgr_review_options">        
                    <p>
                        <label for="<?php _e($this->get_field_id('post_num'), BGR_DOMAIN); ?>"><?php _e('Number of reviews to show:', BGR_DOMAIN); ?>                            
                            <input id="<?php _e($this->get_field_id('post_num'), BGR_DOMAIN); ?>" name="<?php _e($this->get_field_name('post_num'),BGR_DOMAIN); ?>" type="number" min="1" step="1" value="<?php _e($post_num,BGR_DOMAIN); ?>" />
                        </label>
                    </p>

                    <p class="bgr_review_thumbnail">
                        <label for="<?php _e($this->get_field_id('thumbnail'),BGR_DOMAIN); ?>"><?php _e('Thumbnail:', BGR_DOMAIN); ?></label> 
                        <select id="<?php _e($this->get_field_id('thumbnail'),BGR_DOMAIN); ?>" name="<?php _e($this->get_field_name('thumbnail'),BGR_DOMAIN); ?>" style="margin-left: 12px;">
                            <option value="<?php _e('hide',BGR_DOMAIN); ?>" <?php selected($thumbnail, 'hide', true); ?>><?php _e('Hide', BGR_DOMAIN); ?></option>
                            <option value="<?php _e('show',BGR_DOMAIN); ?>" <?php selected($thumbnail, 'show', true); ?>><?php _e('Show', BGR_DOMAIN); ?></option>				
                        </select>       
                    </p>     

                    <div class="clear"></div>       
                    <div class="clear" style="margin-bottom: 15px;"></div>        
                </div>
            </div><!-- .bgr_review_listing_form -->
	<?php }
        
        /* Update Widget data
        *  @since   1.0.0
        *  @access  public
        *  @author  Wbcom Designs
        */
	function update( $new_instance, $old_instance ) {	
		$instance = $old_instance;
		$instance['widget_title'] = strip_tags( $new_instance['widget_title'] );
		$instance['listing_cat']  = $new_instance['listing_cat'];
		$instance['post_num']     = $new_instance['post_num'];
		$instance['thumbnail']    = $new_instance['thumbnail'];
		return $instance;	
	}
        
        /* Display Widget content
        *  @since   1.0.0
        *  @access  public
        *  @author  Wbcom Designs
        */        
	function widget( $args, $instance ) {	
		global $bp; $post;
		extract($instance, EXTR_SKIP);
		$widget_title 		= apply_filters( 'widget_title', $widget_title );
		$widget_listing_cat     = apply_filters( 'listing_cat', $listing_cat );
		$widget_post_num	= apply_filters( 'post_num', $post_num );
		$widget_thumbnail	= apply_filters( 'thumbnail', $thumbnail );
                $custom_args            = array(
                                                'post_type' => 'review',
                                                'posts_per_page' => -1,
                                                'post_status' => 'publish',
                                                'category' => 'group',	
                                                );
                $reviews = get_posts( $custom_args );
                $bgr_admin_settings = get_option( 'bgr_admin_settings' );
                if(!empty($bgr_admin_settings)){
                    $review_rating_fields = $bgr_admin_settings['add_review_rating_fields'];
                }

                $review_groups       = array();	
                $review_groups_count = array();
                if( !empty( $reviews ) ){ 
                        $ttl_rating     = 0;	
                        foreach( $reviews as $review ){                                       
                                $rate = 0;
                                $linked_group        = get_post_meta( $review->ID, 'linked_group', false ); 
                                $review_ratings      = get_post_meta( $review->ID, 'review_star_rating', false );                                               

                                $reviews_field_count = 0;
                                if(!empty($review_rating_fields) && !empty($review_ratings[0])):                                                  
                                        foreach($review_ratings[0] as $field => $value){
                                                if(in_array($field,$review_rating_fields)){
                                                        $rate += $value;
                                                        $reviews_field_count++;
                                                        }
                                                }
                                    $reviews_field_count == 0 ?  ($reviews_field_count = 1): $reviews_field_count; 
                                    $ttl_rating = (int)$rate/$reviews_field_count;

                                    //Rating Count    
                                    if(array_key_exists($linked_group[0],$review_groups)){							
                                        $value = $review_groups[$linked_group[0]];
                                        array_push($value,$ttl_rating);
                                        $review_groups[$linked_group[0]] = $value;

                                    }else{                                                   
                                        $review_groups[$linked_group[0]] = array($ttl_rating)  ;                                                     
                                    }						
                                endif;      

                                //Review count
                                if(!empty($linked_group[0])){
                                    if(array_key_exists($linked_group[0],$review_groups_count)){							
                                         $value = $review_groups_count[$linked_group[0]]; 
                                         $review_groups_count[$linked_group[0]] = (int)$value + 1;
                                    }else{                                               
                                         $review_groups_count[$linked_group[0]] = 1; 
                                    } 
                                }                                 

                        }

                        /*** Sort Group ids according reviews & ratings ****/
                        arsort($review_groups_count);

                        if(!empty($review_groups)){
                            foreach($review_groups as $key=>$val){					
                                $count = count($val);
                                if($count>1){                                         
                                   $sum = array_sum(array_values($val));
                                   $review_groups[$key] = $sum/$count;
                                }else{
                                   $review_groups[$key] = $val[0];
                                }
                             }
                            arsort($review_groups);
                        }                            
                }
                _e($args['before_widget'],BGR_DOMAIN);                       
                if ( ! empty( $widget_title ) ) { 
                    _e($args['before_title'].$widget_title.$args['after_title'],BGR_DOMAIN); 
                }
                 
                /*** Select Listing Type ***/
                if($widget_listing_cat=='toprated'){
                    $group_args = $review_groups;
                }else if($widget_listing_cat=='topreviewed'){
                    $group_args = $review_groups_count;
                }else{
                    $group_args= '';
                }
                
                $post_num_counter = 0;
                if(!empty($group_args)):   
                    $gids = array_keys($group_args);

                    foreach($gids as $gid) {  
                        if( $post_num_counter < $widget_post_num ):
                        $group = groups_get_group( array( 'group_id' => $gid) );                                
                        $avatar_options = array ( 'item_id' => $gid, 'object' => 'group', 'type' => 'full', 'avatar_dir' => 'group-avatars', 'alt' => 'Group avatar', 'class' => 'sidebar_review_avatar' );

                        ?>
                        <div class="bgr-row">
                            <?php if($widget_thumbnail=='show'){ ?>
                                <div class="bgr-col-3">
                                    <a href="<?php _e(site_url().'/groups/'.$group->slug,BGR_DOMAIN); ?>"><?php echo bp_core_fetch_avatar($avatar_options); ?></a>
                                </div>
                                <div class="bgr-col-7">
                            <?php } else { ?> 
                                <div class="bgr-col-12">
                            <?php } ?>        
                                    <div class="item-title"><a href="<?php _e(site_url().'/groups/'.$group->slug,BGR_DOMAIN); ?>"><?php echo $group->name; ?></a></div>
                                    <div class="group-review">                                            
                                    <?php                                             

                                        $group_avg_rating = $review_groups[$gid];
                                        $type = gettype( $group_avg_rating );
                                        if( $type == 'integer' ){
                                                $stars_on = $group_avg_rating;
                                                $stars_off = 5 - $group_avg_rating;
                                                $stars_half = 0;
                                        }

                                        if( $type == 'double' ){
                                                $stars_on = intval( $group_avg_rating );
                                                $stars_half = 1;
                                                $stars_off = 5 - ( $stars_on + $stars_half );
                                        }

                                        for( $i = 1; $i <= $stars_on; $i++ ){
                                                ?><img class="stars" src="<?php _e(BGR_PLUGIN_URL."assets/images/star.png",BGR_DOMAIN);?>" alt="<?php _e('star',BGR_DOMAIN);?>"><?php
                                        }

                                        for( $i = 1; $i <= $stars_half; $i++ ){
                                                ?><img class="stars" src="<?php _e(BGR_PLUGIN_URL."assets/images/star_half.png",BGR_DOMAIN);?>" alt="<?php _e('star',BGR_DOMAIN);?>"><?php
                                        }

                                        for( $i = 1; $i <= $stars_off; $i++ ){
                                                ?><img class="stars" src="<?php _e(BGR_PLUGIN_URL."assets/images/star_off.png",BGR_DOMAIN);?>" alt="<?php _e('star',BGR_DOMAIN);?>"><?php
                                        }

                                        $group_avg_rating = round( $group_avg_rating, 2 );
                                        _e(" (".$group_avg_rating.") stars",BGR_DOMAIN);  
                                        ?>
                                    </div>
                                    <div><?php     
                                        _e("Total Reviews: ".$review_groups_count[$gid],BGR_DOMAIN);                                              
                                        ?>
                                    </div>
                                </div>                                                
                        </div>            
                <?php  endif; $post_num_counter++;  }   
                else: ?>
                    <div id="message" class="info"><?php _e('No group has been reviewed yet!',BGR_DOMAIN); ?></div>
                <?php endif; 
                    _e($args['after_widget'],BGR_DOMAIN);
	}            

}

function bgr_review_register_widget() {
	register_widget( "bgr_review_widget" );
}
add_action( 'widgets_init', 'bgr_review_register_widget' );

?>