     
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
      
