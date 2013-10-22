// called by the newManif action, in the generator.yml
function li_location_new_manif(elt, msg)
{
  name = prompt(msg);
  if ( !name )
    return false;
  
  window.location = $(elt).prop('href')+'?event_name='+encodeURIComponent(name);
  return false;
}

