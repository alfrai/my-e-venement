<div class="sf_admin_list ui-grid-table ui-widget ui-corner-all ui-helper-reset ui-helper-clearfix">
  <table>
    <caption class="fg-toolbar ui-widget-header ui-corner-top">
      <h2><span class="ui-icon ui-icon-triangle-1-s"></span> <?php echo __('Checkpoints list', array(), 'messages') ?></h2>
    </caption>

    <thead class="ui-widget-header">
      <tr>
        <?php include_partial('batch_edit_th_tabular', array('sort' => $sort)) ?>
        <th id="sf_admin_list_th_actions" class="ui-state-default ui-th-column"><?php echo __('Actions', array(), 'sf_admin') ?></th>
      </tr>
    </thead>

  <?php if (!$pager->getNbResults()): ?>

    <tbody>
      <tr class="sf_admin_row ui-widget-content sf_admin_new">
        <td colspan="3"><?php echo __('No result') ?></td>
        <td></td>
      </tr>
    </tbody>

  <?php else: ?>

    <tfoot>
      <tr>
        <th colspan="5">
          <div class="ui-state-default ui-th-column ui-corner-bottom">
            <?php include_partial('batch_pagination', array('pager' => $pager)) ?>
          </div>
        </th>
      </tr>
    </tfoot>

    <tbody>
      <?php foreach ($pager->getResults() as $i => $checkpoint): $odd = fmod(++$i, 2) ? ' odd' : '' ?>
        <tr class="sf_admin_row ui-widget-content <?php echo $odd ?>">
          <?php include_partial('batch_edit_td_tabular', array('checkpoint' => $checkpoint)) ?>
          <?php include_partial('batch_edit_td_actions', array('checkpoint' => $checkpoint, 'helper' => $helper)) ?>
        </tr>
      <?php endforeach; ?>
    </tbody>

  <?php endif; ?>
  </table>
  <span style="display: none" class="_delete_csrf_token"><?php $f = new BaseForm(); echo $f->getCSRFToken() ?></span>
</div>
