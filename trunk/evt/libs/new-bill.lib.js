/**********************************************************************************
*
*	    This file is part of e-venement.
*
*    e-venement is free software; you can redistribute it and/or modify
*    it under the terms of the GNU General Public License as published by
*    the Free Software Foundation; either version 2 of the License.
*
*    beta-libs is distributed in the hope that it will be useful,
*    but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*    GNU General Public License for more details.
*
*    You should have received a copy of the GNU General Public License
*    along with beta-libs; if not, write to the Free Software
*    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*
*    Copyright (c) 2006 Baptiste SIMON <baptiste.simon AT e-glop.net>
*
***********************************************************************************/
// warnings
function warning(msg)
{
  $('#warning').html(msg);
  $('#warning').fadeIn('slow',function(){
    setTimeout(function(){ $('#warning').fadeOut('slow'); },4000);
  });
}

// the clients
function newbill_client_valid()
{
  $.get('evt/bill/transac-personne.cmd.php?'+
        'transac='+$('#bill-op input[name=transac]').val()+
        '&client='+$('#bill-client input[name=client]:checked').val(),
    function(data){
      if ( data == 0 )
      {
        $('#bill-client input[name=search]').remove();
        $('#bill-client input[name=client]:checked').parent().remove().find('span').appendTo('#bill-client p');
        $('#bill-client .list').remove();
        $('#bill-client .microfiche').remove();
        $('#bill-client .search').removeClass('search');
      }
      else
      {
        warning("Erreur dans la mise à jour des données client de l'opération en cours.");
        $('#bill-client .list > *').remove();
        $('#bill-client .microfiche').remove();
      }
  });
}
function newbill_client_search(elt)
{
  $('#bill-client .list').load('evt/bill/search-ppl.page.php?nom='+elt.val()+' .list > ul',null,function(){
    // microfiche refresh
    $('#bill-client .list li').mouseenter(function(){
      $('#bill-client .microfiche').load('ann/microfiche.hide.php?id='+elt.find('input[name=id]').val(),null,function(){
        elt.addClass('display');
        elt.prepend($('<span class="close" />').click(function(){ elt.parent().removeClass('display'); }));
      });
    });
    // client validation
    $('#bill-client input[name=client]').change(newbill_client_valid);
  });
}

// the events
function newbill_evt_select()
{
  evt = $('#bill-tickets input[type=radio]:checked').parent().parent().parent().clone(true);
  evt.find('ul').remove();
  
  evtrub = $('#bill-tickets input[type=radio]:checked').parent().parent();
  
  // select event
  $('#bill-tickets input[type=radio]:checked').parent()
    .remove()
    .prependTo('#bill-tickets ul.spectacles')
    .prepend(evt.find('a'));
  
  // put radio button in front of all
  radio = $('#bill-tickets ul.spectacles input[type=radio]:checked');
  radio.parent().prepend(radio);
  
  // microfiche removal
  $('#bill-tickets .microfiche').remove();
  
  // remove event if no more child
  if ( evtrub.children('li').length <= 0 )
    evtrub.parent().remove();
  
  // print the prices
  $('#bill-tarifs').show();
}
function newbill_evt_refreshjs()
{
  // enable the preview of the "jauges"
  $('#bill-tickets .evt').unbind().mouseenter(function(){
    //$('#bill-tickets .microfiche').load('org/infos/microfiche-evt.hide.php?id='+$(this).find("input[name='manifs[]']").val());
    
    if ( $(this).find('.jauge').children().length == 0 )
    (manif = $(this)).find('.jauge').load('evt/bill/getjauge.hide.php?manifid='+$(this).find('input[name=manifs[]]').val());
    $(this).find('.jauge').unbind().click(function(){
      $(this).load('evt/bill/getjauge.hide.php?manifid='+$(this).parent().find('input[name=manifs[]]').val());
    });
  });
  
  // event validation
  $('#bill-tickets .list input[type=radio]').change(newbill_evt_select);
}

