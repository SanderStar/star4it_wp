<?php
/*
* Plugin Name: Star4It
* Description: Star4It Wordpress components.
* Version: 1.4
* Author: Sander Star
* Author URI: https://sanderstar.wordpress.com
*/

class MySettingsPage {
	
	// TODO definie constant
	const OPTION_NAME = 'my_option_name';
	 
    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;

    /**
     * Start up
     */
    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'page_init' ) );
    }

    /**
     * Add options page
     */
    public function add_plugin_page() {
        // This page will be under "Settings"
        add_options_page(
            'Settings Admin', 
            'My Settings', 
            'manage_options', 
            'my-setting-admin', 
            array( $this, 'create_admin_page' )
        );
    }

    /**
     * Options page callback
     */
    public function create_admin_page() {
        // Set class property
        $this->options = get_option( 'my_option_name' );
        ?>
        <div class="wrap">
            <h1>Star4It settings</h1>
            <form method="post" action="options.php">
            <?php
                // This prints out all hidden setting fields
                settings_fields( 'my_option_group' );
                do_settings_sections( 'my-setting-admin' );
                submit_button();
            ?>
            </form>
        </div>
        <?php
    }

    /**
     * Register and add settings
     */
    public function page_init() {        
        register_setting(
            'my_option_group', // Option group
            'my_option_name', // Option name
            array( $this, 'sanitize' ) // Sanitize
        );

        add_settings_section(
            'setting_section_id', // ID
            'Star4It Custom Settings', // Title
            array( $this, 'print_section_info' ), // Callback
            'my-setting-admin' // Page
        );  

        add_settings_field(
            'id_number', // ID
            'ID Number', // Title 
            array( $this, 'id_number_callback' ), // Callback
            'my-setting-admin', // Page
            'setting_section_id' // Section           
        );      

        add_settings_field(
            'title', 
            'Title', 
            array( $this, 'title_callback' ), 
            'my-setting-admin', 
            'setting_section_id'
        );
		
		// Vers of de dag settings
        add_settings_field(
            'usr', 
            'User', 
            array( $this, 'user_callback' ), 
            'my-setting-admin', 
            'setting_section_id'
        );      

        add_settings_field(
            'pwd', 
            'Password', 
            array( $this, 'password_callback' ), 
            'my-setting-admin', 
            'setting_section_id'
        );
		
		// Diensten media settings
		add_settings_field(
            'churchserviceurl', 
            'Church service URL', 
            array( $this, 'churchservice_callback' ), 
            'my-setting-admin', 
            'setting_section_id'
        );
		
		// Webcam settings
		add_settings_field(
            'webcamserviceurl', 
            'Webcam service URL', 
            array( $this, 'webcamserviceurl_callback' ), 
            'my-setting-admin', 
            'setting_section_id'
        );

		add_settings_field(
            'webcamrefreshrate', 
            'Webcam refresh rate picture (in ms)', 
            array( $this, 'webcamservicerefreshrate_callback' ), 
            'my-setting-admin', 
            'setting_section_id'
        );

    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize( $input ) {
        $new_input = array();
        if( isset( $input['id_number'] ) )
            $new_input['id_number'] = absint( $input['id_number'] );

        if( isset( $input['title'] ) )
            $new_input['title'] = sanitize_text_field( $input['title'] );

        if( isset( $input['usr'] ) )
            $new_input['usr'] = sanitize_text_field( $input['usr'] );

        if( isset( $input['pwd'] ) )
            $new_input['pwd'] = sanitize_text_field( $input['pwd'] );
		
		if( isset( $input['churchserviceurl'] ) )
            $new_input['churchserviceurl'] = sanitize_text_field( $input['churchserviceurl'] );

		if( isset( $input['webcamserviceurl'] ) )
            $new_input['webcamserviceurl'] = sanitize_text_field( $input['webcamserviceurl'] );

		if( isset( $input['webcamservicerefreshrate'] ) )
            $new_input['webcamservicerefreshrate'] = sanitize_text_field( $input['webcamservicerefreshrate'] );

        return $new_input;
    }

    /** 
     * Print the Section text
     */
    public function print_section_info() {
        print 'Enter your settings below:';
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function id_number_callback() {
        printf(
            '<input type="text" id="id_number" name="my_option_name[id_number]" value="%s" />',
            isset( $this->options['id_number'] ) ? esc_attr( $this->options['id_number']) : ''
        );
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function title_callback() {
        printf(
            '<input type="text" id="title" name="my_option_name[title]" value="%s" />',
            isset( $this->options['title'] ) ? esc_attr( $this->options['title']) : ''
        );
    }
	
    public function user_callback() {
        printf(
            '<input type="text" id="usr" name="my_option_name[usr]" value="%s" />',
            isset( $this->options['usr'] ) ? esc_attr( $this->options['usr']) : ''
        );
    }

    public function password_callback() {
        printf(
            '<input type="text" id="pwd" name="my_option_name[pwd]" value="%s" />',
            isset( $this->options['pwd'] ) ? esc_attr( $this->options['pwd']) : ''
        );
    }
	
	public function churchservice_callback() {
        printf(
            '<input type="text" id="churchserviceurl" name="my_option_name[churchserviceurl]" value="%s" />',
            isset( $this->options['churchserviceurl'] ) ? esc_attr( $this->options['churchserviceurl']) : ''
        );
    }

	public function webcamserviceurl_callback() {
        printf(
            '<input type="text" id="webcamserviceurl" name="my_option_name[webcamserviceurl]" value="%s" />',
            isset( $this->options['webcamserviceurl'] ) ? esc_attr( $this->options['webcamserviceurl']) : ''
        );
    }
	
	public function webcamservicerefreshrate_callback() {
        printf(
            '<input type="text" id="webcamservicerefreshrate" name="my_option_name[webcamservicerefreshrate]" value="%s" />',
            isset( $this->options['webcamservicerefreshrate'] ) ? esc_attr( $this->options['webcamservicerefreshrate']) : ''
        );
    }
	

}

if( is_admin() )
    $my_settings_page = new MySettingsPage();


// Example 1 : WP Shortcode to display text on page or post.
function wp_first_shortcode(){
	return "Hello, This is your another shortcode!";
}
add_shortcode('first', 'wp_first_shortcode');


// Bijbel vers van de dag
// TODO: configuration of url feed 
function wp_vers_of_day() {
	$options = get_option( 'my_option_name' );
	
	$username = $options["usr"];
	$password = $options["pwd"]; 

	$cachefile=dirname(__FILE__).'/dagelijkswoord-cache.json';
	$cacheseconds=3600; // 1 uur cache

	if(file_exists($cachefile) && (time()-filemtime($cachefile)< $cacheseconds)){
		  $result=file_get_contents($cachefile);
	}else{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "http://feed.dagelijkswoord.nl/api/json/1.0/");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2); //Maximaal 2 seconden wachten op connectie
		curl_setopt($ch, CURLOPT_TIMEOUT, 10); //Maximaal 10 seconden wachten op ophalen
		curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);  
		$result = curl_exec($ch);
		file_put_contents($cachefile,$result);
		curl_close($ch);
	}

	$tekst = array();
	$details = array();
	$array = json_decode($result, true);
	foreach ($array as $key => $jsons) {
		// titel bijbelvertaling en disclaimer copyright
		array_push($tekst, $jsons["nbv"]);
		$counterElement = 1;
		foreach ($jsons as $key => $value) {
			if (is_array($value)) {
				$currentDate = false;
				foreach ($value as $item) {
					if (is_array($item)) {
						$counterText = 1;
						foreach ($item as $text) {
							// alleen specfieke bijbelvertaling tonen
							if ($counterText == 1 and $currentDate == true) {
								array_push($details, $item["nbv"]);
							}
							$counterText++;
						}
					} else {
						if ($counterElement <= 3) {
							if ((string) date("Y-m-d") == $item) {
								$currentDate = true;
							}
							array_push($details, $item);
						}
						$counterElement++;
					}
					
				}
			}
		}
	}
	
	$content = $details[3];
	$content = $content."<br/>"."<br/>";
	$content = $content."<a href=\"" .$details[2]."\" target=\"_blank\" title=\"".$tekst[1]."\" >".$details[1] . "</a>";
	$content = $content."<br/>"."<br/>";
	
	return $content;
}

