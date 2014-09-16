$(document).ready(function(){
  $('.ui-tabs-panel.ui-widget-content:not(:last)').prepend(
    '<div class="sf_admin_form_row report-versions"><label></label><span class="diff">'+
      'v'+$('.sf_admin_action_version input').val()+
    '</span><span>'+
      $('.sf_admin_action_versions a').html()+
    '</span></div>');
});
