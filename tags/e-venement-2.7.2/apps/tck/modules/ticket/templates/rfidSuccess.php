<?php use_javascript('ticket-rfid') ?>
<div class="ui-widget-content ui-corner-all sf_admin_edit" id="sf_admin_container">
  <div class="fg-toolbar ui-widget-header ui-corner-all">
    <h1><?php echo __('Linking tickets to specific codes') ?></h1>
    <p style="display: none;" id="global_transaction_id">90</p>
  </div>
  <div class="ui-corner-all ui-widget-content action" id="contact">
  <?php echo $form->renderFormTag(url_for('ticket/rfid')) ?>
    <?php echo $form->renderHiddenFields() ?>
    <table>
      <?php echo $form ?>
      </table>
    <p>
      <input type="submit" name="submit" value="ok" />
      <input type="checkbox" name="all" value="all" title="<?php echo __('Apply to all') ?>" />
    </p>
  </form>
</div>
