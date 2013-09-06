<script type="text/javascript"><!--
  $(document).ready(function(){
    $('#member_card_types tbody tr').change(function(){
      currency = $(this).find('.value').html().replace(/^.*(&nbsp;.*)/,'$1');
      $(this).find('.total').html((
        $(this).find('.qty input').val()
        *
        parseInt($(this).find('.value').html().replace(',','.'),10)
      ).toFixed(2) + currency);
      
      // totals
      $('#member_card_types tbody tr .total').each(function(){
        $('#member_card_types tfoot .total').html((
          parseInt($('#member_card_types tfoot .total').html().replace(',','.'),10)
          +
          parseInt($(this).html().replace(',','.'),10)
        ).toFixed(2) + currency);
      });
      $('#member_card_types tbody tr .qty input').each(function(){
        $('#member_card_types tfoot .qty').html(parseInt($('#member_card_types tfoot .qty').html(),10) + parseInt($(this).val(),10));
      });
    });
  });
--></script>
