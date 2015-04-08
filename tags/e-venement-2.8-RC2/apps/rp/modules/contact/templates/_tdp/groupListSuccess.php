<?php use_helper('I18N', 'Date') ?>
<?php include_partial('contact/assets') ?>

<div id="sf_admin_container">
  <?php include_partial('contact/flashes') ?>

  <div id="sf_admin_content">
    <form action="<?php echo url_for('contact_collection', array('action' => 'batch')) ?>" method="post" id="sf_admin_content_form">
      <?php include_partial('contact/group_list', array('pager' => $pager, 'helper' => $helper, 'group_id' => $group_id)) ?>
    </form>
  </div>

</div>
