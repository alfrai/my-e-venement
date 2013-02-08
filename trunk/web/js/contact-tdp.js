$(document).ready(function(){
  var object_elts = 'td:first-child:not([class]), .sf_admin_list_td_name, .sf_admin_list_td_firstname, .sf_admin_list_td_postalcode, .sf_admin_list_td_city, .sf_admin_list_td_list_emails, .sf_admin_list_td_list_phones, .sf_admin_list_td_organisms_list, .sf_admin_list_td_list_see_orgs, .sf_admin_list_td_list_contact, td:last-child';
  var subobjects_elts = '.sf_admin_list_td_list_professional_id, .sf_admin_list_td_list_organism, .sf_admin_list_td_list_professional, .sf_admin_list_td_list_organism_postalcode, .sf_admin_list_td_list_organism_city, .sf_admin_list_td_list_professional_emails, .sf_admin_list_td_list_organism_phones_list, .sf_admin_list_td_list_professional_description';
  
  // READ ONLY: deactivating every field if the user has no credential for modification
  if ( $('#tdp-top-bar .action.update[href=#]').length == 1 )
  {
    $('#tdp-content input, #tdp-content select, #tdp-content textarea')
      .attr('disabled',true);
    $('#tdp-side-bar .tdp-object-groups .new').remove();
  }
  
  // FORMS: submitting subobjects though AJAX
  $('.tdp-subobject form, .tdp-object #sf_admin_content > form').submit(function(){
    $("html, body").animate({ scrollTop: 0 }, "slow");
    $('.sf_admin_flashes *').fadeOut('fast',function(){ $(this).remove(); });
    contact_tdp_submit_forms();
    return false;
  });
  
  // CONTENT: FOCUSING ON A FIELD
  highlight = $('#tdp-content #sf_admin_content input, #tdp-content #sf_admin_content select, #tdp-content #sf_admin_content textarea');
  highlight.focusin(function(){
    $(this).closest('span')
      .addClass('tdp-highlight');
  });
  highlight.focusout(function(){
    $(this).closest('span')
      .removeClass('tdp-highlight');
  });
  
  // CONTENT: MULTIPLE PROFESSIONALS
  $('#tdp-content .sf_admin_row').each(function(){
    if ( (length = $(this).find('.sf_admin_list_td_list_organism .pro').length) > 1 )
    {
      // duplicating professional lines
      for ( i = 1 ; i < length ; i++ )
      {
        tr = $(this).clone(true);
        
        tr.find('> :not('+subobjects_elts+')')
          .remove();
        $(this).find('> :not('+subobjects_elts+')')
          .attr('rowspan',parseInt($(this).find('> :not('+subobjects_elts+')').attr('rowspan'))+1);
        
        $(this).after(tr);
      }
      
      // creating the different search cases for elements removal
      search = Array('.pro:first-child');
      for ( i = 0 ; i < length ; i++ )
        search.push(search[search.length-1]+' + .pro');
      
      tr = $(this);
      for ( i = 0 ; i < length ; i++ )
      {
        // the search path for elements to remove
        tmp = '';
        for ( j = 0 ; j < length ; j++ )
        if ( j != i )
        {
          if ( tmp != '' )
            tmp += ', ';
          tmp += search[j];
        }
        
        // removal
        tr.find(tmp)
          .remove();
        tr = tr.next();
      }
    }
  });
  
  // CONTENT: DELETING A SUBOBJECT
  $('.tdp-subobject .tdp-actions .tdp-delete').click(function(){
    elt = $(this).closest('.tdp-subobject');
    if ( elt.length != 1 )
      return false;
    
    if ( !confirm(elt.find('._delete_confirm').html()) )
    {
      $('#transition .close').click();
      return false;
    }
    
    elt.fadeOut('slow');
    $.ajax({
      url: $(this).attr('href'),
      type: 'POST',
      data: {
        sf_method: 'delete',
        _csrf_token: elt.find('._delete_csrf_token').html(),
      },
      complete: function(data) {
        elt.remove();
        $('#transition .close').click();
        $("html, body").animate({ scrollTop: 0 }, "slow");
        info = $('.tdp-object .sf_admin_flashes');
        info.replaceWith('<div class="sf_admin_flashes ui-widget"><div class="notice ui-state-highlight">Fonction supprimée.</div></div>');
        info.hide().fadeIn('slow');
        setTimeout(function(){ info.fadeOut('slow',function(){ info.remove(); }) },5000);
      },
      error: function(data,error) {
        elt.fadeIn('slow');
        $('#transition .close').click();
        info = elt.find('.sf_admin_flashes');
        info.replaceWith('<div class="sf_admin_flashes ui-widget"><div class="error ui-state-error">Impossible de supprimer la fonction... ('+error+')</div>');
        info.hide().fadeIn('slow');
        setTimeout(function(){ info.fadeOut('slow',function(){ info.remove(); }) },5000);
      },
    });
    
    return false;
  });
  
  // CONTENT: SEEING CONTACT'S ORGANISMS
  $('#tdp-content .sf_admin_list_td_list_see_orgs').click(function(){
    if ( !$(this).closest('table').hasClass('see-orgs') )
    {
      $(this).closest('table').addClass('see-orgs');
      $(this).closest('table').find('.sf_admin_list_td_list_see_orgs span').removeClass('ui-icon-seek-next').addClass('ui-icon-seek-prev');
    }
    else
    {
      $(this).closest('table').removeClass('see-orgs');
      $(this).closest('table').find('.sf_admin_list_td_list_see_orgs span').removeClass('ui-icon-seek-prev').addClass('ui-icon-seek-next');
    }
    
    return false;
  });
  
  // FILTERS
  $('#tdp-update-filters').get(0).blink = function(){
    $(this).addClass('blink');
    setTimeout(function(){ $('#tdp-update-filters').toggleClass('blink'); }, 330);
    setTimeout(function(){ $('#tdp-update-filters').toggleClass('blink'); }, 670);
    setTimeout(function(){ $('#tdp-update-filters').toggleClass('blink'); },1000);
    setTimeout(function(){ $('#tdp-update-filters').toggleClass('blink'); },1330);
  };
  
  // SIDEBAR
  $('#tdp-side-bar li').each(function(){
    $(this).attr('title',$(this).find('label').html());
  });
  $('#tdp-side-bar input[type=checkbox]').click(function(){
    $('#tdp-update-filters').get(0).blink();
  });
  
  // TOPBAR
  $('#tdp-top-bar .tdp-top-widget > a.group').mouseenter(function(){
    $(this).parent().find('.tdp-submenu').fadeIn('medium')
      .css('display','inline-block');
  });
  $('#tdp-top-bar .tdp-submenu, #tdp-top-bar .tdp-top-widget > a.group').mouseleave(function(){
    setTimeout(function(){
      if ( $('#tdp-top-bar .tdp-top-widget .tdp-submenu:hover').length + $('#tdp-top-bar .tdp-top-widget > a.group:hover').length == 0 )
        $('#tdp-top-bar .tdp-top-widget .tdp-submenu').fadeOut('fast');
    },500);
  });
  
  // ADDING CONTACTS TO GROUPS (from list)
  $('#tdp-content .sf_admin_row').unbind('click'); // remove framework bind
  $('#tdp-content .sf_admin_row > :not(.sf_admin_list_td_list_see_orgs)').click(function(){
    if ( $(this).is(subobjects_elts) )
    {
      // tick the box and highlight the professional only if there is something to deal with
      if ( $(this).closest('tr').find('.sf_admin_batch_checkbox[name="professional_ids[]"]').length > 0 )
      {
        $(this).closest('tr').find(subobjects_elts).toggleClass('ui-state-highlight');
        $(this).closest('tr').find('.sf_admin_batch_checkbox[name="professional_ids[]"]')
          .attr('checked',$(this).closest('tr').find(subobjects_elts).hasClass('ui-state-highlight'))
          .change();
      }
    }
    else
    {
      $(this).closest('tr').find('> :not('+subobjects_elts+')').toggleClass('ui-state-highlight');
      $(this).closest('tr').find('.sf_admin_batch_checkbox[name="ids[]"]')
        .attr('checked',$(this).closest('tr').find('> :not('+subobjects_elts+'):first').hasClass('ui-state-highlight'))
        .change();
    }
  });
  $('#tdp-content .sf_admin_batch_checkbox').change(function(){
    if ( $(this).closest('tr').find('.sf_admin_batch_checkbox[name="professional_ids[]"]:checked').length > 0 )
      $(this).closest('tr').find(subobjects_elts).addClass('ui-state-highlight');
    else
      $(this).closest('tr').find(subobjects_elts).removeClass('ui-state-highlight');
    
    $('#tdp-side-groups label').unbind('click');
    if ( $('.sf_admin_batch_checkbox:checked').length > 0 )
    {
      $('#tdp-side-bar').addClass('add-to');
      $('#tdp-side-groups input[type=checkbox]"]:checked').removeAttr('checked');
      $('#tdp-side-groups label').click(function(){
        $(this).closest('li').find('input[type=checkbox]').click();
        $.post($('#tdp-side-bar .batch-add-to.group').attr('href'),$('#tdp-side-bar').serialize()+'&'+$('#tdp-content').serialize(),function(data){
          $('#tdp-content input[type=checkbox]:checked').click().removeAttr('checked').change();
          $('#tdp-side-groups input[type=checkbox]:checked').removeAttr('checked');
          $('.sf_admin_flashes').replaceWith($(data).find('.sf_admin_flashes').hide());
          $('.sf_admin_flashes').fadeIn('slow');
          setTimeout(function(){
            $('.sf_admin_flashes > *').fadeOut('slow',function(){
              $(this).remove();
            });
          },3000);
        });
        
        return false;
      });
    }
    else
    {
      $('#tdp-side-bar').removeClass('add-to');
      $('#tdp-side-bar label').unbind('click');
    }
  });
});

