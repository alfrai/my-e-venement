$(document).ready(function(){
  var elts = 'input[type=text], textarea';
  $(elts).each(function(){
    if ( $(this).parent().find('label').length > 0 )
      return;
    
    // cleaning dust
    $(this).parent().find('.tdp-subtitle').remove();
    
    // the "label"
    var title = $(this).closest('[title]').prop('title');
    
    // the content
    $('<span></span>')
      .addClass('tdp-subtitle')
      .html(title)
      .prependTo($(this).parent())
      .click(function(){
        $(this).parent().find(elts).focus();
      })
      .css('width',$(this).width())
      .css('height',$(this).height())
    ;
    
    // the behaviour
    $(this).focus(function(){
      $(this).parent().find('.tdp-subtitle').css('z-index',-1);
    }).focusout(function(){
      if ( $.trim($(this).val()) == '' )
        $(this).parent().find('.tdp-subtitle').css('z-index',0);
    }).focus().focusout();
  });
});
