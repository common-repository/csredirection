<?php
/*
Plugin Name: csRedirection
Plugin URI: http://URI_Of_Page_Describing_Plugin_and_Updates
Description: A 404 capture and redirect plugin
Version: 1.0
Author: Christopher Shennan
Author URI: http://www.chrisshennan.com
*/

class csRedirection
{
	private $cs_redirector_db_version = '1.0';
	private $table_name = '';
	private $wpdb = null;
	private $current_user_id;
	
	public function csRedirection()
	{
		global $wpdb;
		$this->wpdb = $wpdb;
		
		$this->table_name = $wpdb->prefix . 'csredirection';
		
		if(is_admin()) {
			register_activation_hook(__FILE__, array($this, 'db_install'));
			
			add_action('admin_menu', array($this, 'admin_menu'));
			add_action('admin_head', array($this, 'admin_header'));
			
		} else {
			add_action ('template_redirect', array ($this, 'template_redirect'));
		}
	}
	
	// ADD THE MENU ITEMS
	public function admin_menu()
	{
		add_options_page('csRedirection', 'csRedirection', 'administrator', 'csRedirection-general', array($this,'admin_options'));
	}

	//
	public function admin_options() {
		if(is_admin()) {
			
			$subpage = $_GET['subpage'];
			if(!$subpage || !file_exists(dirname(__FILE__) . '/view/' . $_GET['subpage'] . '.php')) {
				$subpage = 'redirectionList';
			}
			
			$func_name = 'execute' . str_replace(' ' , '', ucwords(str_replace('_', ' ', $subpage)));
			if(is_callable(array($this, $func_name))) {
				call_user_func(array($this, $func_name));
			} else {
				die('Function ' . $func_name . ' not defined');	
			}
		}
	}
	
	public function template_redirect()
	{
		$url = $_SERVER['REQUEST_URI'];
		$query = "SELECT * FROM " . $this->table_name . ' ' .
			"WHERE source_url = '" . $url . "'";

		$results = $this->wpdb->get_results($query, ARRAY_A);
		if(count($results) > 0) {
			if(!is_null($results[0]['destination_url'])) {
				wp_redirect($results[0]['destination_url']);
				exit;
			} else {
				$update = "UPDATE " . $this->table_name . " SET " .
					"request_count = request_count + 1 " .
					"WHERE source_url = '" . $url . "'";
				$this->wpdb->query($update);
			}
		} else {
			$insert = "INSERT INTO " . $this->table_name . " (" . 
        			"source_url, destination_url, request_count, wp_user_id, created_at) VALUES (" .
        			"'" . $this->wpdb->escape($url) . "', " .
        			"null, " .
        			"1," .
        			"null, " . 
        			"NOW()" . 
					");";
			
			$results = $this->wpdb->query( $insert );	
		}
	}
	
	public function admin_header()
	{
		echo '<link type="text/css" rel="stylesheet" href="' . get_bloginfo('wpurl') . '/wp-content/plugins/csRedirection/assets/css/admin.css" />' . "\n";
	}
	
	// CREATE THE NECESSARY TABLES FOR THE PLUGIN
	public function db_install()
	{
		
		if($wpdb->get_var("SHOW TABLES LIKE '$this->table_name'") != $this->table_name) {
			
			$sql = 'CREATE TABLE ' . $this->table_name . '(' .
				'id bigint(20) NOT NULL AUTO_INCREMENT, ' . 
				'source_url varchar(255) NOT NULL, ' .
				'destination_url varchar(255), ' .
				'request_count int(11), ' . 
				'wp_user_id bigint(20), ' . 
				'created_at timestamp, ' .
				'UNIQUE KEY id (id)' .
				');';
			
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($sql);
			
			add_option('cs_redirector_db_version', $this->cs_redirector_db_version);
		}
	}
	
	public function processRedirectionEditForm($form_values)
	{
		$errorArray = array();
		if(!$form_values['source_url']) {
			$errorArray[] = 'Please specify a source url';
		}
		
		if(!$form_values['destination_url']) {
			$errorArray[] = 'Please specify a destination url';
		}
		
		if(empty($errorArray)) {
			if($form_values['id']) {
				$query = "UPDATE " . $this->table_name . " SET " . 
					"destination_url = '" . $this->wpdb->escape($form_values['destination_url']). "', " . 
					"wp_user_id = " . $this->getCurrentUserId() . " " . 
					"WHERE source_url = '" . $this->wpdb->escape($form_values['source_url']) . "' AND " .
						"id = " . $this->wpdb->escape($form_values['id']);
			} else {
				$insert = "INSERT INTO " . $this->table_name . " (" . 
	            			"source_url, destination_url, request_count, wp_user_id, created_at) VALUES (" .
	            			"'" . $this->wpdb->escape($form_values['source_url']) . "', " .
	            			"'" . $this->wpdb->escape($form_values['destination_url']) . "', " .
	            			"0," .
	            			"" . $this->getCurrentUserId() . ", " . 
	            			"NOW()" . 
							");";
			}
			//echo $query;exit;
			$results = $this->wpdb->query( $query );
			wp_redirect($_SERVER['REQUEST_URI'] . '&success=1');
			exit;
		} else {
			return $errorArray;
		}
	}
	
	public function processPreferencesForm($form_values)
	{

	}
	
	private function getCurrentUserId()
	{
		global $current_user;
		return $current_user->ID;
	}
	
	public function executeRedirectionList()
	{
		$query = 'SELECT * FROM ' . $this->wpdb->prefix . 'csredirection WHERE 1=1 ';
		$query .= "AND destination_url IS NULL "; 
		$query .= "ORDER BY request_count DESC";
	
		$results = $this->wpdb->get_results($query, ARRAY_A);

		include(dirname(__FILE__) . '/view/redirectionList.php');
	}
	
	public function executeRedirectionEdit()
	{
		$errorArray = array();
		if($_POST) {
			$defaults = $_POST;
			$func_name = 'process' . ucwords($_GET['subpage']) . 'Form';
			if(is_callable(array($this, $func_name))) {
				$errorArray = call_user_func(array($this, $func_name), $_POST);
			} else {
				die('Function ' . $func_name . ' not defined');	
			}				
		}
	
		$id = $_GET['id'];
		if(is_numeric($id)) {
			$query = 'SELECT * FROM ' . $this->table_name . ' WHERE id = ' . $id;
			$results = $this->wpdb->get_results($query, ARRAY_A);
			
			$defaults = $results[0];
			
		}
		include(dirname(__FILE__) . '/view/redirectionEdit.php');
	}
	
	public function executePreferences()
	{
		include(dirname(__FILE__) . '/view/preferences.php');
	}

	public function executeAbout()
	{
		include(dirname(__FILE__) . '/view/about.php');
	}
}

$csRedirection = new csRedirection();
?>
