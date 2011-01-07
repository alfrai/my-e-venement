<?php use_helper('I18N', 'Date') ?>
<?php include_partial('default/assets') ?>

<div id="sf_admin_container">
  <?php include_partial('default/flashes') ?>
  
  <div class="ui-dialog ui-widget ui-widget-content ui-corner-all  ui-draggable ui-resizable">
    <div class="ui-dialog-titlebar ui-widget-header ui-corner-all ui-helper-clearfix">
      <span id="ui-dialog-title-sf_admin_filter" class="ui-dialog-title"><?php echo __('Search for ...', array(), 'messages') ?></span>
      <a style="-moz-user-select: none;" unselectable="on" role="button" class="ui-dialog-titlebar-close ui-corner-all" href="#"><span style="-moz-user-select: none;" unselectable="on" class="ui-icon ui-icon-closethick">close</span></a>
    </div>
    <div style="height: auto; width: auto" class="sf_admin_filter ui-helper-reset ui-helper-clearfix ui-dialog-content ui-widget-content" id="sf_admin_filter">
      <form action="" method="post"><table>
        <tfoot>
          <tr>
            <td colspan="2">
              <div style="text-align: right;">
                <input name="search[_csrf_token]" value="<?php echo $form->getCSRFToken() ?>" id="search__csrf_token" type="hidden">
                <a class="fg-button ui-state-default ui-corner-all" id="search_reset" onclick="javascript: $('form').get(0).reset()" href="#"><?php echo __('Reset') ?></a>
                <a class="fg-button ui-state-default ui-corner-all" id="search_submit" onclick="javascript: $('form').submit()" href="#"><?php echo __('Search') ?></a>
              </div>
            </td>
          </tr>
        </tfoot>
        <tbody>
          <?php echo $form->render(); ?>
        </tbody>
      </table></form>
    </div>
  </div>

  <div id="sf_admin_footer">
    <?php //include_partial('search/search_footer') ?>
  </div>

  <?php include_partial('default/themeswitcher') ?>
</div>
