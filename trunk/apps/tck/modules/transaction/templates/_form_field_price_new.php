<?php echo $form->renderFormTag(url_for('transaction/complete?id='.$transaction->id), array(
  'method' => 'get',
  'target' => '_blank',
  'autocomplete' => false,
)) ?><p>
  <?php echo $form->renderHiddenFields() ?>
  <?php echo $form['qty'] ?>
</p>
<script type="text/javascript">
  $(document).ready(function(){
    var form = $('#li_transaction_field_price_new form');
    $('#li_transaction_field_price_new').click(function(){
      $(this).find('input[type=text]').focus();
    });
    
    $('#li_transaction_field_content .highlight').focusin(function(){
      form.find('button').remove();
      var infos = $.parseJSON($(this).find('.infos').text());
      $.each(infos.available_prices, function(i, price){
        $('<button name="price_new[id]"></button>')
          .val(price.id)
          .html(price.name)
          .prop('title', price.value+' - '+price.description)
          .attr('data-gauge-id', infos.id)
          .appendTo(form.find('p'))
          .click(function(){
            $(this).closest('form').find('[name="transaction[price_new][price_id]"]').val($(this).val());
            $(this).closest('form').find('[name="transaction[price_new][gauge_id]"]').val($(this).attr('data-gauge-id'));
          })
        ;
      });
      
      $('#li_transaction_field_price_new').fadeIn();
    }).focusout(function(){
      setTimeout(function(){
        if ( $('#li_transaction_field_content .ui-state-highlight').length == 0 )
          $('#li_transaction_field_price_new').fadeOut();
      },100);
    });
  });
</script>
</form>
