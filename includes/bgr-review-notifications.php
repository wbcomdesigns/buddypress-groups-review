<?php
global $bp;
/**function custom_filter_notifications_get_registered_components( $component_names = array() ) {
    if ( ! is_array( $component_names ) ) {
        $component_names = array();
    }
    array_push( $component_names, 'groups' );
    return $component_names;
}
add_filter( 'bp_notifications_get_registered_components', 'custom_filter_notifications_get_registered_components' );
**/

function BGR_Review_notifications( $action, $item_id, $secondary_item_id, $total_items, $format = 'string' ) {
    $group     = groups_get_group( array( 'group_id' => $item_id) );     
    
    switch($action){
        case 'review_request_accepted' :      
                    _e("Your review for  ".$group->name ." accepted by admin.", BGR_DOMAIN );   
                    break;    
        case 'review_request_denied':  
                    _e("Your review for ".$group->name ." denied by admin.", BGR_DOMAIN );  
                    break;
    }


}
add_filter( 'bp_notifications_get_notifications_for_user', 'BGR_Review_notifications', 10, 5 );

function BGR_Review_accept_notification($group_id,$reviewer_id ) { 
    bp_notifications_add_notification( array(
        'user_id'           => $reviewer_id,
        'item_id'           => $group_id,     
        'component_name'    => 'groups',
        'component_action'  => 'review_request_accepted',
        'date_notified'     => bp_core_current_time(),
        'is_new'            => 1,
    ) );

}
add_action( 'bgr_accept_user_review', 'BGR_Review_accept_notification', 99, 2 );

function BGR_Review_denied_notification($group_id,$reviewer_id ) { 
    bp_notifications_add_notification( array(
        'user_id'           => $reviewer_id,
        'item_id'           => $group_id,    
        'component_name'    => 'groups',
        'component_action'  => 'review_request_denied',
        'date_notified'     => bp_core_current_time(),
        'is_new'            => 1,
    ) );

}
add_action( 'bgr_deny_user_review', 'BGR_Review_denied_notification', 99, 2 );
