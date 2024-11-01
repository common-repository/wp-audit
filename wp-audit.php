<?php
/**
 * Plugin Name: &#x200B;WP Audit
 * Plugin URI: https://wp-audit.com
 * Description: Provides audit data for site health and client reporting 
 * Version: 0.5.7
 * Author: Xitadel Technologies
 * Author URI: https://xitadel.net
 *
 * Text Domain: wpaudit
 *
 * @package WPAudit
 */

global $wpaudit;
$wpaudit['tabs'] = array(
      //'overview',
      'environment',
      'wordpress',
      'plugins',
      'themes',
      'content',
      'database',
      'performance',
      'privacy',
      'errors'
    );

$wpaudit['modules'] = array(
      'overview',
      'environment',
      'performance',
      'wordpress',
      'database',
      'server',
      'plugins',
      'themes',
      'content',
      'ecommerce',
      'whois',
      'recommendations'
    );
 
add_action('rest_api_init', function(){
    $wpaudit_rest = new WPAudit_REST();
    $wpaudit_rest->register_routes();

});

class WPAudit_REST extends WP_REST_Controller {

  /**
   * Register the routes for the objects of the controller.
   */
  public function register_routes() {
    global $wpaudit;
    $namespace = 'wp-audit';
    $modules = $wpaudit['modules'];
    $modules[] = 'detailed';
    
    foreach ($modules as $module){
      register_rest_route( $namespace, '/'.$module, array(
        array(
          'methods'         => 'GET',
          'callback'        => array( $this, 'get_'.str_replace('/','_',$module)),
          'permission_callback' => array( $this, 'get_items_permissions_check' ),
        ),
      ));
    }

  }
  
 /**
  * Get Detailed Audit (Return all modules)
  */
  public function get_detailed($request){
    $data = array();
    
    foreach($this->modules as $module){ 
      $data[$module] = call_user_func(array($this,"get_$module"));
    }
    
    return $data;
  }
    
 /**
  * Get Overview information
  */
  public function get_overview($request){
    $data = array();
    
    $data['Performance'] = $this->get_performance($request);
    
    return $data;
  }    
 /**
  * Get Platform information
  */
  public function get_environment($request){
    $data = array();
    
    $data['Web'] = $this->get_server($request);
    $data['Database'] = $this->get_database($request);
    $data['Whois'] = $this->get_whois($request);
    
    return $data;
  }
  
 
 /**
  * Get Plugin information
  */
  public function get_plugins($request){
    $data = array();
		require_once(ABSPATH . 'wp-admin/includes/update.php');
    $updates = get_plugin_updates($request);
    $plugins = get_plugins();
    
    
    foreach($updates as $plugin){
      $licensed = (is_null($plugin->update->package))?true:false;
      
      if(is_multisite()){
        $info_url = network_admin_url('plugin-install.php?tab=plugin-information&plugin=' . $plugin->update->slug . '&section=changelog');
      }else{
        $info_url = self_admin_url('plugin-install.php?tab=plugin-information&plugin=' . $plugin->update->slug . '&section=changelog');
      }
    
      $update_data['Name'] = $plugin->Name;
      $update_data['Licensed'] = $licensed;
      $update_data['Installed_Version'] = $plugin->Version;
      $update_data['Current_Version'] = $plugin->update->new_version;
      $update_data['Release_Date'] = $plugin->update->release_date;
      $update_data['Tested_With'] = $plugin->update->tested;
      $update_data['Info_Url'] = $info_url;
      
      $plugin_info = unserialize(wp_remote_request("http://api.wordpress.org/plugins/info/1.0/".$plugin->update->slug));
      $update_data['Changelog'] = $plugin_info->sections['changelog'];
      //$update_data['API_Data'] = $plugin_info;
      $data['Updates'][] = $this->prepare_response_for_collection($update_data);
    }
    
    $wpaudit_disallowed = json_decode(wp_remote_request("https://wp-audit.com/wp-json/wp/v2/plugins/?categories=9&per_page=100&".time()));
    $disallowed_matches = array();
    foreach($wpaudit_disallowed as $plugin){
      $disallowed_matches[] = $plugin->slug;
    }
    $active_count = 0;
    $disallowed_count = 0;
    foreach($plugins as $plugin => $plugin_data){
            
      $plugin_data['Path'] = $plugin;
      if(is_plugin_active($plugin)){
        $plugin_data['Active'] = true;
        $active_count++;
      }else{
        $plugin_data['Active'] = false;
      }
      
      $slug = current(explode(".",end(explode("/",$plugin))));
      if(in_array($slug,$disallowed_matches)){
        $plugin_data['Disallowed'] = true;
        $data['Disallowed'][] = $this->prepare_response_for_collection($plugin_data);
        $disallowed_count++;
      }
      
      $data['Items'][] = $this->prepare_response_for_collection($plugin_data);      
    }

    $data = array(
      'Installed_Plugins' => count($plugins),
      'Active_Plugins'=>$active_count,
      'Inactive_Plugins' => count($plugins)-$active_count,
      'Disallowed_Plugins' => $disallowed_count,
      'Plugin_Updates' => count($updates),
    ) + $data;
    
    return new WP_REST_Response( $data, 200 );
  }
  
