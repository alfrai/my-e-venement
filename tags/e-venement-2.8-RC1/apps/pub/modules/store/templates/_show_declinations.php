<table><tbody>
  <tr>
    <td class="informations">
      <?php include_partial('show_informations', array('product' => $product)) ?>
      <?php include_partial('global/show_links', array('objects' => $product)) ?>
      <div class="clear"></div>
    </td>
    <td class="declinations">
      <?php foreach ( $declinations as $declination ): ?>
        <?php include_partial('show_declination', array('declination' => $declination)) ?>
      <?php endforeach ?>
    </td>
  </tr>
</tbody></table>
<?php use_javascript('pub-totals?'.date('Ymd')) ?>
