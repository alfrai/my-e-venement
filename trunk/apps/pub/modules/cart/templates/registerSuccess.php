<?php include_partial('global/ariane',array('active' => 3)) ?>
<h1><?php echo __('Your contact') ?> :</h1>
<?php include_partial('contact/edit_header') ?>
<?php include_partial('global/form_errors',array('form' => $form)) ?>
<?php echo $form->renderFormTag(url_for('cart/order'), array('id' => 'contact-form', 'autocomplete' => 'on')) ?>
  <?php include_partial('global/register',array('form' => $form)) ?>
  <p class="submit"><input type="submit" name="submit" value="<?php echo __('Order') ?>" /></p>
</form>

<?php if ( !$sf_user->hasContact() ): ?>
  <div id="oplog"><?php echo link_to(__('You already have an account ? Login first...'),'login/index?register=true') ?></div>
<?php endif ?>

<script type="text/javascript"><!--
  $(document).ready(function(){
    $('#contact-form .field').click(function(){
      $(this).find('input, textarea, select').first().focus();
    });
  });
--></script>
