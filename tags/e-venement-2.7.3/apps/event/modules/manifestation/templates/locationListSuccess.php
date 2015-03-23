<?php use_helper('I18N', 'Date') ?>
<?php include_partial('assets') ?>

<div id="sf_admin_container">
  <?php include_partial('flashes') ?>

  <div id="sf_admin_content">
    <form action="<?php echo url_for('manifestation_collection', array('action' => 'batch')) ?>" method="post" id="sf_admin_content_form">
      <?php include_partial('manifestation/location_list', array('pager' => $pager, 'helper' => $helper, 'location_id' => $location_id,)) ?>
    </form>
  </div>

</div>
