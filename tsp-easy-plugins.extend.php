<?php				
/**
 * TSP_Easy_Plugins_Settings_Facepile - Extends the TSP_Plugin_Settings Class
 * @package TSP_Easy_Plugins
 * @author sharrondenice, thesoftwarepeople
 * @author Sharron Denice, The Software People
 * @copyright 2013 The Software People
 * @license APACHE v2.0 (http://www.apache.org/licenses/LICENSE-2.0)
 * @version $Id: [FILE] [] [DATE] [TIME] [USER] $
 */

/**
 * Extends the TSP_Easy_Plugins_Settings_Facepile Class
 *
 * original author: Sharron Denice
 */
class TSP_Easy_Plugins_Settings_Facepile extends TSP_Easy_Plugins_Settings
{
	/**
	 * Display all the plugins that The Software People has released
	 *
	 * @since 1.1.0
	 *
	 * @param none
	 *
	 * @return output to stdout
	 */
	public function display_parent_page()
	{
		$active_plugins			= get_option('active_plugins');
		$all_plugins 			= get_plugins();
	
		$free_active_plugins 	= array();
		$free_installed_plugins = array();
		$free_recommend_plugins = array();
		
		$pro_active_plugins 	= array();
		$pro_installed_plugins 	= array();
		$pro_recommend_plugins 	= array();
		
		$plugins_txt = file_get_contents( $this->plugin_globals['plugin_list'] );
		$tsp_plugins =  preg_split( "/\n/", $plugins_txt );

		foreach ( $tsp_plugins as $line => $meta )
		{
			$tsp_plugins[$line] = preg_split("/\|/", $meta );
			
			$plugin_data = $tsp_plugins[$line];
			
			$plugin_type 	= $plugin_data[0];
			$plugin_file	= $plugin_data[1];
			
			$saved_plugin = array (
				'title' 	=> $plugin_data[2],
				'desc' 		=> $plugin_data[3],
				'more_url' 	=> $plugin_data[4],
				'store_url' => $plugin_data[5],
				'wp_url' 	=> $plugin_data[6],
				'settings' 	=> $plugin_data[7]
			);
			
			if ( $plugin_type == 'FREE' )
			{
				if ( in_array(str_replace( "\\", "", $plugin_file), $active_plugins ) )
				{
					$free_active_plugins[] = $saved_plugin;
				}//endif
				elseif ( array_key_exists(str_replace( "\\", "", $plugin_file), $all_plugins ) )
				{
					$free_installed_plugins[] = $saved_plugin;
				}//end elseif
				else
				{
					$free_recommend_plugins[] = $saved_plugin;
				}//endelse
			}//endif
			elseif ( $plugin_type == 'PRO' )
			{
				if ( in_array(str_replace( "\\", "", $plugin_file), $active_plugins ) )
				{
					$pro_active_plugins[] = $saved_plugin;
				}//endif
				elseif ( array_key_exists(str_replace( "\\", "", $plugin_file), $all_plugins ) )
				{
					$pro_installed_plugins[] = $saved_plugin;
				}//endelseif
				else
				{
					$pro_recommend_plugins[] = $saved_plugin;
				}//endelse
			}//endelseif
			
		}//endforeach
		
		$free_active_count									= count($free_active_plugins);
		$free_installed_count 								= count($free_installed_plugins);
		$free_recommend_count 								= count($free_recommend_plugins);

		$free_total											= $free_active_count + $free_installed_count + $free_recommend_count;

		$pro_active_count									= count($pro_active_plugins);
		$pro_installed_count 								= count($pro_installed_plugins);
		$pro_recommend_count 								= count($pro_recommend_plugins);
		
		$pro_total											= $pro_active_count + $pro_installed_count + $pro_recommend_count;
				
		// Display settings to screen
		$smarty = TSP_Easy_Plugins_Smarty::get_smarty( $this->plugin_globals );
		
		$smarty->assign( 'free_active_count',		$free_active_count);
		$smarty->assign( 'free_installed_count',	$free_installed_count);
		$smarty->assign( 'free_recommend_count',	$free_recommend_count);

		$smarty->assign( 'pro_active_count',		$pro_active_count);
		$smarty->assign( 'pro_installed_count',		$pro_installed_count);
		$smarty->assign( 'pro_recommend_count',		$pro_recommend_count);
		
		$smarty->assign( 'free_active_plugins',		$free_active_plugins);
		$smarty->assign( 'free_installed_plugins',	$free_installed_plugins);
		$smarty->assign( 'free_recommend_plugins',	$free_recommend_plugins);

		$smarty->assign( 'pro_active_plugins',		$pro_active_plugins);
		$smarty->assign( 'pro_installed_plugins',	$pro_installed_plugins);
		$smarty->assign( 'pro_recommend_plugins',	$pro_recommend_plugins);

		$smarty->assign( 'free_total',				$free_total);
		$smarty->assign( 'pro_total',				$pro_total);

		$smarty->assign( 'title',					"WordPress Plugins by The Software People");
		$smarty->assign( 'contact_url',				$this->plugin_globals['contact_url']);

		$smarty->display( $this->plugin_globals['name'] . '_admin_menu.tpl');
	}//end ad_menu
	
