$(document).ready(function(){
  $('#sf_admin_pager a').each(function(){
    arr = /(.*)(\?.*)$/.exec($(this).attr('href'));
    $(this).attr('href',arr[1]+'/duplicates'+arr[2]);
  });
});
