<?php
/**
 * Plugin Name:       Fast User Switching
 * Description:       Allow only administrators to switch to and impersonate any site user. Choose user to impersonate, by clicking new "Impersonate" link in the user list. To return to your own user, just log out. A log out link is available in the black top menu, top right, profile submenu.
 * Version:           1.2.2
 * Author:            Tikweb
 * Author URI:        http://www.tikweb.dk/
 * Plugin URI:        http://www.tikweb.com/wordpress/plugins/fast-user-switching/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       fast-user-switching
 * Domain Path:       /languages
*/

/*
Fast User Switching is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

Fast User Switching is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Fast User Switching. If not, see http://www.gnu.org/licenses/gpl-2.0.txt.
*/

if(!defined('ABSPATH')) exit;

if ( !class_exists('Tikweb_Impersonate') ):

	class Tikweb_Impersonate {
		/**
		 * Register all the hooks and filters for the plugin
		 */
		public function __construct() {
			// WP logout hook
			add_action('wp_logout',	array($this, 'unimpersonate'), 1);
			
			// Only admins can use this plugin (for obvious reasons)
			if(!current_user_can('add_users')) return;
			
			// Add a column to the user list table which will allow you to impersonate that user
			add_filter('manage_users_columns', array($this, 'user_table_columns'));
			add_action('manage_users_custom_column', array($this, 'user_table_columns_value'), 10, 3);
			
			// Is this request attempting to impersonate someone?
			if(isset($_GET['impersonate']) && !empty($_GET['impersonate'])){
				$this->impersonate($_GET['impersonate']);
			}

		}//End of __construct
		
		/**
		 * Add an additional column to the users table
		 * @param $columns - An array of the current columns
		 */
		public function user_table_columns($columns) {
			$columns['Tikweb_Impersonate']	= __('Switch user', 'fast-user-switching');
			return $columns;
		}
		
		/**
		 * Return the value for custom columns
		 * @param String $value		- Current value, not used
		 * @param String $column	- The name of the column to return the value for
		 * @param Integer $user_id	- The ID of the user to return the value for
		 * @return String
		 */
		function user_table_columns_value($value, $column, $user_id) {
			switch($column) {
				case 'Tikweb_Impersonate':
					$impersonate_url	= admin_url("?impersonate=$user_id");
					return "<a href='$impersonate_url'>".__('Switch user','fast-user-switching')."</a>";
				default: 
					return $value;
			}
		}

		public function saveRecentUser($user){

			$recent_user_opt = get_option('tikemp_recent_imp_users',[]);
			$roles = tikemp_get_readable_rolename($user->roles[0]);
			$keep = $user->data->ID.'&'.$user->data->display_name.'&'.$roles;

			if ( !in_array($keep, $recent_user_opt) ){
				array_unshift( $recent_user_opt, $keep );
			}

			if ( in_array($keep,$recent_user_opt) && $recent_user_opt[0] !== $keep ){
				$key = array_search($keep, $recent_user_opt);
				unset($recent_user_opt[$key]);
				array_unshift($recent_user_opt, $keep);
			}

			$recent_user_opt = array_slice($recent_user_opt, 0,5);
			update_option('tikemp_recent_imp_users',$recent_user_opt);

		}//End saveRecentUser

		/**
		 * Get get user id and switch to
		 */
		public function impersonate($user_id){

			global $current_user;
			
			$user = get_userdata( $user_id );

			if( $user === false ){
				return wp_redirect(admin_url());
			}

			$this->saveRecentUser($user);
			
			// We need to know what user we were before so we can go back
			$hashed_id = $this->encryptDecrypt('encrypt', $current_user->ID);
			setcookie('impersonated_by_'.COOKIEHASH, $hashed_id, 0, SITECOOKIEPATH, COOKIE_DOMAIN, false, true);
			
			// Login as the other user
			wp_set_auth_cookie($user_id, false);

			// If impresonate user is vendor than set vendor cookies.
			if( class_exists('WC_Product_Vendors_Utils') ){
				if ( WC_Product_Vendors_Utils::is_vendor( $user_id ) ){
					$vendor_data = WC_Product_Vendors_Utils::get_all_vendor_data( $user_id );
					$vendor_id = key($vendor_data);
					setcookie('woocommerce_pv_vendor_id_' . COOKIEHASH, absint($vendor_id), 0, SITECOOKIEPATH, COOKIE_DOMAIN);
				}				
			}//End if

			wp_redirect(admin_url());
			exit;
		}//End impersonate
		
		/**
		 * Switch back to old user
		 */
		public function unimpersonate(){
			$impersonated_by = self::impersonatedBy();
			if(!empty($impersonated_by)){
				wp_set_auth_cookie($impersonated_by, false);
				// Unset the cookie
				setcookie('impersonated_by_'.COOKIEHASH, 0, time()-3600, SITECOOKIEPATH, COOKIE_DOMAIN, false, true);
				wp_redirect(admin_url());
				exit;
			}
		}//End unimpersonate

		/**
		 * Initialize
		 */
		public static function init(){
			$instance = new self;
			return $instance;
		}

		/**
		 * Get impersonated user from cookie
		 */
		private static function impersonatedBy(){
			$key = 'impersonated_by_'.COOKIEHASH;
			if(isset($_COOKIE[$key]) && !empty($_COOKIE[$key])){
				$user_id = self::encryptDecrypt('decrypt', $_COOKIE[$key]);
				return $user_id;
			}else{
				return false;
			}
		}//impersonatedBy

		/**
		 * Change logout text
		 */
		public static function changeLogoutText($wp_admin_bar){
			// If user is impersonating, change the logout text
			$impersonatedBy = self::impersonatedBy();
			if(!empty($impersonatedBy)){
				$args = array(
					'id'    => 'logout',
					'title' => __('Switch to own user', 'fast-user-switching'),
					'meta'  => array( 'class' => 'logout' )
				);
				$wp_admin_bar->add_node($args);
			}
		}//End changeLogoutText

		/**
		 * Encript and Decrypt
		 */
		private static function encryptDecrypt($action, $string){
			$output = false;
			$encrypt_method = "AES-256-CBC";
			$secret_key = 'This is fus hidden key';
			$secret_iv = 'This is fus hidden iv';
			// hash
			$key = hash('sha256', $secret_key);

			// iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
			$iv = substr(hash('sha256', $secret_iv), 0, 16);
			if ($action == 'encrypt'){
				$output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
				$output = base64_encode($output);
			}else if($action == 'decrypt'){
				$output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
			}
			return $output;
		}// End encryptDecrypt


	} // Class end
	
	// Initialize the class
	add_action('init', array('Tikweb_Impersonate', 'init'));

	// Admin bar hook
	add_action('admin_bar_menu', array('Tikweb_Impersonate', 'changeLogoutText'));
