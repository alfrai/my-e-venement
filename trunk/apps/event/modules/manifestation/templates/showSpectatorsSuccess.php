<?php use_helper('I18N', 'Date') ?>
<?php include_partial('manifestation/assets') ?>

<div id="sf_admin_container" class="sf_admin_show ui-widget ui-widget-content ui-corner-all">

  <div id="sf_fieldset_spectators">
    <?php include_partial('show_spectators_list', array('form' => $form, 'spectators' => $spectators, 'configuration' => $configuration)) ?>
  </div>

</div>
