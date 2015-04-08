<?php include_partial('global/flashes') ?>
<?php include_partial('global/ariane',array('active' => 0)) ?>
<?php echo $form->renderFormTag(url_for('login/reset'), array('autocomplete' => 'off', 'id' => 'login', )) ?>
  <p><?php echo __('Be sure to keep this window opened, otherwise you will have to restart the entire processus of password recovery.') ?>
  <?php include_partial('recover',array('form' => $form, )) ?>
</form>

<script type="text/javascript"><!--
  $(document).ready(function(){
    $('input[type=text]').first().focus();
  });
--></script>
