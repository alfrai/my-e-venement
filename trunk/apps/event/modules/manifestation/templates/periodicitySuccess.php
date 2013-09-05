<?php include_partial('manifestation/assets') ?>
<?php include_partial('global/flashes') ?>
<?php use_helper('I18N') ?>

<div id="sf_admin_container" class="periodicity sf_admin_edit ui-widget ui-widget-content ui-corner-all">
  <div class="fg-toolbar ui-widget-header ui-corner-all">
    <h1><?php echo __('Periodicity for %%manifestation%%',array('%%manifestation%%' => $manifestation)) ?></h1>
  </div>
  <p class="back"><?php echo link_to(
      '<span class="ui-icon ui-icon-arrowreturnthick-1-w"></span>'.__('Back',null,'sf_admin'),
      'manifestation/edit?id='.$manifestation->id,
      array('class' => 'fg-button-mini fg-button ui-state-default fg-button-icon-left')
  ) ?></p>
  <?php echo $form->renderFormTag(url_for('manifestation/periodicity')) ?>
    <?php include_partial('periodicity_behaviour') ?>
    <?php include_partial('periodicity_repeat') ?>
    <?php include_partial('periodicity_reservation_mods') ?>
    <?php include_partial('periodicity_submit',array('form' => $form, 'manifestation' => $manifestation,)) ?>
  </form>
</div>
