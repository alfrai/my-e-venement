<div class="sf_admin_list ui-grid-table ui-widget ui-corner-all ui-helper-reset ui-helper-clearfix">
  <table>
    <caption class="fg-toolbar ui-widget-header ui-corner-top">
      <h2><span class="ui-icon ui-icon-triangle-1-s"></span> <?php echo __('Workspaces list', array(), 'messages') ?></h2>
    </caption>

    <thead class="ui-widget-header">
      <tr>
        <?php include_partial('gauge/batch_edit_th_tabular', array('sort' => $sort)) ?>
        <th id="sf_admin_list_th_actions" class="ui-state-default ui-th-column"><?php echo __('Actions', array(), 'sf_admin') ?></th>
        <th id="sf_admin_list_th_chained" class="ui-state-default ui-th-column"><?php echo __('Chained', array(), 'messages') ?></th>
      </tr>
    </thead>

  <?php if (!$pager->getNbResults()): ?>

    <tbody>
      <tr class="sf_admin_row ui-widget-content sf_admin_new">
        <?php include_partial('gauge/batch_edit_td_new', array()) ?>
        <td></td>
      </tr>
    </tbody>

  <?php else: ?>

    <tfoot>
      <tr>
        <th colspan="5">
          <div class="ui-state-default ui-th-column ui-corner-bottom">
            <?php include_partial('gauge/pagination', array('pager' => $pager)) ?>
          </div>
        </th>
      </tr>
    </tfoot>

    <tbody>
      <?php foreach ($pager->getResults() as $i => $gauge): $odd = fmod(++$i, 2) ? ' odd' : '' ?>
        <tr class="sf_admin_row ui-widget-content <?php echo $odd ?>">
          <?php include_partial('gauge/batch_edit_td_tabular', array('gauge' => $gauge)) ?>
          <?php include_partial('gauge/batch_edit_td_actions', array('gauge' => $gauge, 'helper' => $helper)) ?>
          <?php if ( $i == 1 ): ?>
          <?php include_partial('gauge/batch_edit_td_rowspan', array('pager' => $pager)) ?>
          <?php endif ?>
        </tr>
      <?php endforeach; ?>
        <tr class="sf_admin_row ui-widget-content sf_admin_new">
          <?php include_partial('gauge/batch_edit_td_new', array()) ?>
          <td></td>
          <td></td>
        </tr>
    </tbody>

  <?php endif; ?>
  </table>
  <span style="display: none" class="_delete_csrf_token"><?php $f = new BaseForm(); echo $f->getCSRFToken() ?></span>
</div>
