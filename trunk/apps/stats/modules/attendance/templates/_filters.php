<?php use_javascript('/sfAdminThemejRollerPlugin/js/fg.menu.js') ?>
<?php use_javascript('/sfAdminThemejRollerPlugin/js/ui.selectmenu.js') ?>
<div id="sf_admin_filter">
  <div aria-labelledby="ui-dialog-title-sf_admin_filter" role="dialog" tabindex="-1" class="ui-dialog ui-widget ui-widget-content ui-corner-all ui-draggable ui-resizable">
    <div unselectable="on" class="ui-dialog-titlebar ui-widget-header ui-corner-all ui-helper-clearfix">
      <span unselectable="on" id="ui-dialog-title-sf_admin_filter" class="ui-dialog-title">Filters</span>
      <a unselectable="on" role="button" class="ui-dialog-titlebar-close ui-corner-all" href="#">
        <span unselectable="on" class="ui-icon ui-icon-closethick">close</span>
      </a>
    </div>
    <?php echo $form->renderFormTag('',array('id'=>'criterias')) ?>
    <?php include_partial('attendance/criterias',array('form' => $form)) ?>
    <div class="ui-dialog-buttonpane ui-widget-content ui-helper-clearfix">
      <button class="ui-state-default ui-corner-all" type="button" name="filter"><?php echo __('Filter',array(),'sf_admin') ?></button>
    </div>
    </form>
  </div>
</div>
