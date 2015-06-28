<?php
/*
Plugin Name: Custom Google Map
Plugin URI: http://www.alexl.de/cgm
Description: A simple configurable Google Map for a post. Possibility to set a marker. 
Version: 0.1
Author: Alexander Lübeck
Author URI: http://www.alexl.de
License: GPL2

	Copyright 2013  Alexander Lübeck  (email : alex@alexl.de)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/


require_once( ABSPATH.PLUGINDIR."/mypluginbase/mypluginbase.php" );


class CustomGoogleMap extends MyPlugin {
	
	private $adminpage_url;
	
	
	public function __construct() {
		
		parent::__construct( array(
			'longname'		=> 'Custom Google Map Integrator',
			'shortname'		=> 'Custom Google Map',
			'optionname'	=> 'cgm_general_option',
			'pluginfile'	=> __FILE__
		));	
		
		
		$this->adminpage_url = "admin.php?page=".$this->slug;

		
		add_action( 'after_setup_theme', array($this, 'after_setup_theme'));
		add_action( 'save_post', array($this, 'savePost'), 10, 2);
		
	}
	
	
	public function after_setup_theme() {
	
		load_plugin_textdomain( 'cgm', false,  'al-custom-googlemap/i18n' );
	
	}
	
	
	/**
	 * @desc routine to add menupage/optionpage specific things
	 */
	public function create_menupage() {
		
		$page_suffix = add_options_page($this->longname, $this->shortname, $this->capability, $this->slug, array($this,'buildConfigPage'));
		
		add_action( "admin_head-$page_suffix", array($this, 'enqueueOptionpageScript') );
		
	}


	/**
	 * @desc additional scripts for optionpage of the plugin
	 */
	public function enqueueOptionpageScript() {
		
		wp_enqueue_style('cgm_css', $this->plugin_url . '/css/optionpage.css');
		
	}
	
	
	
	/**
	 * desc plugin hook for "add_meta_boxes"
	 */
	public function create_post_metaboxes($postType) {
		
		//@todo: set post types where display the metabox through option page
		$types = apply_filters('cgm_metabox_post-types', array('page'));
		if( in_array($postType, $types)) {
		
			wp_enqueue_style( 'cgm_metabox', $this->plugin_url . '/css/metabox.css');
			
			wp_enqueue_script('cgm_js', $this->plugin_url . '/js/custom_googlemap.js', array('jquery'));
			
			wp_localize_script('cgm_js', 'CustomGoogleMapVar', array( 
				
				'ajaxurl' 	=> admin_url( 'admin-ajax.php' ),
				'nonce' 	=> wp_create_nonce( 'nonce' ),
				'key'		=> 'AIzaSyBKNLnUnBPQhSf_mqxzdSbsnGep-oA6n7g'
			
			));
				
			add_meta_box(
				'metabox-cgm',									// Unique ID
				__( 'Lokalisierung mit GoogleMaps', 'cgm' ),	// Title
				array( &$this, 'createMetaboxContent' ),		// Callback function
				$postType,										// Admin page (or post type)
				'normal',										// Context
				'low'											// Priority
			);		
		}
				
		
	}

	
	
	public function createMetaboxContent() {
		
		$post = get_post($_POST['id']);
		
		$json_str = get_post_meta($post->ID, 'cgm_configmap',true);

		if(trim($json_str) == "") {
			
			$struct = array('map' => 
								array('maptype' => 'ROADMAP', 'zoom' => 10, 'center' => 
									array('lat' => '52.53106292115749', 'lng' => '13.48613836945038')
							), 
							'marker' => 
								array('lat' => '52.53106292115749', 'lng' => '13.48613836945038')
							);
			$json_str = json_encode($struct);
		}
		
		$str = htmlspecialchars($json_str);
		
		?>
			<p>
				<label for="cgm-addressfield"><?php _e('Adresse', 'cgm'); ?>:</label>
				<input type="text" id="cgm-addressfield" value="<?php echo apply_filters('cgm_standard_address', ''); ?>"></input>
				<button id="btnSearchAddress" class="button"><?php _e('Suchen', 'cgm'); ?></button>
			</p>
			<div id="custom-google-map" class="cgm-plugin"></div>
			<input type="hidden" id="cgm-configmap" name="cgm-configmap" value="<?php echo $str; ?>"></input>
		<?php
		
	}
	
	
	
	public function buildConfigPage() {
		
		?>
			<h2><?php echo $this->longname; ?></h2>
			<br/>
			<h3>For which types of posts the map configurator should be shown.</h3>	
			
		<?php
		
	}
	

	
	/**
	 * @desc hook when this post is saved
	 */
	public function savePost( $postid, $post ) {
	
		
		//skip auto save and revision save
	    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	    if ($post->post_type == 'revision') return;
	    
		
		
	    if(isset($_POST["cgm-configmap"]) )
	    	update_post_meta($postid, "cgm_configmap", $_POST["cgm-configmap"]);
	    

	    	
	}	

	
}


//instantiate the class
$cgm_var = new CustomGoogleMap();


?>