  /**
   * Get theme information
   */
  public function get_themes($request){
    $data = array();
		require_once(ABSPATH . 'wp-admin/includes/update.php');
    $updates = get_theme_updates();
    $themes = get_themes();
    foreach($themes as $theme){
      $theme_data = $this->prepare_theme_for_response($theme,$request);
      $data['Items'][] = $this->prepare_response_for_collection($theme_data);      
    }
    
    foreach($updates as $update => $update_data){
      $data['Updates'][] = $this->prepare_response_for_collection($update_data);
    }
    
    $data = array(
      'Installed_Themes' => count($themes),
      'Updates' => count($updates),
    )+$data;
    
    return new WP_REST_Response( $data, 200 );
  }

  /**
   * WordPress
   */
  public function get_wordpress($request){
    $data = array();
    
    // Secure Connection
		$data['Secure_Connection'] = (!is_null(fsockopen($host,443,$errno,$errstr,30)))?'SSL enabled':'SSL not enabled';
    
    // Version
    global $wp_version;
    $wp_api = json_decode(wp_remote_request("http://api.wordpress.org/core/version-check/1.7/?version=".$wp_version));    
    $data['Installed_Version'] = $wp_version;
    $data['Current_Version'] = $wp_api->offers[0]->current;
    $data['Status'] = $wp_api->offers[0]->response;
    
    // Multisite
    $data['Multisite'] = is_multisite();
    if(is_multisite()){
      $sites = get_sites();
      foreach($sites as $site){
        $site_data['Domain'] = $site->domain;
        $site_data['Site_ID'] = (int) $site->blog_id;
        $site_data['Network_ID'] = (int) $site->site_id;
        $data['Sites'][] = $this->prepare_response_for_collection($site_data);
      }
    }
    
    return new WP_REST_Response( $data, 200 );
  }
  
  /**
   * Content
   */
  public function get_content($request){
    $data = array();
    
    // Pages
    $pages = get_pages();
    $data['Page_Count'] = count($pages);
    foreach($pages as $page){
      $page_data['Title'] = $page->post_title;
      $page_data['Url'] = $page->guid;
      $data['Pages'][] =  $this->prepare_response_for_collection($page_data);
    }
    
    // Posts
    $posts = get_posts();
    $data['Post_Count'] = count($posts);
    foreach($posts as $post){
      $post_data['Title'] = $post->post_title;
      $post_data['Url'] = $post->guid;
      $data['Posts'][] =  $this->prepare_response_for_collection($post_data);
    }
    
    // Custom Types
    $types = get_post_types(array('_builtin' => false),'objects');
    $data['Custom_Type_Count'] = count($types);
    foreach($types as $type){
      $args = array(
        'post_type'=> $type->name
      );

      $the_query = new WP_Query();
      $items = $the_query->query($args);
      
      $data[ucwords($type->name,'_').'_Count'] = count($items);
      $data[ucwords($type->name,'_').'_Schema'] = $this->prepare_response_for_collection($type);
      
      foreach($items as $item){
        $item_data['Title'] = $item->post_title;
        $item_data['Url'] = $item->guid;
        $data[str_replace(' ','_',$type->label)][] = $this->prepare_response_for_collection($item_data);
      }
    }
    
    return new WP_REST_Response( $data, 200 );
  }
  
