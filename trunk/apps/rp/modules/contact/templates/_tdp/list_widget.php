<form id="tdp-content" class="tdp-container list" action="<?php echo url_for('contact_collection', array('action' => 'batch')) ?>" method="post">
  <?php include_partial('flashes') ?>
  <?php include_partial('list',array('pager' => $pager, 'sort' => $sort, 'helper' => $helper, 'hasFilters' => $hasFilters)) ?>
  <ul class="sf_admin_actions">
    <?php include_partial('contact/list_batch_actions', array('helper' => $helper)) ?>
  </ul>
</form>
