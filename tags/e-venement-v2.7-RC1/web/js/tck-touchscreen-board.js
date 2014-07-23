LI.boardClick = function(){
  var elt = $('.li_fieldset .ui-state-highlight .for-board').length > 0
    ? $('.li_fieldset .ui-state-highlight .for-board:first')
    : $('.li_fieldset .ui-state-highlight').find('textarea, input[type=text]').first();
  
  // special cases
  if ( $('.li_fieldset .ui-state-highlight:not(.new-family)').closest('#li_transaction_field_content').length == 1 )
    elt = $('#li_transaction_field_price_new').find('input[type=text]'); // case of qty of "products"
  if ( $('#li_transaction_manifestations .ui-state-highlight').length > 0
    && $('#li_transaction_manifestations .footer [name=price_name]').val() != '' )
    elt = $('#li_transaction_manifestations .footer [name=price_name]');
  
  if ( $(this).val().substring(0,1) != '_' )
  {
    if ( !$(this).closest('#li_transaction_field_board').hasClass('alpha') )
    {
      elt.val(elt.val()+parseInt($(this).find('.num').html(),10)); // num
      elt.keydown().keyup().keypress();
    }
    else // alpha
    {
      var button = this; // init
     
      if ( $(button).hasClass('selected') )
      {
        // same button
        var index = $(button).val().indexOf($(button).prop('title'))+1;
        var letter = $(button).val().substring(index, index+1);
        if ( !letter )
          letter = $(button).val().substring(0,1);
        
        $(button).prop('title', letter);
      }
      else
      {
        // changing button
        if ( $('#li_transaction_field_board .selected').length > 0 )
          elt.val(elt.val()+$('#li_transaction_field_board .selected').prop('title'));
        $('#li_transaction_field_board .selected').removeClass('selected').prop('title',false);
        
        // recording the current one...
        $(button).addClass('selected').prop('title',$(button).val().substring(0,1));
      }
      
      // completion
      setTimeout(function(){
        if ( $(button).is('.selected') )
        {
          elt.val(elt.val()+$(button).prop('title'));
          $('#li_transaction_field_board .selected').removeClass('selected').prop('title',false);
          elt.keydown().keyup().keypress();
        }
      },1000);
    }
  }
  else
  {
    switch ( $(this).val() ) {
    case '_ACTION_':
      if ( elt.is('textarea') )
        elt.val(elt.val()+"\n");
      else
        elt.keydown().keyup().keypress();
        elt.closest('form').submit();
      break;
    case '_BACKSPACE_':
      elt.val(elt.val().substring(0,elt.val().length-1));
      break;
    }
    elt.keydown().keyup().keypress();
  }
  
  elt.focus();
}