  public function get_content_pages($request){
    $data = array();
    $data['Pages'] = get_pages();
    
    return new WP_REST_Response( $data, 200 );
  }
    
  public function get_content_posts($request){
    $data = array();
    $data['Posts'] = get_posts();
    
    return new WP_REST_Response( $data, 200 );
  }
  
  /**
   * eCommerce
   */
  public function get_ecommerce($request){
    $data = array();
   
    // Dynamic list of carts from WP-Audit.com
    $carts = json_decode(wp_remote_request("https://wp-audit.com/wp-json/wp/v2/plugins/?tags=21&".time())); //eCommerce

    foreach($carts as $key => $cart){
      $path = ($cart->detection_path!=="")?$cart->detection_path:$cart->slug."/".$cart->slug.".php";
      if(file_exists(WP_PLUGIN_DIR."/$path")){
        $plugin = (object) get_plugin_data(WP_PLUGIN_DIR."/$path");
        $cart_data['Name'] = $plugin->Name;
        $cart_data['Title'] = $plugin->Title;
        $cart_data['Version'] = $plugin->Version;
        $cart_data['Installed'] = true;
        $cart_data['Active'] = is_plugin_active("$path");
        $cart_data['Payment_Gateways'] = $this->get_payment_gateways($cart->slug);
        $cart_data['Data'] = call_user_func(array($this,"get_".$cart->slug));
        $data[str_replace(" ","",$cart->title->rendered)] = $cart_data;        
      }
    }
    
    return new WP_REST_Response( $data, 200 );
  }
  
  public function get_payment_gateways($cart){
    $data = array();
    $gateways = array();
    
    if(count($gateways)==0){
      $data['Message'] =   "No information available.";
    }
    
    return $data;
  }
  
  public function get_woocommerce(){
    $data = array();
    
    if(is_plugin_active(WP_PLUGIN_DIR."/woocommerce/woocommerce.php")){ 
      global $woocommerce;
      $products = wc_get_products(); // Get info via WC object
    }else{
      $the_query = new WP_Query(); // Get info va DB query
      $products = $the_query->query(array('post_type'=> 'product'));
    }
    $data['Product_Count'] = count($products);
    if(count($products)>0){
      foreach($products as $product){
        $product_data['Title'] = $product->post_title;
        $product_data['Url'] = $product->guid;
        $data['Products'][] = $product_data;
      }
    }
    
    return $data;
  }
  
  public function get_shopify(){
    $data = array();
    $data['Message'] = "Shopify details not available.";
    
    return $data;
  }
  
  public function get_wpecommerce(){
    $data = array();
    $data['Message'] = "WP E Commerce details not available.";
    
    return $data;
  }
  
  public function get_marketpress(){
    $data = array();
    $data['Message'] = "MarketPress details not available.";
        
    return $data;
  }
  
  /**
   * Database
   */
  public function get_database($request){
    $data = array();
    global $wpdb;
    $action = $request['action'];
    
    switch($action) {
      case 'analyze':
        $data = $this->analyze_database($request);
        break;
      case 'optimize':
        $data = $this->optimize_database($request);
        break;
      default:
        $data = $this->get_database_details($request);
        break;
    }

    return new WP_REST_Response( $data, 200 );
  }
  
  function analyze_database($request){
    $data = array();
    global $wpdb;
    
    $tables = explode(',',$request['tables']);
    
    if($tables[0]=='all'){
      $tables = $wpdb->get_results( 'SHOW TABLE STATUS', ARRAY_N );
    }

    if(count($tables)>1){
      foreach($tables as $table){
        $data[] = $this->analyze_table($table[0]);
      }
    }else{
      $data = $this->analyze_table($tables[0]);
    }
    
    return $data;
  }
  
