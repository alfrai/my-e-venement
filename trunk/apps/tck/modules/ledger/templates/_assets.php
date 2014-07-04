  <?php use_javascript('/sfAdminThemejRollerPlugin/js/jquery.min.js', 'first') ?>
  <?php use_javascript('/sfAdminThemejRollerPlugin/js/jquery-ui.custom.min.js', 'first') ?>
  <?php use_stylesheet('/sfAdminThemejRollerPlugin/css/jquery/redmond/jquery-ui.custom.css') ?>
  <?php use_stylesheet('print-ledger','',array('media' => 'print')) ?>

<?php use_helper('Number','Date') ?>
<?php use_helper('CrossAppLink') ?>
<?php use_stylesheet('tck-ledger', '', array('media' => 'all')) ?>
<?php use_javascript('tck-ledger') ?>

<?php use_stylesheet('/sfFormExtraPlugin/css/jquery.autocompleter.css') ?>
<?php use_javascript('/sfFormExtraPlugin/js/jquery.autocompleter.js') ?>
<?php use_javascript('/cxFormExtraPlugin/js/cx_open_list.js') ?>


<?php // additionnal stylesheet (filament group)
  use_stylesheet('/sfAdminThemejRollerPlugin/css/fg.menu.css');
  use_stylesheet('/sfAdminThemejRollerPlugin/css/fg.buttons.css');
  use_stylesheet('/sfAdminThemejRollerPlugin/css/ui.selectmenu.css');
?>
<?php // additionnal javascript (filament group)
  use_javascript('/sfAdminThemejRollerPlugin/js/fg.menu.js');
  use_javascript('/sfAdminThemejRollerPlugin/js/jroller.js');
  use_javascript('/sfAdminThemejRollerPlugin/js/ui.selectmenu.js');
?>
