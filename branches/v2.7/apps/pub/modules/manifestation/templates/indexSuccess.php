<?php use_helper('I18N', 'Date') ?>
<?php include_partial('manifestation/assets') ?>

<div id="sf_admin_container">
  <h1><?php echo __('Dates list', array(), 'messages') ?></h1>

  <?php include_partial('global/flashes') ?>

  <div id="sf_admin_header">
    <?php include_partial('manifestation/list_header', array('filters' => $filters, 'pager' => $pager)) ?>
  </div>

  <div id="sf_admin_bar">
    <?php include_partial('manifestation/filters', array('form' => $filters, 'configuration' => $configuration)) ?>
  </div>

  <div id="sf_admin_content">
    <?php include_partial('manifestation/list', array('pager' => $pager, 'sort' => $sort, 'helper' => $helper)) ?>
    <ul class="sf_admin_actions">
      <?php include_partial('manifestation/list_batch_actions', array('helper' => $helper)) ?>
      <?php include_partial('manifestation/list_actions', array('helper' => $helper)) ?>
    </ul>
  </div>

  <div id="sf_admin_footer">
    <?php include_partial('manifestation/list_footer', array('pager' => $pager)) ?>
  </div>
</div>
