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
        
        case 'transfert_to_transaction':
          if ( !json.transaction_id )
            LI.alert('An error occurred', 'error');
          else
            $('.sf_admin_form [name="transaction_id"]').val(data.transaction_id).change();
          break;
        
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
});

// seated plan
if ( LI.seatedPlanInitializationFunctions == undefined )
  LI.seatedPlanInitializationFunctions = [];
LI.seatedPlanInitializationFunctions.push(function(selector){
  $(selector).find('.seat.txt').mouseenter(function(event){
    if ( event.buttons == 0 || !event.ctrlKey )
      return;
    $(this).click();
  }).click(function(){
    if ( $(this).hasClass('hold-in-progress') )
      return;
    
    $(this).addClass('hold-in-progress');
    var url = $('#link-seat').prop('href').replace($('#link-seat').attr('data-replace'), $(this).attr('data-id'));
    
    if ( window.location.hash == '#debug' )
    {
      window.open(url+'?debug');
      return;
    }
    
    var seat = this;
    $.ajax({
      type: 'get',
      url: url,
      data: { transaction_id: $('.sf_admin_form [name="transaction_id"]').val() },
      success: function(data){
        if ( !data.success )
          return;
        
        if ( data.type == 'add' )
          $(seat).addClass('held');
        else
        {
          // remove the "held" status
          $(seat).removeClass('held');
          
          // if the seat was booked in a "buffer" Transaction... remove it
          if ( $('.sf_admin_form [name="transaction_id"]').val() )
            $(seat).closest('.seated-plan').find('.seat[data-id="'+$(seat).attr('data-id')+'"]').remove();
        }
        
        $(seat).removeClass('hold-in-progress');
      }
    });
  });
});
        
