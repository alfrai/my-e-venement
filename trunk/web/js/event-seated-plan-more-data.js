// the global var that can be used everywhere as a "root"
if ( LI == undefined )
  var LI = {};

LI.seatedPlanMoreDataInitialization = function(url, show, root)
{
  if ( url == undefined )
    return false;
  
  if ( root == undefined )
    root = $('body');
  
  $('.picture.seated-plan .more-data').remove();
  if ( !show )
    return;
  
  $(root).find('.picture.seated-plan').append($('<div></div>').addClass('more-data'));
  $('#transition').show();
  $.get(url, function(data){
    $.each(data, function(key, obj){
      switch(obj.type) {
      case 'shortname':
        $('<div></div>')
          .addClass('shortname')
          .prop('title', obj.seat_name+' → '+obj.fullname+' #'+obj.transaction_id)
          .text(obj.shortname)
          .addClass('obj-'+obj.slug)
          .attr('data-contact-id', obj.id)
          .attr('data-seat-id', obj.seat_id)
          .css('left', obj.coordinates[0])
          .css('top',  obj.coordinates[1])
          .width(obj.width)
          .appendTo($('.picture.seated-plan .more-data'))
        ;
      break;
      
      case 'rank':
        $('<div></div>')
          .addClass('rank')
          .prop('title', obj.seat_name+' → '+obj.rank)
          .text(obj.rank)
          .addClass('rank-'+obj.rank)
          .attr('data-rank', obj.rank)
          .attr('data-seat-id', obj.seat_id)
          .css('left', obj.coordinates[0])
          .css('top',  obj.coordinates[1])
          .width(obj.width)
          .appendTo($('.picture.seated-plan .more-data'))
        ;
      break;
      
      case 'link':
        console.log('link');
        $('<div></div>')
          .addClass('link')
          .prop('title', obj.names[0]+' - '+obj.names[1])
          .addClass('link-seat-'+obj.ids[0]).addClass('link-seat-'+obj.ids[1])
          .attr('data-link-a', obj.ids[0]).attr('data-link-b', obj.ids[1])
          .css('left', obj.coordinates[0][0])
          .css('top',  obj.coordinates[0][1])
          .width(obj.length)
          .css('transform', 'rotate('+obj.angle+'deg)')
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
          .appendTo($('.picture.seated-plan .more-data'))
        ;
      break;
      }
    });
    
    $('#transition .close').click();
  });
}
