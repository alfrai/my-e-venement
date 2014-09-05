$(document).ready(function(){
  // the product's image size
  if ( $('img.pub-product').width() > $(window).width()/3 )
    $('img.pub-product').width($(window).width()/3);
});
