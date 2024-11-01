<?php
  global $wpaudit;
  wp_register_style('wpaudit-css',plugin_dir_url( __FILE__ ).'css/style.css');
  wp_enqueue_style('wpaudit-css');
  wp_enqueue_style('font-awesome', '//use.fontawesome.com/releases/v5.0.12/css/all.css');
  wp_enqueue_script('handlebars-core', plugin_dir_url( __FILE__ ).'js/handlebars-2.0.0.min.js' );
  wp_enqueue_script('handlebars-helpers', plugin_dir_url( __FILE__ ).'js/handlebars-helpers.js' );
  wp_enqueue_script('tablesorter', plugin_dir_url( __FILE__ ).'js/slimtable.js' );
?>
<script>
  jQuery(function($) {
    $.ajaxSetup({
      headers : {
        "X-WP-Nonce" : "<?php echo wp_create_nonce('wp_rest');?>"
      }
    });
  });
</script>

<div id='audit-dashboard' class='wrap'>
  <h1><strong><?php echo get_bloginfo( 'name' );?></strong> Site Audit</h1>
  <!--<div class='description'><small><strong><?php echo get_bloginfo('url');?></strong> | <?php echo date("F d, Y");?></small></div>-->
  <!--<hr class='wp-header-end' style='margin-bottom:1em;'>-->
  
  <?php echo settings_errors(); ?>
  <h2 class='nav-tab-wrapper disable-selection'>
  <?php
    foreach($wpaudit['tabs'] as $tab){
      $active = ($this->active_tab == $tab)?"nav-tab-active":"";
      echo "<a href='?page=wp-audit&tab=$tab' class='nav-tab $active'>".ucwords($tab)."</a>";
    }
  ?>
  </h2>
  <div id="audit-widgets" class=""> 
    <section id="tab_content" class="x-container">  
      <script id="<?php echo $this->active_tab; ?>" type="text/x-handlebars-template">
        <?php // Audit information and layout go here ?>
        
        <div id="dashboard-widgets-wrap">
          <div id="dashboard-widgets" class="metabox-holder">
            <div id="postbox-container-1" class="postbox-container">
              <div id="normal-sortables" class="">
      
       
