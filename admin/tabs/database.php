
<div class="postbox">
  <h2 class="hndle">Summary</h2>
  <div class="inside">
      {{Server}} version {{Server_Version}} with an uptime of {{duration Stat.Uptime}}<br>
      Total Storage: {{filesize Database_Size}} in {{Table_Count}} tables.

  </div>
</div>


<div class="postbox">
  <h2 class="hndle">Details</h2>
  <div class="inside">
      <table class="wp-list-table widefat striped">
        <thead>
          <th class="manage-column">Table Name</th>
          <th class="manage-column">Rows</th>
          <th class="manage-column">Data Size</th>
          <th class="manage-column">Index Size</th>
          <th class="manage-column">Unused Space</th>
          <th class="manage-column">Total Size</th>
          <th class="manage-column">Bytes</th>
        </thead>
        <tbody>
          {{#each Tables}}
          <tr>
            <td class="column-primary">{{Table}}</td>
            <td>{{Row_Count}}</td>
            <td>{{filesize Data_Size}}</td>
            <td>{{filesize Index_Size}}</td>
            <td>{{filesize Data_Free}}</td>
            <td>{{filesize Total_Size}}</td>
            <td>{{Total_Size}}</td>
          </tr>
          {{/each}}
        </tbody>
      </table>
  </div>
</div>
