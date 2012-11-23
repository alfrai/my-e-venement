<div class="sf_admin_list ui-grid-table ui-widget ui-corner-all ui-helper-reset ui-helper-clearfix">
  <?php if (!$pager->getNbResults()): ?>

  <table>
    <caption class="fg-toolbar ui-widget-header ui-corner-top">
      <?php include_partial('list_caption',array('module' => 'organism')) ?>
    </caption>
    <tbody>
      <tr class="sf_admin_row ui-widget-content">
        <td align="center" height="30">
          <p align="center"><?php echo __('No result', array(), 'sf_admin') ?></p>
        </td>
      </tr>
    </tbody>
  </table>

  <?php else: ?>

  <table>
    <caption class="fg-toolbar ui-widget-header ui-corner-top">
      <?php include_partial('contact/list_caption',array('module' => 'organism')) ?>
    </caption>

    <thead class="ui-widget-header">
      <tr>
                  <th id="sf_admin_list_batch_actions"  class="ui-state-default ui-th-column"><input id="sf_admin_list_batch_checkbox" type="checkbox" onclick="checkAll();" /></th>
        
        <?php include_partial('organism/list_th_tabular', array('sort' => $sort)) ?>

                  <th id="sf_admin_list_th_actions" class="ui-state-default ui-th-column"><?php echo __('Actions', array(), 'sf_admin') ?></th>
              </tr>
    </thead>

    <tfoot>
      <tr>
        <th colspan="9">
          <div class="ui-state-default ui-th-column ui-corner-bottom">
            <?php include_partial('organism/pagination', array('pager' => $pager)) ?>
          </div>
        </th>
      </tr>
    </tfoot>

    <tbody>
      <?php foreach ($pager->getResults() as $i => $organism): $odd = fmod(++$i, 2) ? ' odd' : '' ?>
        <tr class="sf_admin_row ui-widget-content <?php echo $odd ?>">
                      <?php include_partial('organism/list_td_batch_actions', array('organism' => $organism, 'helper' => $helper)) ?>
          
          <?php include_partial('organism/list_td_tabular', array('organism' => $organism)) ?>

                      <?php include_partial('organism/list_td_actions', array('organism' => $organism, 'helper' => $helper)) ?>
                  </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <?php endif; ?>
</div>

<script type="text/javascript">
/* <![CDATA[ */
function checkAll()
{
  var boxes = document.getElementsByTagName('input'); for(var index = 0; index < boxes.length; index++) { box = boxes[index]; if (box.type == 'checkbox' && box.className == 'sf_admin_batch_checkbox') box.checked = document.getElementById('sf_admin_list_batch_checkbox').checked } return true;
}
/* ]]> */
</script>
