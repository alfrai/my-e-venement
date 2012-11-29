<?php use_helper('I18N', 'Date') ?>
<?php include_partial('assets') ?>

<div id="sf_admin_container">
  <?php include_partial('flashes') ?>

  <div id="sf_admin_content">
    <form action="<?php echo url_for('organism_collection', array('action' => 'batch')) ?>" method="post" id="sf_admin_content_form">
      <?php include_partial('group_list', array('pager' => $pager, 'sort' => $sort, 'helper' => $helper, 'group_id' => $group_id)) ?>
    </form>
  </div>

</div>
