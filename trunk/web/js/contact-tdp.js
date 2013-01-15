$(document).ready(function(){
  // FILTERS
  $('#tdp-update-filters').get(0).blink = function(){
    $(this).addClass('blink');
    setTimeout(function(){
      $('#tdp-update-filters').toggleClass('blink');
    },330);
    setTimeout(function(){
      $('#tdp-update-filters').toggleClass('blink');
    },670);
    setTimeout(function(){
      $('#tdp-update-filters').toggleClass('blink');
    },1000);
  };
  
  // SIDEBAR
  $('#tdp-side-bar li').each(function(){
    $(this).attr('title',$(this).find('label').html());
  });
  $('#tdp-side-bar input[type=checkbox]').click(function(){
    $('#tdp-update-filters').get(0).blink();
  });
});
