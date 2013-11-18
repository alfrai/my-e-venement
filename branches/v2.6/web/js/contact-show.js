function contact_load_professionals(i)
{
  if ( typeof(professionals) != "undefined" )
  if ( professionals[i] )
  {
    $.get(professionals[i],function(data){
      $('#more #professional-'+i).html( $($.parseHTML(data)).find('#sf_admin_form_tab_menu') );
      $('#more #professional-'+i+' #sf_admin_form_tab_menu').removeAttr('id').addClass('sf_admin_form_tab_menu');
      contact_load_professionals(i+1);
    });
  }
}

$(document).ready(function(){

  if ( $('#more').length <= 0 )
    return false;

  // existing professionals
  contact_load_professionals(0);
  
  // hide / show every professional
  $('#more .professional h2').parent().click(function(){
    $(this).parent().find('.sf_admin_form').toggle();
  });
  
  if ( $('#more .professional h2').length > 1 )
    $('#more .professional h2').click();
  
  // remove the ticketting part
  if ( $('#remove_ticketting').length > 0 )
    $('a[href="#sf_fieldset_ticketting"]').parent().hide();
});
