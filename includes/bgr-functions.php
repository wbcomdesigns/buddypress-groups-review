<?php

/**
 * Update the activity action with youzify.
 *
 * @param  mixed $action
 * @param  object $activity
 * @return mixed $action
 */
function bgr_youzify_activity_action_wall_posts( $action, $activity ){	
	$action = $activity->action;
	return $action;
}
add_filter( 'youzify_activity_new_post_action', 'bgr_youzify_activity_action_wall_posts',10, 2 );