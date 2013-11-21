<?php use_helper('I18N', 'Date') ?>
<?php include_partial('global/assets') ?>

<div id="sf_admin_container">
  <?php include_partial('global/flashes') ?>

  <div id="sf_admin_content">
    <?php include_partial('gauge/batch_edit', array('pager' => $pager, 'sort' => $sort, 'helper' => $helper, 'hasFilters' => $hasFilters)) ?>
  </div>

</div>
