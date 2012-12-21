<h1> Vos coordonnées </h1>
<?php include_partial('global/form_errors',array('form' => $form)) ?>
<?php echo $form->renderFormTag(url_for('cart/order'), array('id' => 'contact-form', 'autocomplete' => 'on')) ?>
  <?php include_partial('global/register',array('form' => $form)) ?>
  <p class="submit"><input type="submit" name="submit" value="<?php echo __('Order') ?>" /></p>
</form>
<script type="text/javascript"><!--
  $(document).ready(function(){
    $('#contact-form .field').click(function(){
      $(this).find('input, textarea, select').first().focus();
    });
  });
--></script>
