$(document).ready(function(){
  $('#member-cards').submit(function(){
    if ( parseInt($(this).find('[name=qty]').val()) > 1 && !$(this).find('[name="duplicate"]').is(':checked') )
      return confirm($('#i18n .confirm').text());
  });
});
