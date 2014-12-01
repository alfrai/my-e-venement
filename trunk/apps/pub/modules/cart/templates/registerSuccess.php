<?php include_partial('global/ariane',array('active' => 3)) ?>
<?php include_partial('global/oplog') ?>
<h1><?php echo __('Your contact') ?> :</h1>
<?php include_partial('contact/edit_header') ?>
<?php //include_partial('global/form_errors',array('form' => $form)) ?>
<?php echo $form->renderFormTag(url_for('cart/order'.($specific_transaction ? '?transaction_id='.$specific_transaction->id : '')), array('id' => 'contact-form', 'autocomplete' => 'on')) ?>
  <?php include_partial('global/register',array('form' => $form)) ?>
  <?php if ( sfConfig::get('app_texts_terms_conditions') ): ?>
  <p class="terms_conditions field error">
    <input id="terms_conditions" type="checkbox" name="terms_conditions" value="accepted" />
    <label for="terms_conditions"><?php echo sfConfig::get('app_texts_terms_conditions') ?></label>
  </p>
  <?php endif ?>
  <p class="submit"><input type="submit" name="submit" value="<?php echo __('Order') ?>" /></p>
</form>

<script type="text/javascript"><!--
  $(document).ready(function(){
    $('#contact-form .field').click(function(){
      $(this).find('input, textarea, select').first().focus();
    });
  });
--></script>
