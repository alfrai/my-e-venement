<?php use_helper('I18N', 'Date') ?>
<?php include_partial('default/assets') ?>

<div id="sf_admin_container">
  <?php include_partial('default/flashes') ?>

  <div id="sf_admin_content">
  <div class="sf_admin_list ui-grid-table ui-widget ui-corner-all ui-helper-reset ui-helper-clearfix">
  <table>
    <caption class="fg-toolbar ui-widget-header ui-corner-top">
      <h1><span class="ui-icon ui-icon-triangle-1-s"></span> <?php echo __('Welcome on e-venement', array(), 'messages') ?></h1>
    </caption>
    <tbody>
      <tr class="sf_admin_row ui-widget-content">
        <td align="center" height="30">
          <p align="center">blabla</p>
        </td>
      </tr>
    </tbody>
  </table>
  </div>
  </div>

  <div id="sf_admin_footer">
    <?php include_partial('default/list_footer') ?>
  </div>

  <?php include_partial('default/themeswitcher') ?>
</div>
