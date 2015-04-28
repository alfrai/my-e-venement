<?php include_partial('assets') ?>
<div id="sf_admin_container" class="sf_admin_edit ui-widget ui-widget-content ui-corner-all">
  <?php include_partial('import_title', array('event' => $event)) ?>
  <?php include_partial('flashes') ?>
  
  <?php echo $importForm->renderFormTag(url_for('event/import')) ?>
    <?php include_partial('import_actions', array('event' => $event)) ?>
    <div class="ui-helper-clearfix"></div>
    
    <div class="ui-widget ui-widget-content ui-corner-all import-ics">
      <?php include_partial('import_form', array('event' => $event, 'importForm' => $importForm,)) ?>
    </div>
    
    <?php include_partial('import_actions', array('event' => $event)) ?>
    <div class="ui-helper-clearfix"></div>
  </form>
</div>
