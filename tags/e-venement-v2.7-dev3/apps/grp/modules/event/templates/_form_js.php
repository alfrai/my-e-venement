<?php
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
*    Copyright (c) 2006-2012 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2012 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php
/**
  * THIS FILE IS USED BY THE EVENT MODULE BUT ALSO BY THE PROFESSIONAL_FULL MODULE...
  *
  **/
?>
    $(document).ready(function(){
      // if we submit any form
      $('form').submit(function(){
        $(this).find('input[name="contact_entry_new[entry_id]"],input[name="contact_entry[entry_id]"],input[name="manifestation_entry[entry_id]"]').val('<?php echo $entry->id ?>');
        form = this;
        $.post($(this).prop('action'),$(this).serialize(),function(data){
          if ( $(form).closest('#manifestation_entry_new, #contact_entry_new').length > 0 )
            window.location.reload();
          else
            $('#transition .close').click();
        });
        return false;
      });
      
      // if we suppress any contact or manifestation
      $('.delete').click(function(){
        if ( confirm('<?php echo __('Are you sure?',array(),'sf_admin') ?>') )
        {
          $.get($(this).prop('href'),function(){
            window.location.reload();
          });
          return false;
        }
      });
      
      // if we submit a cell
      $('form.EntryElement').unbind().submit(function(){
        var form = this;
        $.post($(this).prop('action'),$(this).serialize(),function(data){
          data = $.parseHTML(data);
          $('#transition .close').click();
          
          $(form).find('input[name="entry_element[second_choice]"]:checked').length > 0
            ? $(form).closest('td').addClass('second_choice')
            : $(form).closest('td').removeClass('second_choice');
          
          $(form).find('input[name="entry_element[accepted]"]:checked').length > 0
            ? $(form).closest('td').addClass('accepted')
            : $(form).closest('td').removeClass('accepted');
          
          $(form).html($(data).find('form').html());
          $(form).prepend('<p></p>');
          $(form).find('label').each(function(){
            $(form).find('p:first').prepend($('<span></span> ').prop('title',$(this).html()).prepend($(this).parent().find('input')));
          });
          
          $(form).find('.content, .sf_admin_form_row, label, fieldset, .sf_admin_actions, input[name=_save_and_add]').remove();
          
          // autosubmit on ticking checkbox
          $(form).find('input[type=checkbox]').change(function(){
            $(this).closest('form').submit();
          });
        });
        
        grp_extra_empty_fields_cleanup();
        return false;
      });
      
      // if a line has already been transposed, we disable any action on it
      $('tbody tr.transposed td:not(.contact) input, tbody tr.transposed select').prop('disabled',true);
      $('tbody tr.transposed a.delete').remove();
      
      $('.EntryTickets form').unbind().submit(form_entry_tickets); // if we submit a tickets' form
      $('.EntryTickets a').unbind().click(a_entry_tickets); // if we submit a deletion on tickets lines
      change_tickets();   // auto submit tickets forms, intiating in an independant thread
      calculate_gauges(); // calculate how many tickets we've got, in an independant thread
      
      // autosubmit on ticking checkbox
      $('form.EntryElement input[type=checkbox]').change(function(){
        $(this).closest('form').submit();
      });
    });
    
    function a_entry_tickets()
    {
      $.get($(this).prop('href'));
      $(this).closest('form').remove();
      calculate_gauges();
      return false;
    }
    
    function change_tickets()
    {
      $('.EntryTickets form input, .EntryTickets form select').unbind().keypress(function(k){
        if ( k.which == '13' )
        {
          var form = $(this).closest('form');
          if ( parseInt(form.find('input').val(),10) != 0 && form.find('select').val() != '' )
            form.submit();
          return false;
        }
      });
    }
    
    function form_entry_tickets()
    {
      var form = this;
      
      $.post($(this).prop('action'),$(this).serialize(),function(data){
        data = $.parseHTML(data);
        $('#transition .close').click();
        
        f = $(data).find('form');
        f.submit(form_entry_tickets);
        f.find('a').unbind().click(a_entry_tickets);
        f.prepend($('<p></p>').append(
          f.find('input[name="entry_tickets[quantity]"],select[name="entry_tickets[price_id]"],input[type=hidden],a.delete,select[name="entry_tickets[gauge_id]"]')
        ));
        f.find('.content, .sf_admin_form_row, label, fieldset, .sf_admin_actions, input[name=_save_and_add]').remove();
        
        // if new
        if ( $(form).find('a.delete').length == 0 )
        {
          $(form).parent().append(f);
          $(form).find('input[type=text]').val('').focus();
          $(form).find('select[name="entry_tickets[price_id]"]').val('');
        }
        else
          $(form).replaceWith(f);
        
        calculate_gauges();
        grp_extra_empty_fields_cleanup();
        change_tickets();   // auto submit tickets forms
      });
      return false;
    }
    
    function calculate_gauges()
    {
      if ( $('#no-calculate-gauge').length > 0 )
        return;
      
      $('tfoot .count > *').remove();
      $('.ticketting .count > *:not(.total)').remove();
      $('.ticketting .count .total').html(0);
      $('tfoot .count').each(function(){
        var curclass = /manifestation-\d+$/.exec($(this).attr('class'));
        
        $.get('<?php echo url_for('event/gauge') ?>?manifestation_id='+/\d+$/.exec(curclass),function(data){
          gauge = $($.parseHTML(data)).find('.gauge');
          $('.count.'+gauge.attr('id')).append(gauge);
        });
      });
      
      // line by line
      $('tbody tr input[name="entry_tickets[quantity]"]').each(function(){
        if ( $(this).closest('form').find('select').val() != '' )
        {
          if ( $(this).closest('tr').find('.ticketting .count .price-id-'+$(this).closest('form').find('select').val()).length > 0 )
          {
            nb = $(this).closest('tr').find('.ticketting .count .price-id-'+$(this).closest('form').find('select').val()+' .nb');
            nb.html(parseInt(nb.html(),10)+parseInt($(this).val(),10));
          }
          else
          {
            $(this).closest('tr').find('.ticketting .count')
              .prepend('<span class="tickets price-id-'+$(this).closest('form').find('select').val()+'"><span class="nb">'+$(this).val()+'</span><span class="name">'+$(this).closest('form').find('select option:selected').html()+'</span></span>');
          }
          
          // total calculation
          total = $(this).closest('tr').find('.ticketting .count .total');
          total.html(parseInt(total.html(),10)+parseInt($(this).val(),10));
        }
      });
    }