  function analyze_table($table){
    $data = array();
    global $wpdb;
    
    $data['analyze'] = $wpdb->get_results("ANALYZE TABLE $table");
    return $data;
  }
  
  
  function optimize_database($request){
    $data = array();
    global $wpdb;
    
    $tables = explode(',',$request['tables']);
    
    if($tables[0]=='all'){
      $tables = $wpdb->get_results( 'SHOW TABLE STATUS', ARRAY_N );
    }

    if(count($tables)>1){
      foreach($tables as $table){
        $data[] = $this->optimize_table($table[0]);
      }
    }else{
      $data = $this->optimize_table($tables[0]);
    }
    
    return $data;
  }
  
  function optimize_table($table){
    $data = array();
    global $wpdb;
    
    $data['optimize'] = $wpdb->get_results("OPTIMIZE TABLE $table");
    return $data;
  }
  
  
  function get_database_details($request){
    $data = array();
    global $wpdb;
    
    // DB Info
    $data['Server'] = ($wpdb->is_mysql==1)?"MySql":"MariaDB";
    $data['Object'] = $wpdb;
    $version = $wpdb->dbh->server_version;
    $version_patch = substr($version,-2,2);
    $version_minor = substr($version,-4,2);
    $version_major = substr($version,0,strlen($version)-4);
    $data['Server_Version'] = "$version_major.$version_minor.$version_patch";
    
    // DB Stats
    $stats = explode("  ",$wpdb->dbh->stat);
    foreach($stats as $stat){
      $stat = explode(": ",$stat);
      $data['Stat'][str_replace(" ","_",$stat[0])] = (int) $stat[1];
    }

    // Table Info
    $tables = $wpdb->get_results( 'SHOW TABLE STATUS', ARRAY_N );
    $data['Table_Count'] = count($tables);
    $db_size = 0;
    foreach($tables as $table){
      $table_data['Table'] = $table[0];
      $table_data['Row_Count'] = (int) $table[4];
      $table_data['Index_Size'] = (int) $table[8];
      $table_data['Data_Free'] = (int) $table[9];
      $table_data['Data_Size'] = (int) $table[6];
      $table_data['Total_Size'] = (int) $table[6]+$table[8];
      $table_data['Average_Size_Per_Row'] = (int) $table[5];
      $data['Tables'][] = $table_data;
      $db_size += $table_data['Total_Size'];
    }
    $data = array('Database_Size' => $db_size) + $data;
    
    return $data;
  }
  
  
  /**
   * Server
   */
  public function get_server($request){
    $data = array();
    
    $os = PHP_OS;
    $version = explode(" ",php_uname('v'));
    $data['Platform'] = "$os $version[0]";
    $data['Server'] = $_SERVER['SERVER_SOFTWARE'];
    $data['Server_Protocol'] = $_SERVER['SERVER_PROTOCOL'];
    $data['HTTPS'] = $_SERVER['HTTPS'];
    $data['Document_Root'] = $_SERVER['DOCUMENT_ROOT'];
    $data['Language'] = "PHP ". explode("-",PHP_VERSION)[0];
    $data['Memory_Limit'] = WPAudit_Helpers::formatBytes((int) substr(ini_get('memory_limit'),0,strlen(ini_get('memory_limit'))-1)*1024*1024);
    $data['Memory_Current'] = WPAudit_Helpers::formatBytes(memory_get_usage(true));
    $data['CURL'] = WPAudit_Helpers::get_php_curl();
    $data['Extensions'] = WPAudit_Helpers::get_php_loaded_extensions();
    $data['Host_Name'] = $_SERVER['SERVER_ADDR'];

    
    // Storage
    $data['Storage']['Total_Size'] = WPAudit_Helpers::folderSize(ABSPATH);

    // Root folders
    $paths = array_diff(scandir(ABSPATH), array(basename(WP_CONTENT_DIR), 'wp-admin','wp-includes','wp-content','..', '.'));
    $data['Storage'] += WPAudit_Helpers::folderInfo('Root',ABSPATH,$paths);
    
    // WP-Content
    $paths = array_diff(scandir(WP_CONTENT_DIR), array('uploads','..', '.'));
    $data['Storage'] += WPAudit_Helpers::folderInfo('WP-Content',WP_CONTENT_DIR,$paths);
    
    // Uploads
    $paths = array_diff(scandir(WP_CONTENT_DIR."/uploads"), array('..', '.'));
    $data['Storage'] += WPAudit_Helpers::folderInfo('Uploads',WP_CONTENT_DIR."/uploads",$paths);

    
    return new WP_REST_Response( $data, 200 );
  }
  
