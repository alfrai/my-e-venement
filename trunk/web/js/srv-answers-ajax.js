if ( LI == undefined )
  var LI = {};

LI.srv_survey_answers_pagination = function(url){
  $.get(url, function(data){
    $('#srv-answers .sf_admin_list').remove();
    
    data = $.parseHTML(data);
    var list = $(data).find('#sf_admin_content .sf_admin_list');
    
    list.find('caption').remove();
    list.find('.sf_admin_pagination input[type=text]').prop('disabled', true);
    list.find('.sf_admin_pagination a').each(function(){
      $(this).prop('href', $(this).prop('href')+'&'+LI.answers_filters);
      $(this).click(function(){
        LI.srv_survey_answers_pagination($(this).prop('href'));
        return false;
      });
    });
    list.appendTo($('#srv-answers'));
    
    // deleting answers
    $('#sf_fieldset_answers .sf_admin_action_delete_answer a').click(function(){
      var answer = $(this).closest('.sf_admin_row');
      $.get($(this).prop('href'), function(){ $(answer).remove(); });
      return false;
    });
  });
}

$(document).ready(function(){
  LI.answers_href = $('#srv-answers a').prop('href');
  LI.answers_filters = $('#srv-answers a').attr('data-filters-url');
  LI.srv_survey_answers_pagination(LI.answers_href);
});
