<?php

/*
* 	Google Fonts Class for Theme Trust Google Font Picker
*   =====================================================
*	Contents:
*	1. Declare Class Variables
*	2. Construct Class, Decode JSON and Set Font Family and Weight Vairables; Add Actions
*	3. Theme Customizer Head CSS
*	4. Register Customizer Controls
*	5. Generate Styles and Link Google Fonts Sheets
*
*/

if (!class_exists('ad_gfonts')) {

    class ad_gfonts {

		// Class Variables
		/* @var $localizationDomain string for translation domain */
        private $localizationDomain 	= 'anarieldesign';
        /* @var $gfonts_file string for the fonts JSON file name */
		private $gfonts_file 			= 'fonts.json';
		/* @var $thispluginurl string for the URL of the plugin (set in constructor) */
        private $thispluginurl 			= '';
        /* @var $thispluginpath string for the path of the plugin (set in constructor) */
        private $thispluginpath 		= '';
        /* @var $tags array that lists all the tag elements as keys and the descriptions as values */
		private $tags 					= array();
		/* @var $all_font_weights array that lists all the possible font weights as keys with the descriptions as values */
		private $all_font_weights 		= array();
		/* @var $list_fonts array for listing all GFont families pulled from the JSON, set in ad_gfp_get_fonts() */
		private $list_fonts        		= array();
		/* @var $font_weights array for listing all GFonts weights pulled from the JSON, set in ad_gfp_get_fonts() */
		private $font_weights 			= array();
		/* @var $fonts_decode string used to temporarily store decoded fonts.json data as string */
		private $fonts_decode			= array();
		/* @var $fonts_decode string used to temporarily store decoded fonts.json data as string */
		private $css_selectors_string 	= '';

		// Class Functions

        // function ad_gfonts(){$this->__construct();} // PHP 4

        function __construct(){
            $this->thispluginurl = WP_PLUGIN_URL . '/' . dirname( plugin_basename(__FILE__) ) . '/';
            $this->thispluginpath = WP_PLUGIN_DIR . '/' . dirname( plugin_basename(__FILE__) ) . '/';

			// Array Values
			$this->tags = array(
					'body'				=> __( 'All Text', $this->localizationDomain),
					'p'					=> __( 'Paragraph Text <p>', $this->localizationDomain),
					'a' 				=> __( 'Links <a>', $this->localizationDomain),
					'h1'				=> __( 'Header 1 <h1>', $this->localizationDomain),
					'h2'				=> __( 'Header 2 <h2>', $this->localizationDomain),
					'h3'				=> __( 'Header 3 <h3>', $this->localizationDomain),
					'h4'				=> __( 'Header 4 <h4>', $this->localizationDomain),
					'h5'				=> __( 'Header 5 <h5>', $this->localizationDomain),
					'h6'				=> __( 'Header 6 <h6>', $this->localizationDomain),
					'blockquote'		=> __( 'Blockquote <blockquote>', $this->localizationDomain),
					'li'				=> __( 'List Item <li>', $this->localizationDomain),
				);
			$this->all_font_weights = array(
					'100'       => __( 'Ultra Light', $this->localizationDomain ),
					'100italic' => __( 'Ultra Light Italic', $this->localizationDomain ),
					'200'       => __( 'Light', $this->localizationDomain ),
					'200italic' => __( 'Light Italic', $this->localizationDomain ),
					'300'       => __( 'Book', $this->localizationDomain ),
					'300italic' => __( 'Book Italic', $this->localizationDomain ),
					'regular'   => __( 'Regular', $this->localizationDomain ),
					'italic' 	=> __( 'Regular Italic', $this->localizationDomain ),
					'500'       => __( 'Medium', $this->localizationDomain ),
					'500italic' => __( 'Medium Italic', $this->localizationDomain ),
					'600'       => __( 'Semi-Bold', $this->localizationDomain ),
					'600italic' => __( 'Semi-Bold Italic', $this->localizationDomain ),
					'700'       => __( 'Bold', $this->localizationDomain ),
					'700italic' => __( 'Bold Italic', $this->localizationDomain ),
					'800'       => __( 'Extra Bold', $this->localizationDomain ),
					'800italic' => __( 'Extra Bold Italic', $this->localizationDomain ),
					'900'       => __( 'Ultra Bold', $this->localizationDomain ),
					'900italic' => __( 'Ultra Bold Italic', $this->localizationDomain )
				);

            // Actions
			add_action( 'customize_controls_print_scripts', array($this, 'ad_gfp_customizer_head') );
			add_action( 'customize_register', array($this, 'ad_gfp_register_customizer_options') ); // Customizer API register
			add_action( 'wp_head', array($this, 'ad_gfp_generate_styles') ); // Add the GFonts Picker Styles to the Head

        }

		/* TODO: Add the jQuery UI and JS to control the auto-complete -- Menus appear, but something about the position: fixed of the customizer controls prevents it from being displayed. */
		function ad_gfp_customizer_head() {
			?>
			<link rel="stylesheet" type="text/css" href="<?php echo plugins_url( 'css/ad_gfp.css' , __FILE__ ); ?>">
			<?php /*<link rel="stylesheet" href="//code.jquery.com/ui/1.10.4/themes/smoothness/jquery-ui.css">
			<script src="//code.jquery.com/jquery-1.10.2.js"></script>
			<script src="//code.jquery.com/ui/1.10.4/jquery-ui.js"></script>
			<script>
			  $(function() {
				    $( ".font-family" ).autocomplete({
				      source: [<?php

						$gfonts_local = $this->list_fonts;

						foreach( $gfonts_local as $gfont ) {
							echo "\"$gfont\",";
						}?>]
				    });
				  });
			</script> */ ?>
		<?php
		}

		// Add the Customizer API settings and controllers
		function ad_gfp_register_customizer_options( $wp_customize ) {

            $json = file_get_contents( plugins_url( 'fonts.json' , __FILE__ ) );
            $fonts_decode = json_decode( $json, TRUE );
            $this->list_fonts['default'] = 'Default';

            foreach ( $fonts_decode['items'] as $key => $value ) {
                $item_family 							= $value['family'];
                $this->list_fonts[$item_family]        	= $item_family;
                $this->list_font_weights[$item_family] 	= $value['variants'];
            }

			$wp_customize->add_section( 'ad_gfp_customizer_section_fonts', array(
				'title'    => __( 'Google Fonts for AnarielDesign', 'anarieldesign' ),
				'description'    => __( 'From the drop-down menus you can select font family and font weight per tag. To speed up the search open the drop-down and type in the first few letters of the font you are looking for.', 'anarieldesign' ),
				'priority' => 1
			));

			$i = 1;

			$tags_local = $this->tags;

			foreach ($tags_local as $key => $value) {

				// Tag

				$wp_customize->add_setting( 'ad_gfp_' . $key . '_tag');
				$wp_customize->add_control( new AD_GFP_Tag( $wp_customize, 'ad_gfp_' . $key . '_tag', array(
					'label'		=> __( $value, 'anarieldesign' ),
					'section'   => 'ad_gfp_customizer_section_fonts',
					'settings'  => 'ad_gfp_' . $key . '_tag',
					'priority' => "1".$i++,
				) ) );

				// Font Family

				$wp_customize->add_setting( 'ad_gfp_' . $key . '_font_family', array(
					'default' => 'default',
					'transport' => 'refresh'
				));

				$wp_customize->add_control( 'ad_gfp_' . $key . '_font_family',
				array(
					'type'	   => 'select',
					'label'    => __( 'Font Family', 'anarieldesign' ),
					'section'  => 'ad_gfp_customizer_section_fonts',
					'settings' => 'ad_gfp_' . $key . '_font_family',
					'priority' => "1".$i++,
					'choices'  => $this->list_fonts
				) );

				// Weight

				$wp_customize->add_setting( 'ad_gfp_' . $key . '_font_weight', array(
					'default' => 'regular',
					'transport' => 'refresh'
				));

				$wp_customize->add_control( 'ad_gfp_' . $key . '_font_weight', array(
					'type'     => 'select',
					'label'    => __( 'Weight', 'anarieldesign' ),
					'section'  => 'ad_gfp_customizer_section_fonts',
					'priority' => "1".$i++,
					'choices'  => $this->all_font_weights
				));

			} // End foreach()

		} // End ad_gfp_register_customizer_options()

		function ad_gfp_generate_styles() {
			$tags_local = $this->tags;
			$font_css_register = array(); // Prevent loading the same stylesheet multiple times

			foreach($tags_local as $key => $value) {
				$font_family_temp = get_theme_mod("ad_gfp_" . $key . "_font_family");
				$font_weight_temp = get_theme_mod("ad_gfp_" . $key . "_font_weight");
				$font_family = str_replace(" ", "+", $font_family_temp);
				$css_selectors_string = preg_replace('/[^a-z0-9 \#\.\,\-]/i', '', strip_tags(get_theme_mod("ad_gfp_" . $key . "_css_selectors"))) ;

				// Some string replacement to help with the string functions used in the style generation
				if ($font_weight_temp == 'regular' ) {
					$font_weight = '400';
				} elseif($font_weight_temp == 'italic') {
					$font_weight = '400italic';
				} else {
					$font_weight = $font_weight_temp;
				}

				// Create array using the possible font weights for the font. Initialize an empty array if list_font_weights is NULL
				$possible_values = !empty($this->list_font_weights[$font_family_temp]) ? $this->list_font_weights[$font_family_temp] : array();

				// Check to see if the requested font style is available to avoid requesting CSS from Google that doesn't exist
				if(!in_array($font_weight, $possible_values)) { $font_weight = ''; }

				$font_string = $font_weight != '400' ? $font_family .':'. $font_weight : $font_family;

				if($font_family != 'default' && !empty($font_family) ) {?>
					<!-- AnarielDesign Google Font Picker -->
					<?php if(!array_key_exists($font_family_temp, $font_css_register)){ ?><link href='https://fonts.googleapis.com/css?family=<?php
					if( substr($font_string, -1) == ":" ) {
						echo $font_family;
					} else {
						echo $font_string;
					}
					?>' rel='stylesheet' type='text/css'><?php } ?>

					<style type="text/css"><?php
						if( empty($css_selectors_string) ){ echo $key; }
						elseif ( $key == "body" ) { echo $key . " " . $css_selectors_string; }
						else { echo $css_selectors_string . " " . $key; }
					?> { font-family: <?php echo "'$font_family_temp'"; ?>!important; <?php

					if(substr($font_weight,0,3) != '' && substr($font_weight,0,3) != '400' && !empty($font_weight)) {
						?> font-weight: <?php echo substr($font_weight,0,3); ?>!important;<?php
					}
					if(substr($font_weight,3) == 'italic') {
						?> font-style: <?php echo substr($font_weight,3);?>;<?php
					} ?> }</style>

				<?php } // End if()
				$font_css_register[$font_family_temp] = $font_family_temp; // Update the register to say we've included X.css
			} // End foreach ()

		} // End ad_gfp_generate_css()

    } // End Class
} // End if()
?>
