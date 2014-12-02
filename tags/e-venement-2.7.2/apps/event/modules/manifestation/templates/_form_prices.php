<?php use_javascript('form-list') ?>
<?php use_stylesheet('form-list') ?>
<div class="sf_admin_form_row sf_admin_text sf_admin_form_field_prices_list">
  <div class="label ui-helper-clearfix">
    <label for="manifestation_prices"><?php echo __('Prices list') ?></label>
  </div>
  <div id="form_prices" class="sf_admin_form_list ajax">
    <script type="text/javascript">
      document.getElementById('form_prices').url   = '<?php echo url_for('price_manifestation/batchEdit?id='.$form->getObject()->id) ?>';
      document.getElementById('form_prices').field = '.sf_admin_form_field_value';
      document.getElementById('form_prices').wait_msg = "<?php echo __('Click to validate your modifications',null,'sf_admin') ?>";
    </script>
  </div>
</div>
