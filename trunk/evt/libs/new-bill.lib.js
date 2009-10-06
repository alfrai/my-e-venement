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
  $.get(encodeURI('evt/api/transac-personne.cmd.php?'+
        'transac='+$('#bill-op input[name=transac]').val()+
        '&client='+$('#bill-client input[name=client]:checked').val()),
    function(data){
      if ( data == 0 )
      {
        $('#bill-client input[name=search]').remove();
        $('#bill-client input[name=client]:checked').parent().remove().find('> *').appendTo('#bill-client p');
        $('#bill-client .list').fadeOut(2000,function(){ $('#bill-client .list').remove(); });
        $('#bill-client .microfiche').remove();
        $('#bill-client .search').removeClass('search');
        $('#bill-client').addClass('selected');
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
  $('#bill-client .list').load(encodeURI('evt/bill/search-ppl.page.php?nom='+elt.val())+' .list > ul',null,function(){
    if ( $('#bill-client .list ul li').length == 0 && $('#bill-client input[name=search]').val() != '' )
      window.open(encodeURI('ann/fiche.php?add&nom='+$('#bill-client input[name=search]').val()));
    $('#bill-client .list').fadeIn(2000,function(){ $(this).addClass('show'); });
    // microfiche refresh
    $('#bill-client .list li').mouseenter(function(){
      $('#bill-client .microfiche').load(encodeURI('ann/microfiche.hide.php?id='+$(this).find('input[name=id]').val()),null,function(){
        $('#bill-client .microfiche')
          .addClass('display')
          .prepend($('<span class="close" />'));
        $('#bill-client .microfiche .close').click(function(){ $(this).parent().fadeOut('slow'); });
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
  $('#bill-tarifs, #bill-compta, #bill-paiement #pay').fadeIn(2000,function(){ $(this).addClass('show'); });
  
  // focus on nb
  newbill_tickets_focus()
}
function newbill_evt_refreshjs()
{
  // enable the preview of the "jauges"
  $('#bill-tickets .evt').unbind().mouseenter(function(){
    //$('#bill-tickets .microfiche').load(encodeURI('org/infos/microfiche-evt.hide.php?id='+$(this).find("input[name='manifs[]']").val()));
    
    if ( $(this).find('.jauge').children().length == 0 )
    $(this).find('.jauge').load(encodeURI('evt/bill/getjauge.hide.php?manifid='+$(this).find('input[name=manifs[]]').val()));
    $('#bill-tickets .evt .jauge').click(function(){
      $(this).load(encodeURI('evt/bill/getjauge.hide.php?manifid='+$(this).parent().find('input[name=manifs[]]').val()));
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
  // focus on nb
  newbill_tickets_focus()
  
  if ( !tarif )
    return false;
  
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
// gives the focus to the number of tickets and selects its text to make modifications easier
function newbill_tickets_focus()
{
  $('#bill-tarifs input[name=nb]').focus().select();
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
      url:  'evt/api/tickets.cmd.php',
      data: ({ transac: transac, manifid: manifid, qte: qte, tarif: tarif }),
      success: function(data){
        if ( data != '0' && data != '253' )
          newbill_tickets_add_error(true);
        else
        {
          newbill_tickets_refresh_money();
          newbill_tickets_focus();
        }
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
      price += parseFloat((val = manif.find('input[name='+tarif+'].prix').val()) ? val : 0);
    });
    
    manif.find('.total').html(price);
    total += price;
  });
  
  $('.spectacles li.total span.total').html(total);
}

function newbill_paiement_remove()
{
  elt = $(this);
  $.ajax({
    type: 'GET',
    url:  'evt/api/pay.cmd.php',
    data: {
      transac: $('#bill-op input[name=transac]').val(),
      amount: $(this).parent().parent().parent().find('input.money').val(),
      mode:   $(this).parent().parent().parent().find('select.mode').val(),
      date:   $(this).parent().parent().parent().find('input.date').val(),
      del:    'del'
    },
    success: function(data){
      if ( data == '0' )
      {
        amount = parseInt(elt.parent().parent().parent().find('input.money').val());
        total = $('#bill-paiement p.total span');
        total.html(parseFloat(total.html()) + amount);
        
        elt.parent().parent().parent().remove();
      }
      else if ( data == '2' )
        warning("Vérifiez les informations saisies.");
      else
        warning("Impossible de supprimer ce règlement...");
    },
    error: function(){
      warning('Impossible de supprimer ce règlement.');
    }
  });
}
function newbill_paiement_print()
{
  clean = $('#bill-paiement ul li').eq(0);
  modetxt = clean.find('select.mode option:selected').html();
  modeval = clean.find('select.mode').val();
  amount = parseInt(clean.find('input.money').val());
  elt = clean.clone(true);
  
  // cleaning fields for a new record
  clean.find('input[type=text], select').val('');
  clean.find('input[type=text].date').blur();
  
  // duplicating
  elt.removeClass('new').addClass('untouchable');
  elt.find('input[type=submit]').unbind()
    .val('^^ retirer ^^')
    .click(newbill_paiement_remove);
  elt.find('input.date').each(function(){
    if ( !$(this).hasClass('blured') && $(this).val() )
      $(this).parent().prepend($(this).val());
  })
  elt.find('input.money').each(function(){
    $(this).parent().prepend(amount);
  })
  elt.find('select.mode').val(modeval).parent().append(modetxt);
  elt.appendTo($('#bill-paiement ul'));
  
  // soustraire de ce qu'il reste à payer visuellement
  total = $('#bill-paiement p.total span');
  total.html(parseFloat(total.html()) - amount);
  
  // focus sur un nouveau paiement
  $('#bill-paiement input.money').eq(0).focus();
}

$(document).ready(function(){
  $('form').submit(function(){ return false; });
  $('input[type=text]').keypress(function(e){
    if ( e.which == 13 )
      return false;
  });
  
  // stage 1 : client search validation
  $('#bill-client input[name=search]').focus();
  $('#bill-client input[name=search]').keypress(function(e){ if ( e.which == 13 ) {
    newbill_client_search($(this));
    return false;
  }});
  $('#bill-client a.create').click(function(){
    $('#bill-client input[name=search]').focus();
    $(this).attr('href',encodeURI('ann/fiche.php?add&nom='+$('#bill-client input[name=search]').val()));
  });
  
  // stage 2 : 
  var url;
  url = 'evt/bill/search-evt.page.php?';
  // initial loading
  $('#bill-tickets .list').load(encodeURI(url)+' .list > ul',null,newbill_evt_refreshjs);
  // hide / display the event list
  $('#bill-tickets .search .toggle').click(function(){
    $('#bill-tickets .list > ul').toggle();
    return false;
  });
  $('#bill-tickets input[name=express]').click(function(){
    if ( $(this).hasClass('unflash') )
    {
      $.get('evt/api/flash.cmd.php');
      $(this).attr('value','flash');
    }
    else
    {
      manifs = '';
      $("#bill-tickets .spectacles input[name='manifs[]']").each(function(){
        manifs += 'manifs[]='+$(this).val()+'&';
      });
      $.get(encodeURI('evt/api/flash.cmd.php?'+manifs));
      $(this).val('unflash');
    }
      
    $(this).toggleClass('unflash');
  });
  // load after search
  $('#bill-tickets input[name=search]').keypress(function(e){ if ( e.which == 13 ) {
    excludes = '';
    $('#bill-tickets .spectacles .evt input[name=manifs[]]').each(function(){
      excludes += '&exclude[]='+$(this).val();
    });
    $('#bill-tickets .list').load(encodeURI(url+excludes+'&nom='+$(this).val())+' .list > ul',null,newbill_evt_refreshjs);
    return false;
  }});
  
  // stage 3 : select tickets
  $('#bill-tarifs button[name=tarif]').click(function(){
    tarif = $(this).val();
    newbill_tickets_new_visu(tarif);
    
    // SGBD
    transac = $('#bill-op input[name=transac]').val();
    qte = $('#bill-tarifs input[type=text]').val();
    $.ajax({
      type: 'GET',
      url:  'evt/api/tickets.cmd.php',
      data: ({ transac: transac, manifid: manifid, qte: qte, tarif: tarif }),
      success: function(data) {
        if ( data != '0' && data != '253' )
          newbill_tickets_add_error(true);
        else
        {
          newbill_tickets_refresh_money();
          if ( data == '253' )
            warning('Attention, si toutes les demandes aboutissent, la jauge sera dépassée sur la manifestation sélectionnée...');
        }
      },
      error: newbill_tickets_add_error
    });
    
    newbill_tickets_click_remove();
  });
  
  // compta : choose BdC or Facture / print tickets
  $('#bill-compta button[name=bdc]').click(function(){
    opt = '';
    if ( $(this).parent().find('input[name=old-compta]:checked').length > 0 )
      opt = '&old-compta'+($(this).parent().find('input[name=msexcel]:checked').length > 0 ? '&msexcel' : '');
    window.open('evt/bill/new-compta.php?type=bdc&transac='+$('#bill-op input[name=transac]').val()+opt);
    $('#bill-compta .annul').addClass('bdc');
  });
  $('#bill-compta button[name=annul-bdc]').click(function(){
    $.get('evt/bill/new-compta.php?type=bdc&transac='+$('#bill-op input[name=transac]').val()+'&annul',function(data){
      if ( data == 0 )
      {
      	warning('Bon de commande annulé');
        $('#bill-compta button[name=annul-bdc]').removeClass('bdc');
      }
      else warning("Impossible d'annuler le bon de commande.");
    });
    return false;
  });
  $('#bill-compta .facture').click(function(){
    window.open('evt/bill/new-compta.php?type=facture&transac='+$('#bill-op input[name=transac]').val());
  });
  $('#bill-compta input[name=tarif].print').keypress(function(e){
    if ( e.which == 13 )
    {
      $('#bill-compta button.print').click();
      return false;
    }
  });
  
  if ( $('#bill-compta input[name=duplicata].print:checked').length == 0 )
    $('#bill-compta input[name=tarif].print').attr('disabled','disabled');
  $('#bill-compta input[name=duplicata].print').change(function(){
    if ( this.checked )
      $('#bill-compta input[name=tarif].print').attr('disabled','').focus();
    else
      $('#bill-compta input[name=tarif].print').attr('disabled','disabled');
  });
  
  $('#bill-compta button.print').click(function(){
    $('#bill-tarifs div.cat-tarifs table').removeClass('show');
    $('#bill-compta button.facture').addClass('printed');
    group = $('#bill-compta input[name=group].print:checked').length > 0 ? '&group' : '';
    if ( $('#bill-compta input[name=duplicata].print:checked').length > 0 )
    {
      manifid = '&manifid='+$("#bill-tickets .evt input[name='manifs[]']:checked").val();
      if ( manifid )
        tarif = (str = $('#bill-compta input[name=tarif].print').val()) != '' ? '&tarif='+str : '';
      window.open(encodeURI('evt/bill/new-tickets.php?transac='+$('#bill-op input[name=transac]').val()+group+tarif+manifid));
    }
    else
    {
      wintick = window.open();
      // faire du JSON et hilighter les manifs overbookées... ensuite afficher une boite de dialogue OUI/NON pour continuer
      $.get('evt/api/overbooking.cmd.php',{ transac: $('#bill-op input[name=transac]').val() },function(data){
        if ( data.length > 0 )
        {
          for ( i = 0 ; i < data.length ; i++ )
            $("#bill-tickets .spectacles input[name='manifs[]'][value="+data[i]+"]").parent().addClass('highlight');
          if ( !confirm('Les manifestations sur-lignées seront en dépassement de jauge si vous imprimez vos billets, souhaitez-vous continuer tout de même ?') )
          {
            $("#bill-tickets .spectacles .evt").removeClass('highlight');
            wintick.close();
            return false;
          }
          $("#bill-tickets .spectacles .evt").removeClass('highlight');
        }
        else if ( data.length == 0 ) warning('Toutes les jauges sont bonnes, on peut imprimer');
        else warning("Erreur dans le contrôle des jauges, on lance l'impression quand même");
        wintick.location = encodeURI('evt/bill/new-tickets.php?transac='+$('#bill-op input[name=transac]').val()+group);
      },'json');
    }
    $('#bill-compta input[name=duplicata].print').get(0).checked = false;
    $('#bill-compta input[name=tarif].print').attr('disabled','disabled').val('');
    return false;
  });
  
  // stage 4 : pay !
  $('#bill-paiement #pay').click(function(){
    $.get('evt/api/all-is-printed.cmd.php',{ transac: $('#bill-op input[name=transac]').val() },function(data){
      if ( data == 0 )
      {
        $('#bill-paiement').addClass('show');
        $('#bill-verify').addClass('show');
        $('#bill-tickets').addClass('paiement');
        
        // cleaning useless widgets on screen
        $('#bill-tickets span.tickets').unbind('click');
        $('#bill-tickets .list, #bill-tickets .search').remove();
        $('#bill-tarifs').remove();
        $('#bill-compta .print').remove();
        
        topay = parseFloat($('#bill-tickets .spectacles .total .total').html());
        paid  = 0;
        $('#bill-paiement li input.money').each(function(){
          if ( $(this).val() )
            paid += parseFloat($(this).val());
        });
        $('#bill-paiement p.total span').html(topay - paid);
        $('#bill-paiement input[type=text]').eq(0).focus();
      }
      else if ( data == 255 )
        warning("Attention, vous devez bien imprimer tous les tickets avant de passer à l'encaissement");
      else
        warning("Impossible de vérifier si tout a bien été imprimé...");
    });
    return false;
  });
  date = 'YYYY-MM-JJ'
  $('#bill-paiement input.date').val(date)
    .addClass('blured')
    .focus(function(){
      if ( $(this).val() == date )
        $(this).val('').removeClass('blured');
    })
    .blur(function(){
      if ( $(this).val() == '' )
        $(this).addClass('blured').val(date);
      else if ( $(this).val() == date )
        $(this).addClass('blured');
      else
        $(this).removeClass('blured');
    });
  $('#bill-paiement ul input[type=submit]').click(function(){
    $.ajax({
      type: 'GET',
      url:  'evt/api/pay.cmd.php',
      data: {
        transac: $('#bill-op input[name=transac]').val(),
        amount: $(this).parent().parent().parent().find('input.money').val(),
        mode:   $(this).parent().parent().parent().find('select.mode').val(),
        date:   $(this).parent().parent().parent().find('input.date').val()
      },
      success: function(data){
        if ( data == '0' )
          newbill_paiement_print();
        else if ( data == '2' )
          warning("Vérifiez les informations saisies.");
        else
          warning("Impossible d'ajouter le règlement...");
      },
      error: function() {
        warning("Impossible d'ajouter le règlement... Contactez votre administrateur");
      }
    });
  });
  $('#bill-paiement ul input[type=text]').keypress(function(e){
    if ( e.which == 13 )
    {
      $(this).parent().parent().parent().find('input[type=submit]').click();
      return false;
    }
  });

  // verify the data
  $('#bill-verify input').click(function(){
    $.ajax({
      type: 'GET',
      url:  'evt/api/verify.cmd.php',
      dataType: 'json',
      data: { transac: $('#bill-op input[name=transac]').val() },
      success: function(data){
        w = '';
        
        // client
        if ( data.client.fctorgid )
          client = 'prof_'+data.client.fctorgid;
        else
          client = 'pers_'+data.client.id;
        if ( !$('#bill-client input[name=client]').val() == client )
          w += 'Client mal renseigné.<br/>';
        
        // tickets
        prix = 0;
        nb = 0;
        for ( i = 0 ; i < data.tickets.length ; i++ )
        {
          tic = data.tickets[i];
          nb += parseInt(data.tickets[i].nb);
          if ( $("#bill-tickets input[name='manif["+tic.manifid+"][]'][value="+tic.tarif+"]").length != parseInt(tic.nb) )
            w += 'Ticket '+tic.tarif+' mal renseigné pour la manifestation '+tic.manifid+'.<br/>';
          else
            prix += parseInt(tic.nb) * parseFloat( tic.prixspec ? tic.prixspec : tic.prix );
        }
        if ( prix != parseFloat($('#bill-tickets .spectacles .total .total').html()) )
          w += 'Le total financier ne correspond pas !<br/>';
        if ( $('#bill-tickets input[type=hidden].ticket').length != nb )
          w += 'Vous avez un nombre de billets différent en base de données ('+$('#bill-tickets input[type=hidden].ticket').length+' vs '+nb+').<br/>';
        
        // les paiements
        paid = { db: 0, screen: 0 };
        nb = 0;
        for ( i = 0 ; i < data.paiements.length ; i++ )
        {
          pay = data.paiements[i];
          nb++;
          paid.db += parseFloat(pay.montant);
        }
        $('#bill-paiement input.money').each(function(){
          if ( $(this).val() )
          paid.screen += parseFloat($(this).val());
        });
        if ( paid.db != paid.screen )
          w += 'Le montant payé en base ne correspond pas avec celui affiché ('+paid.db+' vs '+paid.screen+'). Vérifiez bien avoir ajouté tous vos règlements.<br/>';
        if ( prix - paid.db != parseFloat($('#bill-paiement .total span').html()) )
          w += 'Le montant "à payer" ne correspond pas avec celui affiché ('+(prix-paid.db)+' vs '+parseFloat($('#bill-paiement .total span').html())+').<br/>';
        if ( $('#bill-paiement li.untouchable').length != nb )
          w += 'Vous avez un nombre de règlements différent en base de données ('+nb+' vs '+$('#bill-paiement li.untouchable').length+').<br/>';
        if ( prix - paid.db > 0 )
          w += "Votre client ne s'est pas acquité entièrement de sa dette";
        
        // the print the warnings
        if ( w == '' )
          $('form').unbind().submit();
        else
          warning(w);
      },
      error: function(e,t) {
        warning("Impossible de vérifier l'opération... Erreur: "+t);
      }
    });
    return false;
  });
});