endif; 

function tikemp_load_plugin_textdomain() {
    load_plugin_textdomain('fast-user-switching', FALSE, basename( dirname( __FILE__ ) ) . '/languages/');
}
add_action('plugins_loaded', 'tikemp_load_plugin_textdomain');


/**
 * plugin script to be enqueued in admin and frontend.
 * @return [type] [description]
 */
function tikemp_scripts(){
	wp_enqueue_script('tikemp_search_scroll', plugins_url( '/js/jquery.nicescroll.min.js', __FILE__ ), array( 'jquery' ),'1.1',true);
	wp_enqueue_script('tikemp_script', plugins_url( '/js/script.js', __FILE__ ), array( 'jquery','tikemp_search_scroll' ),'1.2',true);
}

add_action( 'admin_enqueue_scripts', 'tikemp_scripts' );
add_action( 'wp_enqueue_scripts', 'tikemp_scripts' );

/**
 * Return list of impersonated recent users list.
 * @return string [description]
 */
function tikemp_impersonate_rusers(){
	$ret = '';
	$opt = get_option('tikemp_recent_imp_users',[]);

	if ( !empty($opt) ){
		foreach ($opt as $key => $value) {
			
			$user = explode('&', $value);
			
			$user_id = isset($user[0]) ? $user[0] : null;
			$user_name = isset($user[1]) ? $user[1] : null;
			$user_role = isset($user[2]) ? $user[2] : null;

			$ret .= '<a href="'.admin_url("?impersonate=$user_id").'">'.$user_name.' ('.$user_role.')'.'</a>'.PHP_EOL;
		}
	}

	return $ret;
}


/**
 * Rendar user search function in wp admin bar. 
 */
