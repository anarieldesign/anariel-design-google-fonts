<?php

function ad_gfp_custom_controls( $wp_customize ) {

	// Description

	class AD_GFP_Tag extends WP_Customize_Control {
		public $type = 'tag';
		public function render_content() {
		?>
			<h3 class="ad_gfp"><?php echo esc_html( $this->label ); ?></h4>
		<?php
		}
	}
	// Autocomplete -- Requires the JS in gfonts.class.php > ad_gfp_js_controllers()

	class AD_GFP_Autocomplete extends WP_Customize_Control {
		public $type = 'autocomplete';
		public function render_content() {
		?>
			<label for="font-family"><?php echo esc_html( $this->label ) ?>: </label>
			<input class="font-family">
		<?php
		}
	}

}

add_action( 'customize_register', 'ad_gfp_custom_controls' );
?>