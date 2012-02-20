<?php use_helper('Date','Number') ?>
<?php if ( isset($modifiable) && $modifiable ): ?>
<script type="text/javascript" language="javascript">
  print();
  //close();
</script>
<script type="text/javascript">
$(document).ready(function(){
  $('form.inline-modifications').submit(function() {
    return false;
  });
  $('form.inline-modifications button').click(function() {
    $('.inline-modifiable').each(function() {
      if ( $(this).find('input').length == 0 )
        $(this).html('<input type="text" value="'+$(this).html()+'" name="inline-modifiable" />');
      else
      {
        $(this).html($(this).find('input').val());
        if ( $('[name="inline-modifiable"]').length == 0 )
        {
          $.post($('form.inline-modifications').attr('action'), {
            content: $('html').html(),
            invoice_id: $('#ids .invoice_id').html()
          });
        }
      }
    });
    return false;
  });
});
</script>

<form class="inline-modifications" method="post" action="<?php echo url_for('ticket/recordAccounting') ?>"><p><button name="inline-modification"><?php echo __('Modify on-the-fly') ?></button></p></form>
<?php endif ?>
