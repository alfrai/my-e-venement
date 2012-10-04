<?php use_helper('Date','Number') ?>
<script type="text/javascript" language="javascript">
  print();
  //close();
</script>
<?php if ( isset($modifiable) && $modifiable ): ?>
<script type="text/javascript">
$(document).ready(function(){
  $('form.inline-modifications').submit(function() {
    return false;
  });
  $('form.inline-modifications button').click(function() {
    $('.inline-modifiable').each(function() {
      if ( $(this).find('input').length == 0 )
        $(this).html($('<input type="text" value="" name="inline-modifiable" />').val($(this).html()));
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
    
    // on the current line
    $('#lines tbody .qty input').change(function(){
      $(this).closest('tr').find('.pit').html(
        (parseFloat($(this).closest('tr').find('.up').html()) * parseInt($(this).val())).toFixed(2)
        + '&nbsp;€'
      );
      $(this).closest('tr').find('.tep').html(
        (parseFloat($(this).closest('tr').find('.pit').html()) / (1 + parseFloat($(this).closest('tr').find('.vat .percent').html()) / 100)).toFixed(2)
        + '&nbsp;€'
      );
      $(this).closest('tr').find('.vat .value').html(
        (parseFloat($(this).closest('tr').find('.pit').html()) - parseFloat($(this).closest('tr').find('.tep').html())).toFixed(2)
        + '&nbsp;€'
      );
      
      // on totals
      $('#totals .vat:not(:first)').remove();
      $('#totals .vat span:first').html($('#lines thead .vat span').html()+':');
      $('#totals .vat .float').html('0&nbsp;€');
      $('#lines tbody .vat .value').each(function(){
        $('#totals .vat .float').html(
          (parseFloat($('#totals .vat .float').html()) + parseFloat($(this).html())).toFixed(2) + '&nbsp;€'
        );
      });
      $('#totals .tep .float').html('0&nbsp;€');
      $('#lines tbody .tep').each(function(){
        $('#totals .tep .float').html(
          (parseFloat($('#totals .tep .float').html()) + parseFloat($(this).html())).toFixed(2) + '&nbsp;€'
        );
      });
      $('#totals .pit .float').html('0&nbsp;€');
      $('#lines tbody .pit').each(function(){
        $('#totals .pit .float').html(
          (parseFloat($('#totals .pit .float').html()) + parseFloat($(this).html())).toFixed(2) + '&nbsp;€'
        );
      });
    });
    return false;
  });
});
</script>

<form class="inline-modifications" method="post" action="<?php echo url_for('ticket/recordAccounting') ?>"><p><button name="inline-modification"><?php echo __('Modify on-the-fly') ?></button></p></form>
<?php endif ?>
