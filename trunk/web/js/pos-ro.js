// the global var that can be used everywhere as a "root"
if ( LI == undefined )
  var LI = {};
if ( LI.posAfterRendering == undefined )
  LI.posAfterRendering = [];

LI.posAfterRendering.push(function(){
  $('.sf_admin_form').find('button, input, select').prop('readonly', true);
  $('.sf_admin_form').find('.sf_admin_form_field_picture_del, .li-delete, .li-new-declination .fg-button').remove();
  $('.sf_admin_form form').submit(function(){ return false; });
  
  setTimeout(function(){
    $(tinymce.editors).each(function(){
      this.getBody().setAttribute('contenteditable', false);
    });
  },2000);
});
