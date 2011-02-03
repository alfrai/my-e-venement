<?php use_helper('I18N', 'Date') ?>
<?php include_partial('price_manifestation/assets') ?>

<div id="sf_admin_container">
  <?php include_partial('price_manifestation/flashes') ?>

  <div id="sf_admin_header">
    <?php include_partial('price_manifestation/list_header', array('pager' => $pager)) ?>
  </div>

  <div id="sf_admin_content">
    <?php include_partial('price_manifestation/batch_edit', array('pager' => $pager, 'sort' => $sort, 'helper' => $helper, 'hasFilters' => $hasFilters)) ?>
  </div>

  <div id="sf_admin_footer">
    <?php include_partial('price_manifestation/list_footer', array('pager' => $pager)) ?>
  </div>

  <?php include_partial('price_manifestation/themeswitcher') ?>
</div>