  /**
   * Whois
   */
  public function get_whois($request){
    $data = array();
    
    $name = explode('.', $_SERVER['SERVER_NAME']);
		$tld = end($name);
		array_pop($name);
		$base = end($name);
		$protocol = strtolower(substr($_SERVER["SERVER_PROTOCOL"],0,strpos( $_SERVER["SERVER_PROTOCOL"],'/'))).'://';
		$host = $protocol.$base.'.'.$tld;

		$json = wp_remote_request("https://api.wp-audit.com/whois/?url=".$host);
		$data = json_decode($json['body'])->whois;
    
    return new WP_REST_Response( $data, 200 );
  }
  
  /**
   * Performance
   */
  public function get_performance($request){
    $data = array();
    
    $pagespeed = json_decode(wp_remote_request("https://www.googleapis.com/pagespeedonline/v4/runPagespeed?url=".get_bloginfo('url')));
    $data['Title'] = $pagespeed->title;
    $data['Url'] = $pagespeed->id;
    $data['Score'] = $pagespeed->ruleGroups->SPEED->score;
    $stats = $pagespeed->pageStats;
    foreach($stats as $key => $stat){
      $data['Stats'][$key] = (int) $stat;
    }
    
    return new WP_REST_Response( $data, 200 );
  }
    
  /**
   * Recommendations
   */
  public function get_recommendations($request){
    $data = array();
    
    $pagespeed = json_decode(wp_remote_request("https://www.googleapis.com/pagespeedonline/v4/runPagespeed?url=".get_bloginfo('url')));
     
    $data['Title'] = $pagespeed->title;
    $data['Url'] = $pagespeed->id;
    $data['Score'] = $pagespeed->ruleGroups->SPEED->score;
    
    $recommendations = $pagespeed->formattedResults->ruleResults;
    foreach($recommendations as $recommendation){
      $recommendation_data['Title'] = $recommendation->localizedRuleName;
      $recommendation_data['Impact'] = $recommendation->ruleImpact;
      $recommendation_data['Group'] = $recommendation->groups[0];
      $recommendation_data['Details']['Summary'] = $this->prepare_format_for_response($recommendation->summary);
      //$recommendation_data['Details']['Url_Blocks'] = $this->prepare_blocks_for_response($recommendation->urlBlocks);
      foreach($recommendation->urlBlocks as $block){
        $block_data['Header'] = $this->prepare_format_for_response($block->header);
        foreach($block->urls as $url){
          $block_data['Urls'][] = $this->prepare_format_for_response($url->result);
        }
        $recommendation_data['Details']['Url_Blocks'][] = $block_data;
      }
      $data['Items'][] = $recommendation_data;   
    }
    
    return new WP_REST_Response( $data, 200 );
  }


