<?php
/*
* Plugin Name: Star4It
* Description: Star4It Wordpress components.
* Version: 2.1
* Author: Sander Star
* Author URI: https://sanderstar.wordpress.com
*/

// Example 1 : WP Shortcode to display text on page or post.
function wp_first_shortcode(){
	return "Hello, This is your another shortcode!";
}
add_shortcode('first', 'wp_first_shortcode');

/*
  TODO improve
  get_the_excerpt( int|WP_Post $post = null ): string

  set_the_excerpt -> url van het bericht ->  get_permalink($event->ID),

  https://developer.wordpress.org/reference/functions/get_the_excerpt/
*/
// Opgenomen diensten
function wp_recorded_events($atts, $data = null) {
 /*
    Event organiser event list. Alternatief: [eo_events event_category="Kerkdiensten1" event_end_before="today" orderby="eventstart" order="desc"][/eo_events]
 */
 // parameter aantal berichten
 $numberposts = $data;

 $events = eo_get_events(array(
	     'event-category'=>'Kerkdiensten',
         'event_end_before'=>'today',
	     'numberposts'=>$numberposts,
	     'orderby'=>'eventstart',
	     'order'=>'desc',
         'showpastevents'=>true,//Will be deprecated, but set it to true to play it safe.
    ));

	 $content = '<table>';
	 $content = $content.'<thead>';
	 $content = $content.'<tr>';
	 $content = $content.'<th>Datum</th>';
	 $content = $content.'<th>Dominee</th>';
	 $content = $content.'</tr>';
	 $content = $content.'</thead>';
	 $content = $content.'<tbody>';

	 foreach ($events as $event):
		  //Check if all day, set format accordingly
		  $format = ( eo_is_all_day($event->ID) ? get_option('date_format') : get_option('date_format').' '.get_option('time_format') );
		  $content = $content.'<tr>';
		  $content = $content.'<td>';
		  $value = eo_get_the_start($format, $event->ID,null,$event->occurrence_id);
		  $content = $content.$value;
		  $content = $content.'</td>';
		  $content = $content.'<td>';
		  $value = sprintf(
			 '<a href="%s"> %s </a>',
			 get_permalink($event->ID),
			 get_the_title($event->ID)
		  );
		  $content = $content.$value;
		  $content = $content.'</td>';
		  $content = $content.'</tr>';
	 endforeach;

	 $content = $content.'</th>';
	 $content = $content.'</tbody>';
	 $content = $content.'</table>';
	
  return $content;
}

add_shortcode('opgenomen_diensten', 'wp_recorded_events');

// Komende diensten
function wp_coming_events($atts, $data = null) {
    /*
        [eo_events event_category="Kerkdiensten" showpastevents="false"]
    */
 $events = eo_get_events(array(
	     'event-category'=>'Kerkdiensten',
	     'orderby'=>'eventstart',
	     'order'=>'asc',
         'showpastevents'=>false,//Will be deprecated, but set it to true to play it safe.
    ));

     $content = '<table>';
     $content = $content.'<thead>';
     $content = $content.'<tr>';
     $content = $content.'<th>Datum</th>';
     $content = $content.'<th>Dominee</th>';
     $content = $content.'</tr>';
     $content = $content.'</thead>';
     $content = $content.'<tbody>';

     foreach ($events as $event):
          //Check if all day, set format accordingly
          $format = ( eo_is_all_day($event->ID) ? get_option('date_format') : get_option('date_format').' '.get_option('time_format') );
		  $content = $content.'<tr>';
		  $content = $content.'<td>';
		  $value = eo_get_the_start($format, $event->ID,null,$event->occurrence_id);
		  $content = $content.$value;
          $content = $content.'</td>';
		  $content = $content.'<td>';
          $value = sprintf(
             '<a href="%s"> %s </a>',
             get_permalink($event->ID),
             get_the_title($event->ID)
          );
		  $content = $content.$value;
          $content = $content.'</td>';
          $content = $content.'</tr>';
     endforeach;

     $content = $content.'</th>';
     $content = $content.'</tbody>';
     $content = $content.'</table>';
	
  return $content;
}

add_shortcode('komende_diensten', 'wp_coming_events');

?>