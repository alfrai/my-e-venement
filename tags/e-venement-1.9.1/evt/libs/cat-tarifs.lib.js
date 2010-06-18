$(document).ready(function(){
  $('.cat-tarifs table button.libelle').click(function(){
    $(this).parent().parent().parent().parent().toggleClass('show');
    return false;
  });
});
