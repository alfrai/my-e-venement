<?php include_partial('global/flashes') ?>
<?php echo $form->renderFormTag(url_for('login/validate'), array('autocomplete' => 'on', 'id' => 'login', )) ?>
  <?php include_partial('login',array('form' => $form, )) ?>
</form>
<script type="text/javascript"><!--
  $(document).ready(function(){
    $('input[type=text]').first().focus();
  });
--></script>
