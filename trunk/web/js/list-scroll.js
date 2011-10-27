$(document).ready(function(){
  list_add_actions_titles();
  list_scroll();
  list_edit();
});
function list_scroll()
{
  $('.sf_admin_pagination .ui-icon-seek-next').parent().unbind().click(function(){
    $('.sf_admin_pagination .ui-icon-seek-next').parent().unbind().click(function(){return false;});
    $.get($(this).attr('href'),function(data){
      $('.sf_admin_list > table > tbody').append($(data).find('.sf_admin_list > table > tbody tr.sf_admin_row')
        .mouseenter(function(){
          $(this).addClass('ui-state-hover');
        })
        .mouseleave(function(){
          $(this).removeClass('ui-state-hover');
        }));
      $('#sf_admin_pager')
        .replaceWith($(data).find('#sf_admin_pager'));
      list_add_actions_titles();
      list_scroll();
    });
    return false;
  });
}
function list_add_actions_titles()
{
  $('.sf_admin_td_actions a').each(function(){
    elt = $(this).clone(true);
    elt.find('span').remove();
    $(this).attr('title',elt.html());
  });
}

function list_edit()
{
  // adding the possibility to edit in the list itself the records
  $('.sf_admin_row .sf_admin_text').unbind().dblclick(function(){

    fieldname = $(this).attr('class').replace(/sf_admin_list_td_(\w+)/g,"$1").replace(/sf_admin_text/g,'').trim();
    id = $(this).closest('.sf_admin_row').find('[name="ids[]"]').val();
    
    $(this).load(window.location+'/'+id+'/getSpecializedForm?field='+fieldname+' #nothing',function(data){
      if ( $(data).find('.specialized-form input[type=text]').length > 0 )
      {
        width = $(this).innerWidth()-13+'px';
        $(this).html($(data).find('.specialized-form'));
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
          $.post($(this).attr('action'), $(this).serialize(), function(data){
            $('.specialized-form.submitting').each(function(){
              $(this).closest('.sf_admin_text').html($(this).find('input[type=text]:first').val());
            });
          });
          return false;
        });
      }
    });
  });
  // submit all specialized forms when submitting any form on the page
  window.onbeforeunload = function(){
    $('.specialized-form form').each(function(){
      $(this).submit();
    });
    window_transition();
  };
}