  public function prepare_theme_for_response( $theme, $request ) {
    $data   = array();
    $data['Name'] = $theme->get( 'Name' );
    $data['ThemeURI'] = $theme->get( 'ThemeURI' );
    $data['Description'] = $theme->get( 'Description' );
    $data['Author'] = $theme->get( 'Author' );
    $data['AuthorURI'] = $theme->get( 'AuthorURI' );
    $data['Version'] = $theme->get( 'Version' );
    $data['Template'] = $theme->get( 'Template' );
    $data['Status'] = $theme->get( 'Status' );
    $data['Tags'] = $theme->get( 'Tags' );
    $data['TextDomain'] = $theme->get( 'TextDomain' );
    $data['DomainPath'] = $theme->get( 'DomainPath' );
    $data['Path'] = $theme->theme_root.'/'.$theme->stylesheet;
    $data['Update'] = $theme->update;
    
    $response = rest_ensure_response( $data );
    return $response;
  }

  public function prepare_format_for_response($details,$request){
    $format = $details->format;
    foreach($details->args as $arg){
      switch ($arg->key){
        case "LINK":
          $url = $arg->value;
          $begin_link = "<a href='$url' target='_blank'>";
          $format = str_replace("{{BEGIN_LINK}}",$begin_link,$format);
          $format = str_replace("{{END_LINK}}","</a>",$format);
          break;
        default:
          $format = str_replace("{{".$arg->key."}}",$arg->value,$format);
          break;
      }
    }
    
    return $format;
  }
 
  /**
   * Check if a given request has access to get items
   */
  public function get_items_permissions_check( $request ) {
    return true; //<--use to make readable by all
    return current_user_can( 'activate_plugins' );
  }
 
}


add_action( 'plugins_loaded', 'wpaudit_dashboard' );
function wpaudit_dashboard(){
  $wpaudit_dashboard = new WPAudit_Dashboard();
}
      
class WPAudit_Dashboard {
  private $screen;
  private $active_tab;
  /**
   * Initializes the dashboard .
   */
  function __construct() {
    if (current_user_can('update_plugins')) {
      add_action('admin_menu', array( &$this,'register_menu') );
      add_action('current_screen', array($this,'init'));
    }
  }
  
  function register_menu() {
    $this->page = add_dashboard_page('WP Audit', 'WP Audit', 'read', 'wp-audit', array( &$this,'render_dashboard'));
  }
  
  function init(){
    if (current_user_can('update_plugins')) {
      add_action('load-'.$this->page,  array($this,'page_actions'),9);
      add_action('load-'.$this->page,  array($this,'screen_options'),9);
    }
  }
  
  function page_actions(){
    $this->screen = get_current_screen();
    $this->active_tab = (isset($_GET[ 'tab' ]))?$_GET[ 'tab' ]:'environment';
  }
  
  function screen_options(){
    add_screen_option( 'layout_columns', array('max' => 2, 'default' => 2 ) );
  }

  function render_dashboard() {
    $this->get_dashboard_header();
    $this->get_dashboard_tab($this->active_tab);
    $this->get_dashboard_footer();
  }
    
  function get_dashboard_header(){
    include_once("admin/header.php");
  }
  
  function get_dashboard_tab($tab){
    include_once("admin/tabs/".$this->active_tab.".php");
  }
  
  function get_dashboard_footer(){
    include_once("admin/footer.php");
  }
  
  
  
  
}


class WPAudit_Helpers {
  function folderInfo($section, $base_path, $paths){
    $data = array();
    foreach($paths as $path){
      if(is_dir($base_path."/".$path)){
        $data[$section][$path] = WPAudit_Helpers::folderSize($base_path."/".$path);
      }
    }
    return $data;
  }
  
  function folderSize($path){
    $size = 0;
    
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($path)
    );
    
    foreach ($iterator as $file) {
        $size += $file->getSize();
    }
    
    return WPAudit_Helpers::formatBytes($size);
  } 
  
  function formatBytes($bytes) {
      $i = floor(log($bytes, 1024));
      return round($bytes / pow(1024, $i), [0,0,2,2,3][$i]).['B','kB','MB','GB','TB'][$i];
  }
  function get_php_curl() {
    return function_exists('curl_init') ? esc_html__('Enabled', 'wp-admin') : esc_html__('Disabled', 'wp-admin');
    
  }
  
  function get_php_loaded_extensions() {
    return sanitize_text_field(implode(', ', get_loaded_extensions()));
  }
}
