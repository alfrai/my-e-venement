<script type="text/javascript"><!--
  $(document).ready(function(){
    $('#member_card_types tbody tr').change(function(){
      var fr_style = LI.currency_style($(this).find('.value').text()) == 'fr';
      var currency = LI.get_currency($(this).find('.value').text());
      $(this).find('.total').html(LI.format_currency(
        parseInt($(this).find('.qty input').val())
        *
        LI.clear_currency($(this).find('.value').text())
      , true, fr_style, currency));
      
      // totals
      $('#member_card_types tfoot .total').text(0);
      $('#member_card_types tbody tr .total').each(function(){
        $('#member_card_types tfoot .total').html(
          LI.format_currency((
            LI.clear_currency($('#member_card_types tfoot .total').text())
            +
            LI.clear_currency($(this).text())
          ).toFixed(2), true, fr_style, currency)
        );
      });
      $('#member_card_types tfoot .qty').text(0);
      $('#member_card_types tbody tr .qty input').each(function(){
        $('#member_card_types tfoot .qty').text(
          parseInt($('#member_card_types tfoot .qty').text(),10) + parseInt($(this).val(),10));
      });
    }).change();
  });
--></script>
