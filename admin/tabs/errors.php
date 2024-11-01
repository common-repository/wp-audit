
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
