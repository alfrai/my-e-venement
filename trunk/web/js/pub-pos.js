$(document).ready(function(){
  // the product's image size
  $('img.pub-product').load(function(){
    if ( $(this).width() > $('img.pub-product').closest('table').width()/3 )
      $(this).width($(window).width()/3);
  }).load();
  
  // the form submission for adding products to the cart
  $('.product .declination .prices form').each(function(){
    var orig = $(this).find('select').val();
    $(this).find('select').change(function(){
      if ( orig == $(this).val() )
        return;
      $(this).closest('form').submit();
    });
    $(this).submit(function(){
      if ( window.location.hash == '#debug' )
        return true;
      
      var form = this;
      $.ajax({
        url: $(this).prop('action'),
        type: $(this).prop('method'),
        data: $(this).serialize(),
        error: function(){
          $(form).find('select').val(orig);
          console.log('fail: '+orig);
        },
        success: function(json){
          orig = $(form).find('select').val();
          LI.alert(json.success.message, 'success');
        }
      });
      return false; // submit only w/ ajax
    });
  });
});
