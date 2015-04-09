$(document).ready(function(){
  $('form.pub-survey').submit(function(){
    var form = this;
    $.ajax({
      type: 'GET', //$(this).prop('method'),
      url: $(this).prop('action'),
      data: $(this).serialize(),
      success: function(data){
        data = $.parseHTML(data);
        if ( $(data).find('.error_list').length > 0 ) // some errors
        {
          var msg = [];
          $(data).find('.error_list li').each(function(){
            msg.push($(this).closest('tr').find('th').text()+' '+$(this).text());
          });
          LI.alert(msg.join('<br/>'));
        }
        else // everything's ok
        {
          $(form).slideUp(function(){
            $(this).remove();
            
            // continue the process if it was the last form to fill in
            if ( $('form.pub-survey').length == 0 )
              window.location = $('.survey-next').prop('href');
          });
        }
      }
    });
    return false;
  });
});
