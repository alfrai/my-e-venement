// the global var that can be used everywhere as a "root"
if ( LI == undefined )
  var LI = {};

LI.seatedPlanMoreDataInitialization = function(url, show, root)
{
  if ( url == undefined )
    return false;
  
  if ( root == undefined )
    root = $('body');
  
  $('.picture.seated-plan .more-data:not(.group)').remove();
  if ( !show )
    return;
  
  $(root).find('.picture.seated-plan').append($('<div></div>').addClass('more-data'));
  $('#transition').show();
  $.get(url, function(data){
    var color = Math.floor(Math.random()*255)+','+Math.floor(Math.random()*255)+','+Math.floor(Math.random()*255);
    $.each(data, function(key, obj){
      var elt = $('<div></div>')
        .text(obj.shortname)
        .attr('data-seat-id', obj.seat_id)
        .css('left', obj.coordinates[0])
        .css('top',  obj.coordinates[1])
        .width(obj.width)
        .appendTo($('.picture.seated-plan .more-data'))
      ;
      if ( obj.seat_class )
        elt.addClass('seat-extra-'+obj.seat_class)

      switch(obj.type) {
      case 'shortname':
        elt
          .addClass('shortname')
          .addClass('obj-'+obj.slug)
          .prop('title', obj.seat_name+' → '+obj.fullname+' #'+obj.transaction_id)
          .text(obj.shortname)
          .addClass('obj-'+obj.slug)
          .attr('data-contact-id', obj.id)
          .each(function(){
            $(this).width($(this).width()+parseInt($(this).css('padding-left'),10)*2);
            $(this).css('padding','0');
          });
        ;
      break;
      
      case 'rank':
        elt
          .addClass('rank')
          .prop('title', obj.seat_name+' → '+obj.rank)
          .text(obj.rank)
          .addClass('rank-'+obj.rank)
          .attr('data-rank', obj.rank)
        ;
      break;
      
      case 'link':
        elt
          .addClass('link')
          .prop('title', obj.names[0]+' - '+obj.names[1])
          .addClass('link-seat-'+obj.ids[0]).addClass('link-seat-'+obj.ids[1])
          .attr('data-link-a', obj.ids[0]).attr('data-link-b', obj.ids[1])
          .css('transform', 'rotate('+obj.angle+'deg)')
          .dblclick(function(){
            // DELETE A LINK USING THE GUI/WYSIWYG
            $('#sf_fieldset_neighbors [name="auto_links[exceptions_to_remove]"]').val(
              'eve-ids-'+$(this).attr('data-link-a')+
              '--'+
              $(this).attr('data-link-b')
            );
            $('#sf_fieldset_neighbors [name="auto_links[exceptions_to_remove_submit]"]').attr('data-no-msg', 'no-msg');
            $('#sf_fieldset_neighbors [name="auto_links[exceptions_to_remove_submit]"]').click();
            $(this).remove();
            $('#sf_fieldset_neighbors [name="auto_links[exceptions_to_remove]"]').val('');
          })
        ;
      break;
      
      case 'debt':
        elt
          .addClass('debt')
          .prop('title', obj['debt-txt']+' / '+obj.seat_name+' #'+obj.transaction_id)
          .text(obj.debt != 0 ? obj['debt-txt'] : '')
          .height(elt.width())
          .addClass('debt-'+(obj.debt == 0 ? 'ok' : (obj.debt > 0 ? 'too-much' : 'debt')))
        ;
      break;
      
      case 'group':
        elt.closest('.more-data').addClass('group');
        elt
          .addClass('group')
          .height(elt.width())
          .prop('title', obj.group_name)
          .css('background-color', 'rgb('+color+',0.5)')
        ;
        elt.closest('.seated-plan-parent').find('.seated-plan-actions .groups [name=group_id] [value='+obj.group_id+']')
          .css('background-color', 'rgba('+color+',0.5)');
      break;
      }
    });
    
    $('#transition .close').click();
  });
}
