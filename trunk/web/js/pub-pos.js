$(document).ready(function(){
  if ( window.location.hash != '#debug' )
  {
    // directly goes to the target, if only one choice is possible
    if ( $('.mod-store.action-index .sf_admin_list_td_list_children a').length == 1
      && $('.mod-store.action-index .sf_admin_list_td_list_name a').length == 1 )
      window.location = $('.mod-store.action-index .sf_admin_list_td_list_children a').prop('href');
    else if ( $('.mod-store.action-index #sf_admin_content .sf_admin_list_td_list_name a, .mod-store.action-index #sf_admin_footer .sf_admin_list_td_list_name a').length == 1 )
      window.location = $('.mod-store.action-index .sf_admin_list_td_list_name a').prop('href');
  }
  
  // the categories list header, to hide/show
  if ( $('#sf_admin_header h1').length > 0 )
    $('#sf_admin_content table thead, #sf_admin_content table tbody tr:first').hide();
  
  // the product's image size
  $('img.pub-product').load(function(){
    var width = 0;
    if ( $(this).width() > (width = $(this).closest('table').width()/($(window).width() > 1400 ? 3 : 2)) )
      $(this).width(width);
  }).load();
  
  $('.product .declination .text').niceScroll();
  
  // the form submission for adding products to the cart
  $('.product .declination .prices form.price_qty').each(function(){
    var orig = $(this).find('select').val();
    $(this).find('select').change(function(){
      if ( orig == $(this).val() )
        return;
      
      // for free prices
      if ( $(this).closest('.free-price').length == 0 )
      {
        var free_price_selector = '[name="store[free-price]"]';
        $(this).closest('form').find(free_price_selector).val(
          $(this).closest('[data-price-id]')
            .find('.value '+free_price_selector).val()
        );
      }
      
      $(this).closest('form').submit();
    });
    $(this).submit(function(){
      if ( window.location.hash == '#debug' )
      {
        $(this).prop('method', 'get');
        return true;
      }
      $(this).prop('method', 'post');
      
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
          if ( $(form).closest('.free-price').length > 0 )
            $(form).closest('.free-price').remove();
          else
            orig = $(form).find('select').val();
          $(form).closest('[data-price-id]').find('.value [name="store[free-price]"]').prop('readonly', orig > 0);
          if ( json.success.message )
            LI.alert(json.success.message, 'success');
          if ( json.error.message )
          LI.alert(json.error.message, 'error');
        }
      });
      return false; // submit only w/ ajax
    });
  });
});