	/**
	 * Implements the settings_page to display settings specific to this plugin
	 *
	 * @since 1.1.0
	 *
	 * @param none
	 *
	 * @return output to screen
	 */
	function display_plugin_settings_page() 
	{
		$message = "";
		
		$error = "";
		
		// get settings from database
		$defaults = new TSP_Easy_Plugins_Globals ( get_option( $this->plugin_globals['option_name'] ) );

		$form = null;
		if ( array_key_exists( $this->plugin_globals['name'] . '_form_submit', $_REQUEST ))
		{
			$form = $_REQUEST[ $this->plugin_globals['name'] . '_form_submit'];
		}//endif
				
		// Save data for settings page
		if( isset( $form ) && check_admin_referer( $this->plugin_globals['name'], $this->plugin_globals['name'] . '_nonce_name' ) ) {
			
			$defaults->set_form_field_values( $_REQUEST );
			
			$settings = $defaults->get();
			
			update_option( $this->plugin_globals['option_name'], $settings );
			
			$message = __( "Options saved.", $this->plugin_globals['name'] );
		}

		$form_fields = $defaults->get_form_field_values( true );

		// Display settings to screen
		$smarty = TSP_Easy_Plugins_Smarty::get_smarty( $this->plugin_globals, true );
		
		$smarty->assign( 'form_fields',				$form_fields);
		$smarty->assign( 'message',					$message);
		$smarty->assign( 'error',					$error);
		$smarty->assign( 'form',					$form);
		$smarty->assign( 'plugin_name',				$this->plugin_globals['name']);
		$smarty->assign( 'nonce_name',				wp_nonce_field( $this->plugin_globals['name'], $this->plugin_globals['name'].'_nonce_name' ));
		
		$smarty->display( $this->plugin_globals['name'] . '_shortcode_settings.tpl');
				
	}//end settings_page
	
}//end TSP_Easy_Plugins_Settings_View


/**
 * TSP_Easy_Plugins_Widget_Facepile - Extends the TSP_Easy_Plugins_Widget Class
 * @package TSPEasyPlugin
 * @author sharrondenice, thesoftwarepeople
 * @author Sharron Denice, The Software People
 * @copyright 2013 The Software People
 * @license APACHE v2.0 (http://www.apache.org/licenses/LICENSE-2.0)
 * @version $Id: [FILE] [] [DATE] [TIME] [USER] $
 */

/**
 * Extends the TSP_Easy_Plugins_Widget_Facepile Class
 *
 * original author: Sharron Denice
 */
class TSP_Easy_Plugins_Widget_Facepile extends TSP_Easy_Plugins_Widget
{
	/**
	 * PHP4 constructor
	 */
	public function TSP_Easy_Plugins_Widget_Facepile() 
	{
		TSP_Easy_Plugins_Widget_Facepile::__construct();
	}//end TSP_Plugin_Widget

	/**
	 * PHP5 constructor
	 */
	public function __construct() 
	{
		// TODO: figure out a way to set globals without doing it directly
		$this->plugin_globals 		= get_option ( 'tsp-facepile-option' );
		
        // Create the widget
		parent::__construct( $this->plugin_globals );
	}//end __construct

	
	/**
	 * Override required of form function to display widget information
	 *
	 * @since 1.1.0
	 *
	 * @param array $instance Required - array of current values
	 *
	 * @return display to widget box
	 */
	public function display_form( $fields )
	{
		foreach ( $fields as $key => $value )
		{
			// since there are multiple widgets on a page it is important
			// to make sure the id and name are unique to this particular
			// instance of the plugin so override the id and name
			$fields[$key]['id'] 		= $this->get_field_id($key);
			$fields[$key]['name'] 		= $this->get_field_name($key);
		}//end foreach

		$smarty = TSP_Easy_Plugins_Smarty::get_smarty( $this->plugin_globals, true );
    	$smarty->assign('form_fields', $fields);
    	$smarty->assign('class', 'widefat');
		$smarty->display( $this->plugin_globals['name'] . '_form.tpl');
	}//end form
	
