<style type="text/css">
<!--
.lth {border: 1px solid rgb(0,0,0); border-width: 1px 0px; text-align: left; background-color: rgb(180,180,180);}
.ltd {/* border-bottom: 1px solid rgb(150,150,150); */}
-->
</style>

<!-- BEGIN table -->
<h3>{table.TITLE}</h3>
<table cellspacing="0" cellpadding="2" border="0" width="100%">
  <!-- BEGIN header -->
  <tr>
  <!-- BEGIN column -->
    <th class="lth">{table.header.column.TITLE}</th>
  <!-- END column -->
  </tr>
  <!-- END header -->
  <!-- BEGIN row -->
  <tr>
  <!-- BEGIN cell -->
    <td class="ltd">
    <!-- BEGIN url -->
    <a href="{table.row.cell.url.URL}">
    <!-- END url -->
    {table.row.cell.VALUE}
    <!-- BEGIN url -->
    </a>
    <!-- END url -->
    </td>
  <!-- END cell -->
  </tr>
  <!-- END row -->
</table>
<!-- END table -->
