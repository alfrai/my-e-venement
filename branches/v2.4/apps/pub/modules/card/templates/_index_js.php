<script type="text/javascript"><!--
  $(document).ready(function(){
    $('#member_card_types tbody tr').change(function(){
      currency = $(this).find('.value').html().replace(/^.*(&nbsp;.*)/,'$1');
      $(this).find('.total').html((
        $(this).find('.qty input').val()
        *
        parseInt($(this).find('.value').html().replace(',','.'))
      ).toFixed(2) + currency);
      
      // totals
      $('#member_card_types tbody tr .total').each(function(){
        $('#member_card_types tfoot .total').html((
          parseInt($('#member_card_types tfoot .total').html().replace(',','.'))
          +
          parseInt($(this).html().replace(',','.'))
        ).toFixed(2) + currency);
      });
      $('#member_card_types tbody tr .qty input').each(function(){
        $('#member_card_types tfoot .qty').html(parseInt($('#member_card_types tfoot .qty').html()) + parseInt($(this).val()));
      });
    });
  });
--></script>