add_shortcode('vers_of_day', 'wp_vers_of_day');

// Webcam foto
function wp_webcam() {
	
	$options = get_option( 'my_option_name' );
	
	$url = $options["webcamserviceurl"];
	$refresh_rate = $options["webcamservicerefreshrate"];
	
	$newline = "\r\n";

	$content .=	"<script type=\"text/javascript\">// <![CDATA[";
	$content .= $newline;
	$content .= "// Set the BaseURL to the URL of your camera";
	$content .= $newline;
	$content .= "\r\n";
	$content .= $newline;
	$content .= "var BaseURL = '".$url."'";
	$content .= $newline;
	$content .= "// Force an immediate image load";
	$content .= $newline;
	$content .= "var theTimer = setTimeout('reloadImage()', 1);";
	$content .= $newline;
	$content .= "function reloadImage() {";
	$content .= $newline;
	$content .= "	theDate = new Date();";
	$content .= $newline;
	$content .= "	var url = BaseURL;";
	$content .= $newline;
	$content .= "	url += '?dummy=' + theDate.getTime().toString(10);";
	$content .= $newline;
	$content .= "	// The dummy above enforces a bypass of the browser image cache";
	$content .= $newline;
	$content .= "	// Here we load the image";
	$content .= $newline;
	$content .= "	document.theImage.src = url;";
	$content .= $newline;
	$content .= "	// Reload the image every x seconds (x000 ms)";
	$content .= $newline;
	$content .= "	theTimer = setTimeout('reloadImage()',".$refresh_rate.");";
	$content .= $newline;
	$content .= "}";
	$content .= $newline;
	$content .= "document.write('<img name=\"theImage\" src=\"\"');";
	$content .= $newline;
	$content .= "document.write('alt=\"Live image\">');";
	$content .= $newline;
	$content .= "// ]]></script>";
	$content .= $newline;
	return $content;
}

add_shortcode('webcam', 'wp_webcam');


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

// Dienst media
// TODO; maybe change to rsnippet for backwards compability
function wp_dienst_media($atts, $content = null) {
	
   $options = get_option( 'my_option_name' );
   
   $url = $options["churchserviceurl"];
  
   $media = $content;

   // Downloaden uitgezet vanwege licentie kosten (zie emails)
   // Via proxy geen mogelijkheid tot doorspoelen, terugspelen of totale tijdsduur zien
   $content = '<audio controls="true" controlsList="nodownload">';
   $content = $content.'<source src="'.$url.'?media='.$media.'" type="audio/mpeg">';
   $content = $content.'</audio>';

   $content = $content.'<p/>';
   $content = $content.'<p/>';
   
   // TODO eens uitzetten
   $server = "86.87.234.78";
   $port = "80";
   $path = "diensten";
   $url = "http://".$server.":".$port."/".$path."/".$media;

   // Vergeet eerdere vulling van $content (proxy werkt niet goed genoeg)
   $content = '<a href="'.$url.'" target="_blank">Direct luisteren opgenomen dienst</a>';

   return $content;
}

add_shortcode('dienst_media', 'wp_dienst_media');

?>