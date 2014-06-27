<script type="text/javascript">
  $(document).ready(function(){
    $('form input[name=ticket_id]').focus();
    $('form.cancel').submit(function(){
      if ( confirm("<?php echo __("Are you sure?",null,'sf_admin') ?> - "+$('input[type=text]').val()) )
        return true;
      else
      {
        $('#transition .close').click();
        return false;
      }
    });
    $('form.batch').submit(function(){
      if ( confirm("<?php echo __("Are you sure? You are going to replace all your payments in the original and (if it exists) cancelling transactions...") ?>") )
        return true;
      else
      {
        $('#transition .close').click();
        return false;
      }
    });
    setTimeout(function(){
      $('.sf_admin_flashes').fadeOut();
    },4000);
  });
</script>
