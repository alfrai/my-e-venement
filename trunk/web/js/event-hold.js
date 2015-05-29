/**********************************************************************************
*
*	    This file is part of e-venement.
*
*    e-venement is free software; you can redistribute it and/or modify
*    it under the terms of the GNU General Public License as published by
*    the Free Software Foundation; either version 2 of the License.
*
*    e-venement is distributed in the hope that it will be useful,
*    but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*    GNU General Public License for more details.
*
*    You should have received a copy of the GNU General Public License
*    along with e-venement; if not, write to the Free Software
*    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*
*    Copyright (c) 2006-2015 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2015 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
// the global var that can be used everywhere as a "root"
if ( LI == undefined )
  var LI = {};

// the booking transaction
$(document).ready(function(){
  // the actions from buttons
  $('.sf_admin_form button.ajax').click(function(){
    var button = this;
    $.ajax({
      type: 'get',
      url: $(this).attr('data-url'),
      data: {
        source: $(this).closest('.sf_admin_form_row').find('.source').val(),
      },
      success: function(json){
        switch ( $(button).prop('name') ) {
        
        // create a transaction where we can drop deselected seats when it occurs
        case 'transfert_to_transaction':
          if ( !json.transaction_id )
            LI.alert('An error occurred', 'error');
          else
            $('.sf_admin_form [name="transaction_id"]').val(json.transaction_id).change();
          break;
        
        // steal seats from a transaction for this hold, when it is possible
        case 'get_back_seats':
          LI.alert(json.message, json.type);
          LI.seatedPlanLoadData($('.sf_admin_form .seated-plan.picture .seats-url').prop('href'), $('.sf_admin_form .seated-plan.picture'));
          break;
        }
      },
      error: function(){
        LI.alert('An error occurred', 'error');
      }
    });
    return false;
  });
  
  // the button that removes the transaction_id
  $('.sf_admin_form [name="transaction_id"]').change(function(){
    if ( $(this).val() )
      $(this).closest('.sf_admin_form_row').addClass('with-transaction-id');
    else
      $(this).closest('.sf_admin_form_row').removeClass('with-transaction-id');
  });
  $('.sf_admin_form .remove_transaction_id').click(function(){
    $(this).closest('.sf_admin_form_row').find('.transaction_id input').val('').change();
    return false;
  });
  
  // removing useless features from the classic seated plan
  setTimeout(function(){
    $('.sf_admin_form_field_show_picture .picture').unbind('mousedown');
    $('.sf_admin_form_field_show_picture .picture .anti-handling').unbind('mouseup').unbind('mousemove');
  }, 1000);
});

// seated plan
if ( LI.seatedPlanInitializationFunctions == undefined )
  LI.seatedPlanInitializationFunctions = [];
LI.seatedPlanInitializationFunctions.push(function(selector){
  LI.seatedPlanMouseup = function(){}; // to avoid any prompt after the seats rendering
  $(selector).find('.seat.txt').mouseenter(function(event){
    if ( event.buttons == 0 || (!event.ctrlKey && !event.metaKey) )
      return;
    $(this).click();
  }).click(function(){
    if ( $(this).hasClass('hold-in-progress') )
      return;
    
    // if the seat is already booked/ordered
    if ( ($(this).hasClass('ordered') || $(this).hasClass('asked')) && !$(this).hasClass('held') )
    {
      var url = $('#get-transaction-id').prop('href').replace($('#get-transaction-id').attr('data-replace'), $(this).attr('data-ticket-id'));
      $.ajax({
        type: 'get',
        url: url,
        success: function(json){
          if ( !json.transaction_id )
          {
            LI.alert('An error occurred', 'error');
            return;
          }
          $('.sf_admin_form [name="get_back_seats_from_transaction_id"]').val(json.transaction_id);
        },
        error: function(){
          LI.alert('An error occurred', 'error');
        },
      });
      return;
    }
    
    if ( $('.sf_admin_form [name="transaction_id"]').val()
      && $(this).hasClass('held')
      && ($(this).hasClass('ordered') || $(this).hasClass('printed') || $(this).hasClass('asked'))
    )
    {
      console.error('You cannot transfert a seat into a transaction if it is already booked...');
      return;
    }
    
    // if the seat is still free
    $(this).addClass('hold-in-progress');
    var url = $('#link-seat').prop('href').replace($('#link-seat').attr('data-replace'), $(this).attr('data-id'));
    
    var data = {
      transaction_id: $('.sf_admin_form [name="transaction_id"]').val(),
      hold_id: $('.sf_admin_form [name="hold_id"]').val(),
    };
    if ( window.location.hash == '#debug' )
    {
      var tmp = [];
      $.each(data, function(key,value){ tmp.push(key+'='+value); });
      window.open(url+'?debug&'+tmp.join('&'));
      return;
    }
    
    var seat = this;
    $.ajax({
      type: 'get',
      url: url,
      data: data,
      error: function(){
        LI.alert('An error occurred', 'error');
      },
      success: function(data){
        if ( !data.success )
        {
          LI.alert('An error occurred', 'error');
          return;
        }
        
        if ( data.type == 'add' )
          $(seat).addClass('held');
        else
        {
          // remove the "held" status
          $(seat).removeClass('held');
          
          // if the seat was booked in a "buffer" Transaction... remove it
          if ( $('.sf_admin_form [name="transaction_id"]').val() || data.type == 'move' )
            $(seat).closest('.seated-plan').find('.seat[data-id="'+$(seat).attr('data-id')+'"]').remove();
        }
        
        $(seat).removeClass('hold-in-progress');
      }
    });
  });
});
        
