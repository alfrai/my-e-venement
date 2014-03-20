$(document).ready(function(){
  list_add_actions_titles();
  list_scroll();
  list_edit();
});
function list_scroll()
{
  $('.sf_admin_pagination .ui-icon-seek-next').parent().unbind().click(function(){
    $('.sf_admin_pagination .ui-icon-seek-next').parent().unbind().click(function(){return false;});
    
    if ( window.list_scroll_beginning != undefined )
    for ( i = 0 ; i < window.list_scroll_beginning.length ; i++ )
      window.list_scroll_beginning[i]();
    
    $.get($(this).prop('href'),function(data){
      $('#transition .close').click();
      $('.sf_admin_list > table > tbody').append($($.parseHTML(data)).find('.sf_admin_list > table > tbody tr.sf_admin_row')
        .mouseenter(function(){
          $(this).addClass('ui-state-hover');
        })
        .mouseleave(function(){
          $(this).removeClass('ui-state-hover');
        }));
      $('#sf_admin_pager')
        .replaceWith($($.parseHTML(data)).find('#sf_admin_pager'));
      list_add_actions_titles();
      list_scroll();
      
      if ( window.list_scroll_end != undefined )
      for ( i = 0 ; i < window.list_scroll_end.length ; i++ )
        window.list_scroll_end[i]();
    });
    return false;
  });
}
function list_add_actions_titles()
{
  $('.sf_admin_td_actions a').each(function(){
    elt = $(this).clone(true);
    elt.find('span').remove();
    $(this).prop('title',elt.html());
  });
}

function list_edit()
{
  // adding the possibility to edit in the list itself the records
  $('.sf_admin_row .sf_admin_text').unbind().dblclick(function(){

    fieldname = $(this).prop('class').replace(/sf_admin_list_td_(\w+)/g,"$1").replace(/sf_admin_text/g,'').trim();
    id = $(this).closest('.sf_admin_row').find('[name="ids[]"]').val();
    
    $(this).load(window.location+'/'+id+'/getSpecializedForm?field='+fieldname+' #nothing',function(data){
      if ( $($.parseHTML(data)).find('.specialized-form input[type=text]').length > 0 )
      {
        width = $(this).innerWidth()-13+'px';
        $(this).html($($.parseHTML(data)).find('.specialized-form'));
        $(this).find('input[type=text]:first').css('width',width);
        $(this).find('input[type=text]:first').each(function(){ if(this.value == this.defaultValue) this.select(); });
        $(this).find('input[type=text]:first').focus();
        
        // escape
        $(this).find('input[type=text]:first').unbind().keyup(function(e){
          if ( e.keyCode == 27 )
          {
            $(this).closest('form').get(0).reset();
            $(this).closest('.sf_admin_text').html($(this).val());
          }
        });
        
        // submitting specialized form
        $(this).find('form').unbind().submit(function(){
          $(this).parent().addClass('submitting');
          $.ajax({
            url: $(this).prop('action'),
            data: $(this).serialize(),
            method: 'post',
            success: function(data){
              $('.specialized-form.submitting').each(function(){
                $(this).closest('.sf_admin_text').html($(this).find('input[type=text]:first').val());
              });
            }
          });
          return false;
        });
      }
    });
  });
  // submit all specialized forms when submitting any form on the page
  window.onbeforeunload = function(){
    $('.specialized-form:not(.submitting) form').each(function(){
      $(this).submit();
    });
    window_transition();
  };
}
