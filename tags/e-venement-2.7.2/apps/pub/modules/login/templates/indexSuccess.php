<?php include_partial('global/flashes') ?>
<?php include_partial('global/fake_flashes', array(
  'notice' => __('This website requires your authentication'),
)) ?>
<?php include_partial('global/ariane',array('active' => 0)) ?>
<?php echo $form->renderFormTag(url_for('login/validate'.(isset($register) && $register ? '?register=true' : '')), array('autocomplete' => 'on', 'id' => 'login', )) ?>
  <?php include_partial('login',array('form' => $form, )) ?>
</form>

<script type="text/javascript"><!--
  $(document).ready(function(){
    $('input[type=text]').first().focus();
  });
--></script>
