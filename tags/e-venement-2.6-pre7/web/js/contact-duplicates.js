$(document).ready(function(){
  $('#sf_admin_pager a').each(function(){
    arr = /(.*)(\?.*)$/.exec($(this).prop('href'));
    $(this).prop('href',arr[1]+'/duplicates'+arr[2]);
  });
});