// tickets
function newbill_tickets_add_error(ok)
{
  manif = $('#bill-tickets input[type=radio]:checked').parent();
  for ( i = 0 ; i < qte ; i++ )
    manif.find('input.ticket[value='+tarif+'][type=hidden]').eq(0).remove();
  if ( (nb = manif.find('input.ticket[value='+tarif+'][type=hidden]').length) > 0 )
    manif.find('span.tickets span.'+tarif).html(nb+tarif);
  else
    manif.find('span.tickets span.'+tarif).parent().remove();
  if ( ok ) warning("Impossible d'ajouter un ticket, problème lors de l'accès à la base de données.");
  else      warning("Impossible d'ajouter un ticket, accès à la base de données impossible.");
}
function newbill_tickets_remove_error(ok)
{
  // les hidden
  for ( i = 0 ; i < -qte ; i++ )
  {
    hidden = $('#bill-tarifs input.ticket').clone(true);
    hidden.val(tarif)
      .attr('name','manif['+manifid+'][]')
      .addClass(tarif);
    manif.append(hidden);
  }
  // l'affichage visuel
  if ( manif.find('span.tickets span.'+tarif).length == 0 )
  {
    print = $('#bill-tarifs span.tickets').clone(true);
    print.find('span').addClass(tarif)
    manif.append(print);
  }
  manif.find('span.tickets span.'+tarif).html(manif.find('input.ticket[value='+tarif+'][type=hidden]').length+tarif);
  // l'alerte
  if ( ok ) warning("Impossible de retirer un ticket, problème lors de l'accès à la base de données.");
  else      warning("Impossible de retirer un ticket, accès à la base de données impossible.");
}
function newbill_tickets_new_visu(tarif)
{  
  span   = $('#bill-tarifs span.tickets').clone(true);
  
  if ( $('#bill-tarifs input[type=text]').val() <= 1 )
    $('#bill-tarifs input[type=text]').val(1);
  qte = $('#bill-tarifs input[type=text]').val();
  
  // visuel
  if ( (nb = $('#bill-tickets input[type=radio]:checked').parent().find('input.ticket.'+tarif).length) > 0 )
    $('#bill-tickets input[type=radio]:checked').parent().find('span.tickets span.'+tarif).html((parseInt(nb)+parseInt(qte))+tarif);
  else
  {
    span.find('span').append(qte+tarif).addClass(tarif);
    $('#bill-tickets input[type=radio]:checked').parent().append(span);
  }
  
  // form
  manifid = $('#bill-tickets input[type=radio]:checked').val();
  for ( i = 0 ; i < qte ; i++ )
  {
    hidden = $('#bill-tarifs input.ticket').clone(true);
    hidden.val(tarif)
      .attr('name','manif['+manifid+'][]')
      .addClass(tarif);
    $('#bill-tickets input[type=radio]:checked').parent().append(hidden);
  }
}
function newbill_tickets_click_remove()
{
  // remove some tickets from selection
  $('#bill-tickets span.tickets').unbind().click(function(){
    manif = $(this).parent();
    tarif = $(this).find('span').attr('class');
    manif.find('input.ticket[value='+tarif+'][type=hidden]').eq(0).remove();
    if ( (nb = manif.find('input.ticket[value='+tarif+'][type=hidden]').length) > 0 )
      $(this).find('span').html(nb+tarif);
    else
      $(this).remove();
    
    // SGBD
    qte = -1;
    transac = $('#bill-op input[name=transac]').val();
    manifid = manif.find('input[type=radio]').val();
    $.ajax({
      type: 'GET',
      url:  'evt/bill/tickets.cmd.php',
      data: ({ transac: transac, manifid: manifid, qte: qte, tarif: tarif }),
      success: function(data){
        if ( data != '0' )
          newbill_tickets_remove_error(true)
        else
          newbill_tickets_refresh_money();
      },
      error: newbill_tickets_remove_error
    });
  });
}
function newbill_tickets_refresh_money()
{
  total = 0;
  
  $('#bill-tickets .spectacles .evt').each(function(){
    price = 0;
    manif = $(this);
    
    manif.find("input.ticket").each(function(){
      tarif = $(this).val();
      price += parseFloat(manif.find('input[name='+tarif+'].prix').val());
    });
    
    manif.find('.total').html(price);
    total += price;
  });
  
  $('.spectacles li.total span.total').html(total);
}