	/**
	 * Implementation (required) to print widget & shortcode information to screen
	 *
	 * @since 1.1.0
	 *
	 * @param array $fields  - the settings to display
	 * @param boolean $echo Optional - if false returns output instead of displaying to screen
	 *
	 * @return string $output if echo is true displays to screen else returns string
	 */
	public function display_widget( $fields, $echo = true )
	{
	    extract ( $fields );
	    
		$return_HTML = "";

	    // If there is a title insert before/after title tags
	    if (!empty($title)) {
	        $return_HTML .= $before_title . $title . $after_title;
	    }
	    
	    global $wpdb;
	    
	    // Query all subscribers
		$user_count = $num_rows * $num_cols;
		
		// Display subscribers only  
		$total_users = $wpdb->get_var("SELECT COUNT($wpdb->users.ID) FROM $wpdb->users INNER JOIN $wpdb->usermeta ON $wpdb->users.ID = $wpdb->usermeta.user_id WHERE $wpdb->usermeta.meta_key = 'wp_user_level' AND $wpdb->usermeta.meta_value = '0'");
		
		$query = "SELECT $wpdb->users.ID FROM $wpdb->users INNER JOIN $wpdb->usermeta ON $wpdb->users.ID = $wpdb->usermeta.user_id WHERE $wpdb->usermeta.meta_key = 'wp_user_level' AND $wpdb->usermeta.meta_value = '0' ORDER BY RAND() LIMIT 0, $user_count";
		$users = $wpdb->get_results($query);
		
		$entry_cnt = 0;
		$num_entries = $user_count;
	
		if (!empty($users))
		{
			$smarty = TSP_Easy_Plugins_Smarty::get_smarty( $this->plugin_globals );

		    // Store values into Smarty
		    foreach ($fields as $key => $val)
		    {
		    	$smarty->assign($key, $val, true);
	   		}//end foreach
	
	       	for ($i=0; $i < $num_rows; $i++) 
	       	{ 
				$start_row = true;
				$end_row = false;
				
				for ($j=0; $j < $num_cols; $j++) 
				{ 		        
			        $entry_cnt++;
			
					if ($entry_cnt == 1)
						$smarty->assign("first_entry", true, true);
					else
						$smarty->assign("first_entry", null, true);
										
					if ($entry_cnt == $num_entries)
						$smarty->assign("last_entry", true, true);
					else
						$smarty->assign("last_entry", null, true);
	
					// get the current user index
					$user_index = ($i * $num_cols) + $j;
					
					// If we are done processing users then break
					if($user_index >= $user_count)
					{
						break;
					}//endif
					// If we are on the last row display the ending tr tag
					elseif ($user_index == ($user_count - 1))
					{
						$end_row = true;
					}//endelseif
						
					$email = "";
					$name = "";

					if ( array_key_exists( $user_index, $users ))
					{
						$user = $users[$user_index]; // current user
						$curauth = get_userdata($user->ID); //user data

						if ($show_names == 'Y')
							$name = $curauth->display_name;
							
						$email = $curauth->user_email;
					}//end if
											
					$gravatar_type = get_option('wpu_gravatar_type');
					$display_gravatar = get_avatar($email, $thumb_size, $gravatar_type, $name); //get avatar
	
					$smarty->assign("key", $this->plugin_globals['key'], true);
					$smarty->assign("start_row", $start_row, true);
					$smarty->assign("end_row", $end_row, true);
					$smarty->assign("total_users", $total_users, true);
					$smarty->assign("display_gravatar", $display_gravatar, true);
					
					$return_HTML .= $smarty->fetch( $this->plugin_globals['name'] . '_widget.tpl');
					
					$start_row = false;
	
				}//endfor
			}//endfor
	
		}// end if
		
		if ($echo)
			echo $return_HTML;
		
		return $return_HTML;
	}//end display
}//end TSP_Easy_Plugins_Widget_Facepile
?>