function tikemp_adminbar_rendar(){

	// if admin_bar is showing.
	if(is_admin_bar_showing()){

		global $wp_admin_bar;

		// if current user can edit_users than he can see this.
		if(current_user_can('edit_users')){

			$wp_admin_bar->add_menu(
				array(
					'id'    => 'tikemp_impresonate_user',
					'title' => __('Switch user','fast-user-switching'),
					'href'  => '#',
				)
			);

			// search form
			$html = '<div id="tikemp_search">';
				$html .= '<form action="#" method="POST" id="tikemp_usearch_form" class="clear">';
					$html .= '<input type="text" name="tikemp_username" id="tikemp_username" placeholder="'.__('Username or ID','fast-user-switching').'">';
					$html .= '<input type="submit" value="'.__('Search','fast-user-switching').'" id="tikemp_search_submit">';
					$html .= '<input type="hidden" name="tikemp_search_nonce" value="'.wp_create_nonce( "tikemp_search_nonce" ).'">';
					$html .= '<div class="wp-clearfix"></div>';
				$html .= '</form>';
				$html .= '<div id="tikemp_usearch_result"></div>';
				$html .= '<div id="tikemp_recent_users">';
					$html .= '<strong>'.__('Recent Users','fast-user-switching').'</strong>';
					$html .= '<hr>'.tikemp_impersonate_rusers();
				$html .= '</div>';
			$html .= '</div>';

			$wp_admin_bar->add_menu(
				array(
					'id'		=> 'tikemp_impresonate_user_search',
					'parent'	=> 'tikemp_impresonate_user',
					'title'		=> $html,
				)
			);
		}//if(current_user_can('edit_users'))

	}//if(is_admin_bar_showing())	
}
add_action( 'wp_before_admin_bar_render', 'tikemp_adminbar_rendar', 1 );


/**
 * User search on ajax request
 */
function tikemp_user_search(){

	$query = isset($_POST['username']) ? trim($_POST['username']) : '';
	$nonce = $_POST['nonce'];

	if ( !wp_verify_nonce($nonce,'tikemp_search_nonce') ){
		exit();
	}

	$args = array(
		'search'	=> is_numeric( $query ) ? $query : '*' . $query . '*'
	);

	$user_query = new WP_User_Query( $args );
	$ret = '';

	$site_roles = tikemp_get_roles();

	if ( !empty($user_query->results) ){
		foreach ( $user_query->results as $user ) {

			if( $user->ID == get_current_user_id() ) {
				continue;
			}

			$ret .= '<a href="'.admin_url("?impersonate={$user->ID}").'">'.$user->display_name.' ('.$site_roles[$user->roles[0]].')'.'</a>'.PHP_EOL;
		}
	} else {
		$ret .= '<strong>'.__('No user found!','fast-user-switching').'</strong>'.PHP_EOL;
	}

	echo $ret;
	die();
}
add_action( 'wp_ajax_tikemp_user_search', 'tikemp_user_search' );
add_action( 'wp_ajax_nopriv_tikemp_user_search', 'tikemp_user_search' );

/**
 * Adminbar search bar 
 */
function tikemp_styles(){
?>
<style type="text/css">
#wpadminbar .quicklinks #wp-admin-bar-tikemp_impresonate_user ul li .ab-item{height:auto}#wpadminbar .quicklinks #wp-admin-bar-tikemp_impresonate_user #tikemp_username{height:22px;font-size:13px !important;padding:2px;width:145px;border-radius:2px !important;float:left;box-sizing:border-box !important;line-height: 10px;}#tikemp_search{width:auto;box-sizing:border-box}#tikemp_search_submit{height:22px;padding:2px;line-height:1.1;font-size:13px !important;border:0 !important;float:right;background-color:#fff !important;border-radius:2px !important;width:74px;box-sizing:border-box;color:#000 !important;}#tikemp_usearch_result{max-height: 320px;overflow-y: auto;margin-top:10px;float:left;}#tikemp_usearch_form{width: 226px}#tikemp_recent_users{float:left;}form#tikemp_usearch_form input[type="text"]{background-color:#fff !important;}
</style>
<?php
}
add_action( 'wp_head', 'tikemp_styles' );
add_action( 'admin_head', 'tikemp_styles' );

/**
 * Get site user roles
 * @return array array of roles and capabilities.
 */
function tikemp_get_roles(){
	
	$all_roles = wp_roles()->roles;
    
    $return_array = [];
    
    foreach($all_roles as $key => $role){
        $return_array[$key] = $role['name'];
    }

    return $return_array;
}

/**
 * Return readable rolename
 */
function tikemp_get_readable_rolename($role){
	$all_roles = tikemp_get_roles();

	$ret = isset($all_roles[$role]) ? $all_roles[$role] : 'subscriber';

	return $ret;
}

/**
 * Set ajax url
 */
function tikemp_ajax_urls(){
	?>
	<script>
		var tikemp_ajax_url = "<?php echo admin_url('admin-ajax.php');?>";
	</script>
	<?php
}
add_action( 'wp_head', 'tikemp_ajax_urls' );
add_action( 'admin_head', 'tikemp_ajax_urls' );