<div class="sf_admin_list ui-grid-table ui-widget ui-corner-all ui-helper-reset ui-helper-clearfix">
  <table>
    <caption class="fg-toolbar ui-widget-header ui-corner-top">
      <h2><span class="ui-icon ui-icon-triangle-1-s"></span> <?php echo __('Price manifestation List', array(), 'messages') ?></h2>
    </caption>

    <thead class="ui-widget-header">
      <tr>
        <?php include_partial('price_manifestation/batch_edit_th_tabular', array('sort' => $sort)) ?>
        <th id="sf_admin_list_th_actions" class="ui-state-default ui-th-column"><?php echo __('Actions', array(), 'sf_admin') ?></th>
      </tr>
    </thead>

  <?php if (!$pager->getNbResults()): ?>

    <tbody>
      <tr class="sf_admin_row ui-widget-content sf_admin_new">
        <?php include_partial('price_manifestation/batch_edit_td_new', array()) ?>
        <td></td>
      </tr>
    </tbody>

  <?php else: ?>

    <tfoot>
      <tr>
        <th colspan="5">
          <div class="ui-state-default ui-th-column ui-corner-bottom">
            <?php include_partial('price_manifestation/pagination', array('pager' => $pager)) ?>
          </div>
        </th>
      </tr>
    </tfoot>

    <tbody>
      <?php foreach ($pager->getResults() as $i => $price_manifestation): $odd = fmod(++$i, 2) ? ' odd' : '' ?>
        <tr class="sf_admin_row ui-widget-content <?php echo $odd ?>">
          <?php include_partial('price_manifestation/batch_edit_td_tabular', array('price_manifestation' => $price_manifestation)) ?>
          <?php include_partial('price_manifestation/batch_edit_td_actions', array('price_manifestation' => $price_manifestation, 'helper' => $helper)) ?>
        </tr>
      <?php endforeach; ?>
        <tr class="sf_admin_row ui-widget-content sf_admin_new">
          <?php include_partial('price_manifestation/batch_edit_td_new', array()) ?>
          <td></td>
        </tr>
    </tbody>

  <?php endif; ?>
  </table>
  <span style="display: none" class="_delete_csrf_token"><?php $f = new BaseForm(); echo $f->getCSRFToken() ?></span>
</div>

<script type="text/javascript">
/* <![CDATA[ */
function checkAll()
{
  var boxes = document.getElementsByTagName('input'); for(var index = 0; index < boxes.length; index++) { box = boxes[index]; if (box.type == 'checkbox' && box.className == 'sf_admin_batch_checkbox') box.checked = document.getElementById('sf_admin_list_batch_checkbox').checked } return true;
}
/* ]]> */
</script>
