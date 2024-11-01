              </div>
            </div>
            

            <!-- Tools Panel -->
            <div id="postbox-container-3" class="postbox-container">
              <div id="column3-sortables" class="tools">
                <div class="postbox"> 
                    <h2 class="hndle">Tools</h2>
                  <div class="inside">
                  <?php //include_once(realpath(dirname(__FILE__))."/tabs/$this->active_tab-tools.php");?>
                  <?php include_once(realpath(dirname(__FILE__))."/tabs/tools.php");?>
                  </div>
                </div>
              </div>
            </div>


          </div>
        </div>
        
    
      </script>
    </section>
  </div> <!-- #dashboard-widgets -->
</div> <?php // Dashboard wrapper ?>


<div class="ajax_loading"></div>
<script>
  jQuery(function($) {        
    $('.nav-tab').click(function(){
      $('.ajax_loading').show();   
    });
    setTimeout(function(){
      $('.ajax_loading').hide(); 
    }, 10000); // hide overlay after 10 seconds

    var template = jQuery('#<?php echo $this->active_tab;?>').html();
    var templateScript = Handlebars.compile(template);
    var html = '';
    
    $.getJSON("<?php echo esc_url_raw(rest_url());?>wp-audit/<?php echo $this->active_tab;?>/?noalert", function(data) {
      html = templateScript(data);
    }).fail(function(){
      html = templateScript();
      $('#tab_content').html(html);
    }).always(function(){
      $('#tab_content').html(html);
      $('.ajax_loading').hide();
      $('table').slimtable();
    });
  });
 
</script>