function contact_tdp_submit_forms(i = 0)
{
  $('select[multiple] option').attr('selected',true);
  if ( i < $('.tdp-subobject form').length )
  {
    $('.tdp-subobject form').eq(i).find('select[multiple] option').attr('selected',true);
    $.post($('.tdp-subobject form').eq(i).attr('action'), $('.tdp-subobject form').eq(i).serialize(), function(data){
      // retrieving corresponding subobject
      subobject = $('[name="professional[id]"][value='+$(data).find('[name="professional[id]"]').val()+']')
        .closest('.sf_admin_edit');
      if ( subobject.length == 0 )
        subobject = $('.sf_admin_edit.tdp-object-new');
    
      // flashes
      subobject.find('.sf_admin_flashes')
        .replaceWith($(data).find('.sf_admin_flashes'));
      setTimeout(function(){
        $('[name="professional[id]"][value='+$(data).find('[name="professional[id]"]').val()+']')
          .closest('.sf_admin_edit')
          .find('.sf_admin_flashes > *').fadeOut('medium',function(){ $(this).remove(); });
      },6000);
      
      // errornous fields
      if ( !subobject.hasClass('tdp-object-new') || subobject.find('.tdp-organism_id input').val() != '' )
      $(data).find('.errors').each(function(){
        subobject.find('.tdp-'+$(this).closest('.sf_admin_form_row').attr('class').replace(/^.*sf_admin_form_field_([\w_]+).*$/g,'$1'))
          .addClass('ui-state-error').addClass('ui-corner-all')
          .append($(this));
      });
      
      i++;
      contact_tdp_submit_forms(i);
    });
  }
  else
  {
    $('.form_phonenumbers .sf_admin_flashes').remove();
  
    // included forms  
    $('.tdp-object form form').submit();
    
    if ( $('.tdp-subobject .errors').length == 0 ) // no error
      $('.tdp-object #sf_admin_content > form').unbind().submit();
    else // at least one error, stopping the process
    {
      $('.tdp-object .sf_admin_flashes').fadeOut('fast',function(){
        $(this).replaceWith(
          $('.tdp-subobject .errors').first()
            .closest('.tdp-subobject')
            .find('.sf_admin_flashes')
            .clone(true)
            .hide()
            .fadeIn('medium')
        );
      });
    }
  }
}