$(document).ready(function(){
  $('form').submit(function(){ return false; });
  
  // stage 1 : client search validation
  $('#bill-client input[name=search]').focus();
  
  $('#bill-client input[name=search]').keypress(function(e){ if ( e.which == 13 ) {
    newbill_client_search($(this));
    return false;
  }});
  
  // stage 2 : 
  var url;
  url = 'evt/bill/search-evt.page.php?';
  // initial loading
  $('#bill-tickets .list').load(url+' .list > ul',null,newbill_evt_refreshjs);
  // load after search
  $('#bill-tickets input[name=search]').keypress(function(e){ if ( e.which == 13 ) {
    excludes = '';
    $('#bill-tickets .spectacles .evt input[name=manifs[]]').each(function(){
      excludes += '&exclude[]='+$(this).val();
    });
    $('#bill-tickets .list').load(url+excludes+'&nom='+$(this).val()+' .list > ul',null,newbill_evt_refreshjs);
    return false;
  }});
  
  // stage 3 : select tickets
  $('#bill-tarifs').hide();
  $('#bill-tarifs button').click(function(){
    tarif = $(this).val();
    newbill_tickets_new_visu(tarif);
    
    // SGBD
    transac = $('#bill-op input[name=transac]').val();
    qte = $('#bill-tarifs input[type=text]').val();
    $.ajax({
      type: 'GET',
      url:  'evt/bill/tickets.cmd.php',
      data: ({ transac: transac, manifid: manifid, qte: qte, tarif: tarif }),
      success: function(data) {
        if ( data != '0' )
          newbill_tickets_add_error(true);
        else
          newbill_tickets_refresh_money();
      },
      error: newbill_tickets_add_error
    });
    
    newbill_tickets_click_remove();
  });
  
  // compta : choose BdC or Facture / print tickets
  $('#bill-compta .bdc').click(function(){
    window.open('evt/bill/compta.php?type=bdc&transac='+$('#bill-op input[name=transac]').val());
  });
  $('#bill-compta .facture').click(function(){
    window.open('evt/bill/compta.php?type=facture&transac='+$('#bill-op input[name=transac]').val());
  });
  $('#bill-compta button.print').click(function(){
    group = $('#bill-compta input[name=group].print:checked').length > 0 ? '&group' : '';
    if ( $('#bill-compta input[name=duplicata].print:checked') )
    {
      manifid = (str = $('#bill-tickets .spectacles input[name=manifs[]]:checked').val()) ? '&manifid='+str : '';
      if ( manifid )
        tarif = (str = $('#bill-compta input[name=tarif].print').val()) != '' ? '&tarif='+str : '';
    }
    
    window.open('evt/bill/new-tickets.php?transac='+$('#bill-op input[name=transac]').val()+group+tarif+manifid);
  });
  
  if ( $('#bill-compta input[name=duplicata].print:checked').length == 0 )
    $('#bill-compta input[name=tarif].print').attr('disabled','disabled');
  $('#bill-compta input[name=duplicata].print').change(function(){
    if ( $('#bill-compta input[name=duplicata].print:checked').length > 0 )
      $('#bill-compta input[name=tarif].print').attr('disabled','');
    else
      $('#bill-compta input[name=tarif].print').attr('disabled','disabled');
  });
  
  // stage 4 : pay !
  $('#bill-paiement #pay').click(function(){
    $.get('evt/bill/all-is-printed.cmd.php',{ transac: $('#bill-op input[name=transac]').val() },function(data){
      if ( data == 0 )
      {
        warning('ok pour le paiement');
        $('#bill-paiement').addClass('show');
        
        // cleaning useless widgets on screen
        $('#bill-tickets span.tickets').unbind('click');
        $('#bill-tickets .list, #bill-tickets .search').remove();
        $('#bill-tarifs').remove();
        $('#bill-compta .print').remove();
        
        $('#bill-paiement p.total span').html($('#bill-tickets .spectacles .total .total').html());
      }
      else if ( data == 255 )
        warning("Attention, vous devez bien imprimer tous les tickets avant de passer à l'encaissement");
      else
        warning("Impossible de vérifier si tout a bien été imprimé...");
    });
  });
  $('#bill-paiement ul input[name=valider]').click(function(){
    // vérifier les données
    // insérer en base
    // dupliquer les éléments gfx
    // soustraire de ce qu'il reste à payer visuellement
  });
});

