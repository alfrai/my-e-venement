// called by the newManif action, in the generator.yml
function li_location_new_manif(elt, msg)
{
  $('.new-manif.dialog').clone().addClass('active').dialog({
    appendTo: 'body',
    title: $(elt).html(),
    width: "50%",
    buttons: [{
      text: 'Ok',
      click: function(){
        window.location = $(elt).prop('href')+'?event_id='+
          encodeURIComponent($('.new-manif.dialog.active [name=event_id]').val());
      }
    }],
    modal: true,
    closeOnEscape: true,
    close: function(){ $('#transition .close').click(); }
  });
  
  return false;
}

