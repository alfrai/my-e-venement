$(document).ready(function(){
  var object_elts = 'td:first-child:not([class]), .sf_admin_list_td_name, .sf_admin_list_td_firstname, .sf_admin_list_td_postalcode, .sf_admin_list_td_city, .sf_admin_list_td_list_emails, .sf_admin_list_td_list_phones, .sf_admin_list_td_organisms_list, .sf_admin_list_td_list_see_orgs, .sf_admin_list_td_list_contact, td:last-child';
  var subobjects_elts = '.sf_admin_list_td_list_professional_id, .sf_admin_list_td_list_organism, .sf_admin_list_td_list_professional, .sf_admin_list_td_list_organism_postalcode, .sf_admin_list_td_list_organism_city, .sf_admin_list_td_list_professional_emails, .sf_admin_list_td_list_organism_phones_list, .sf_admin_list_td_list_professional_description';
  
  // MULTIPLE PROFESSIONALS
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
  
  // SEEING CONTACT'S ORGANISMS
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
  
  // ADDING CONTACTS TO GROUPS
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
