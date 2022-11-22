<?php
/*
* Plugin Name: Vers van de dag
* Description: Vers van de dag API.
* Version: 0.6
* Author: Sander Star
* Author URI: https://sanderstar.wordpress.com
*/

// TODO settings page and others can be improved
class VersVanDeDagPage {
	
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
        // This page will be under "Instellingen"
        add_options_page(
            'Vers van de dag instellingen', 
            'Vers van de dag', 
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
            <h1>Vers van de dag instellingen</h1>
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
            'Details', // Title
            array( $this, 'print_section_info' ), // Callback
            'my-setting-admin' // Page
        );  

		// Vers of de dag settings
        add_settings_field(
            'translation', 
            'Vertaling', 
            array( $this, 'translation_callback' ), 
            'my-setting-admin', 
            'setting_section_id'
        );      
        add_settings_field(
            'usr', 
            'Gebruiker', 
            array( $this, 'user_callback' ), 
            'my-setting-admin', 
            'setting_section_id'
        );      

        add_settings_field(
            'pwd', 
            'Wachtwoord', 
            array( $this, 'password_callback' ), 
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

        if( isset( $input['translation'] ) )
            $new_input['translation'] = sanitize_text_field( $input['translation'] );

        if( isset( $input['usr'] ) )
            $new_input['usr'] = sanitize_text_field( $input['usr'] );

        if( isset( $input['pwd'] ) )
            $new_input['pwd'] = sanitize_text_field( $input['pwd'] );
		
        return $new_input;
    }

    /** 
     * Print the Section text
     */
    public function print_section_info() {
        print 'Enter your settings below:';
    }

    public function translation_callback() {
        printf(
            '<input type="text" id="translation" name="my_option_name[translation]" value="%s" />',
            isset( $this->options['translation'] ) ? esc_attr( $this->options['translation']) : ''
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

}

if( is_admin() )
    $my_settings_page = new VersVanDeDagPage();


// Bijbel vers van de dag
// TODO: configuration of url feed 
function wp_vers_van_de_dag() {
	$options = get_option( 'my_option_name' );
	
    $translation = $options["translation"];
	$username = $options["usr"];
	$password = $options["pwd"]; 

	$cachefile=dirname(__FILE__).'/dagelijkswoord-cache.json';
	$cacheseconds=3600; // 1 uur cache

// TODO improve if cache file is leeg, dan werkt het tijdelijk niet.
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

    // titel bijbelvertaling en disclaimer copyright
    $translations = $array['translations'];
    $tekst = $translations[$translation];

    foreach ($array as $key => $jsons) {
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
								array_push($details, $item[$translation]);
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
	$content = $content."<a href=\"" .$details[2]."\" target=\"_blank\" title=\"".$tekst."\" >".$details[1] . "</a>";
	$content = $content."<br/>"."<br/>";
	
	return $content;
}

add_shortcode('vers_van_de_dag', 'wp_vers_van_de_dag');


?>