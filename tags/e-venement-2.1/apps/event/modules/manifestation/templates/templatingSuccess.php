<?php include_partial('assets') ?>

<div class="ui-widget-content ui-corner-all sf_admin_templating" id="sf_admin_container">
  <div class="fg-toolbar ui-widget-header ui-corner-all action">
    <h1><?php echo __('Templating prices') ?></h1>
  </div>
  
  <?php include_partial('manifestation/flashes') ?>
  
  <?php echo $form->renderFormTag(url_for('manifestation/templating')) ?>
    <?php echo $form->renderHiddenFields(); ?>
    <table>
      <?php echo $form; ?>
      <tr><td></td><td><input type="submit" name="submit" value="<?php echo __('Apply') ?>" /></td></tr>
    </table>
  </form>
</div>
