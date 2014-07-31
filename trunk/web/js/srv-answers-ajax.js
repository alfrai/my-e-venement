if ( LI == undefined )
  var LI = {};

LI.srv_survey_answers_pagination = function(url){
  $.get(url, function(data){
    $('#sf_admin_content .sf_admin_list').remove();
    
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
  });
}

$(document).ready(function(){
  LI.answers_href = $('#srv-answers a').prop('href');
  LI.answers_filters = $('#srv-answers a').attr('data-filters-url');
  LI.srv_survey_answers_pagination(LI.answers_href);
});
