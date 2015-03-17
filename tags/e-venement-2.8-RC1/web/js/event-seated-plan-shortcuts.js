/** all the keyboard shortcuts **/
$(document).ready(function(){
  $(document).keypress(function(e){
    if ( $(':not(.ui-tabs-anchor):focus').length != 0 )
      return;
    if ( $('#sf_fieldset_plan').css('display') == 'none' )
      return;
    
    // CTRL+[+] / CTRL+[-] : zoom-in / zoom-out
    if ( (e.ctrlKey||e.metaKey) && e.key == '+' )
    {
      $('.gauge:first .magnify .magnify-in').click();
      return false;
    }
    if ( (e.ctrlKey||e.metaKey) && e.key == '-' )
    {
      $('.gauge:first .magnify .magnify-out').click();
      return false;
    }
    
    // [+]/[-] : increase/decrease the size of seats
    if ( e.key == '+' )
    {
      $('#seated_plan_seat_diameter').each(function(){
        $(this).val(parseInt($(this).val(),10)+1);
      });
      return false;
    }
    if ( e.key == '-' )
    {
      $('#seated_plan_seat_diameter').each(function(){
        $(this).val(parseInt($(this).val(),10)-1);
      });
      return false;
    }
    
    if ( e.key == '*' )
    {
      $('.gauge .show-all input').click();
      return false;
    }
  });
});
