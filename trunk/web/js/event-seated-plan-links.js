// the global var that can be used everywhere as a "root"
if ( LI == undefined )
  var LI = {};

LI.seatedPlanLinksInitialization = function(url, show = true, root)
{
  if ( url == undefined )
    return false;
  
  if ( root == undefined )
    root = $('body');
  
  $('.picture.seated-plan .links').remove();
  if ( !show )
    return;
  
  $('.picture.seated-plan').append('<div class="links"></div>');
  $('#transition').show();
  $.get('/event_dev.php/seated_plan/20/getLinks', function(data){
    $.each(data, function(key, link){
      var elt = $('<div></div>').addClass('link');
      elt
        .prop('title', link.names[0]+' - '+link.names[1])
        .addClass('link-seat-'+link.ids[0]).addClass('link-seat-'+link.ids[1])
        .attr('data-link-a', link.ids[0]).attr('data-link-b', link.ids[1])
        .css('left', link.coordinates[0][0])
        .css('top',  link.coordinates[0][1])
        .width(link.length)
        .css('transform', 'rotate('+link.angle+'deg)')
        .dblclick(function(){
          // DELETE A LINK USING THE GUI/WYSIWYG
          $('#sf_fieldset_seat_links [name="auto_links[exceptions_to_remove]"]').val(
            'eve-ids-'+$(this).attr('data-link-a')+
            '--'+
            $(this).attr('data-link-b')
          );
          $('#sf_fieldset_seat_links [name="auto_links[exceptions_to_remove_submit]"]').attr('data-no-msg', 'no-msg');
          $('#sf_fieldset_seat_links [name="auto_links[exceptions_to_remove_submit]"]').click();
          $(this).remove();
          $('#sf_fieldset_seat_links [name="auto_links[exceptions_to_remove]"]').val('');
        })
      ;
      
      $('.picture.seated-plan .links').append(elt);
    });
    
    $('#transition .close').click();
  });
}
