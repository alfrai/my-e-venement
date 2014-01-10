<?php include_partial('global/flashes') ?>
<?php include_partial('global/ariane',array('active' => 0)) ?>
<?php echo $form->renderFormTag(url_for('login/send'), array('autocomplete' => 'on', 'id' => 'login', )) ?>
  <?php include_partial('forgot',array('form' => $form, )) ?>
</form>

<script type="text/javascript"><!--
  $(document).ready(function(){
    $('input[type=text]').first().focus();
  });
--></script>
