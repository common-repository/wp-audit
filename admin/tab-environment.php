<?php

    $args = array(
      'label' => __('Autoload data', 'wp_audit'),
      'default' => 10,
      'option' => 'wpad_autoload'
    );
    add_screen_option( 'wpad_autoload', $args );
    
?>

<section id="tab_content" class="x-container">  
  <script id="environment" type="text/x-handlebars-template">
    <?php // Audit information and layout go here ?>
    
    <div class="x-row"><!-- Begin Widget Row -->
      
        <!--<div class="x-widget-wrap">
        <div class="x-widget">
          <div class="x-title-wrap"> 
            <i class="fa fa-clipboard"></i>
            <div class="x-title {{Status}}">Environment Summary</div>
          </div>
          <div class="x-widget-inner">
            <div class="arrow-chart muted small">
              <section class="active high"></section>
              <section class=""></section>
              <section class=""></section>
              <section class=""></section>
              <section class=""></section>
              <section class=""></section>
            </div>
            Your overall environment score was {{Score}}... <br>[TODO: Include summary based on section metrics]
          </div>
        </div>
      </div>-->

      
      <div class="x-widget-wrap">
        <div class="x-widget">
          <div class="x-title-wrap">
            <i class="fa fa-server"></i>
            <div class="x-title">Web Server</div>
          </div>
          <div class="x-widget-inner">
              <div><h4>Platform</h4>{{Web.data.Server}} web server running {{Web.data.Language}} on {{Web.data.Platform}}<br>Document Root: {{Web.data.Document_Root}}</div>
              <div><h4>Memory</h4>{{Web.data.Memory_Current}} of {{Web.data.Memory_Limit}} RAM used.<br></div>
              <div>
                <h4>Machine Info</h4>
                External IP: {{Whois.data.ip_address}} 
              </div>
              <div>
                <h4>Connectivity</h4>
                Server Protocol is {{Web.data.Server_Protocol}} with HTTPS turned {{Web.data.HTTPS}}<br>
                cURL is {{Web.data.CURL}}
              </div>
              <div>
                <h4>Loaded Extensions</h4>
                {{Web.data.Extensions}}
              </div>
          </div>
        </div>
      </div>
      
      <div class="x-widget-wrap">
        <div class="x-widget">
          <div class="x-title-wrap"> 
            <i class="fa fa-database"></i>
            <div class="x-title">Database Server</div>
          </div>
          <div class="x-widget-inner">
              <div><h4>Platform</h4>{{Database.data.Server}} version {{Database.data.Server_Version}} with an uptime of {{duration Database.data.Stat.Uptime}}<br>
              Total Storage: {{filesize Database.data.Database_Size}} in {{Database.data.Table_Count}} tables.
              </div>
              <div>
              <table>
                <thead><th style="width:50%;">Table Name</th><th style="width:20%;">Rows</th><th style="width:30%;">Size</th></thead>
                <tbody>
                  {{#each Database.data.Tables}}
                  <tr><td>{{Table}}</td><td>{{Row_Count}}</td><td>{{filesize Data_Size}}</td></tr>
                  {{/each}}
                </tbody>
              </table>
              </div>
          </div>
        </div>
      </div>
    
      <div class="x-widget-wrap">
        <div class="x-widget">
          <div class="x-title-wrap"> 
            <i class="fa fa-hdd"></i>
            <div class="x-title">File Storage</div>
          </div>
          <div class="x-widget-inner">
            <div><h4>Summary</h4>Total Storage: {{Web.data.Storage.Total_Size}}</div>
            <div><h4>WP-Content</h4>
              <table>
                <thead><th style="width:70%;">Directory</th><th style="width:30%;">Size</th></thead>
                <tbody>
                  {{#each Web.data.Storage.WP-Content}}
                  <tr><td>{{@key}}</td><td>{{this}}</td></tr>
                  {{/each}}
                </tbody>
              </table>
            </div>
            <div><h4>Uploads</h4>
              <table>
                <thead><th style="width:70%;">Directory</th><th style="width:30%;">Size</th></thead>
                <tbody>
                  {{#each Web.data.Storage.Uploads}}
                  <tr><td>{{@key}}</td><td>{{this}}</td></tr>
                  {{/each}}
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
            
      <div class="x-widget-wrap">
        <div class="x-widget">
          <div class="x-title-wrap"> 
            <i class="fa fa-search"></i>
            <div class="x-title">WHOIS Information</div>
          </div>
          <div class="x-widget-inner">
            <div>
              <h4>Domain</h4>
              <span class="lower">{{Whois.data.raw.[Domain Name]}}</span> is registered with {{Whois.data.registrar}}. <br>
              Domain record was created on {{Whois.data.created}} and expires on {{Whois.data.expires}}
            </div>
          </div>
        </div>
      </div>
      
      <!--<div class="x-widget-wrap">
        <div class="x-widget">
          <div class="x-title-wrap"> 
            <i class="fa fa-chart-line"></i>
            <div class="x-title">Recommendations</div>
          </div>
          <div class="x-widget-inner">
            [TODO: Recommendation overview for each subsection]
          </div>
        </div>
      </div>-->
      
    </div><!-- End Widget Row -->
    
    <div class="x-wrapper">
      <div class="x-title-wrap">
        <div class="x-title">Error Log</div>
      </div>
      <div class="x-wrapper-inner">
        <?php $path = get_home_path(); if(file_exists($path.'error.log')){ ?>
        <pre>
          <?php echo file_get_contents($path.'error.log');?>
        </pre>
        
        <?php } ?>
        <!--
        <table class='audit-table wp-list-table widefat striped'>
          <tr><th></th><td></td></tr>
        </tbody></table>
        -->
      </div>
    </div>

  </script>
</section>
 
<script>  
  jQuery(function($) {
    var template = jQuery('#environment').html();
    var templateScript = Handlebars.compile(template);
    
    $.getJSON("<?php echo esc_url_raw(rest_url());?>wp-audit/environment/?noalert", function(data) {
      var html = templateScript(data);
      $('#tab_content').html(html);
      $('.ajax_loading').hide();
    });

    
  });
</script>