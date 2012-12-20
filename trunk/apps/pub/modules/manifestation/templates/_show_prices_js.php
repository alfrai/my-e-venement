<?php use_javascript('jquery') ?>
<script type="text/javascript"><!--
  $(document).ready(function(){
    $('.prices .quantity select').change(function(){
      // hiding options to limit the global qty to the max value
      selects = $(this).closest('.gauge').find('.quantity select');
      var max_qty = 0;
      selects.each(function(){
        if ( parseInt($(this).find('option:last-child').val()) > max_qty )
          max_qty = parseInt($(this).find('option:last-child').val());
      });
      for ( quantities = i = 0 ; i < selects.length ; i++ )
        quantities += parseInt($(selects[i]).val());
      options = selects.find('option');
      options.show();
      for ( i = 0 ; i < options.length ; i++ )
      if ( parseInt($(options[i]).val()) > max_qty - quantities + parseInt($(options[i]).closest('select').val()) )
        $(options[i]).hide();
      
      // calculating totals by line
      val = parseFloat($(this).closest('tr').find('.value').html().replace(',','.')) * parseInt($(this).val());
      currency = $(this).closest('tr').find('.value').html().replace(/^.*(&nbsp;.*)/,'$1');
      txt = val.toFixed(2) + currency;
      $(this).closest('tr').find('.total').html(txt);
      
      // calculating the global total
      val = 0;
      totals = $(this).closest('tbody').find('.total');
      for ( i = 0 ; i < totals.length ; i++ )
        val += parseFloat($(totals[i]).html().replace(',','.'));
      $(this).closest('.prices').find('tfoot .total').html(val.toFixed(2) + currency)
    }).change();
  });
--></script>
