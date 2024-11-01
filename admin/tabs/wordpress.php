
    
      <div class="x-widget-wrap">
        <div class="x-widget">
          <div class="x-title-wrap">
            <i class="fa fa-server"></i>
            <div class="x-title">Base Info</div>
          </div>
          <div class="x-widget-inner">
            <div>
              Running WordPress version {{Installed_Version}} with {{Secure_Connection}}.
            </div>
              <div>
                Multisite: {{Multisite}}<br>
                Sites:
              <table>
                <thead><th style="width:70%;">Domain</th><th style="width:30%;">Site ID</th></thead>
                <tbody>
                  {{#each Sites}}
                  <tr><td>{{this.Domain}}</td><td>{{this.Site_ID}}</td></tr>
                  {{/each}}
                </tbody>
              </table>
            </div>
              
          </div>
        </div>
      </div>
      
      
    