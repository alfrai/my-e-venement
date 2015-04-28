var liDoubleList =
{
  init: function(id, className)
  {
    form = liDoubleList.get_current_form(id);
    $(form).submit(function(){
      liDoubleList.submit(this, className);
    });
  },

  move: function(jqsrc, jqdest)
  {
    jqdest.prepend(jqsrc.find('option:selected').removeAttr('selected'));
  },

  submit: function(form, className)
  {
    $(form).find(str = 'select[multiple].'+className+' option').prop('selected',true);
  },

  get_current_form: function(el)
  {
    if ("form" == el.tagName.toLowerCase())
      return el;
    return $(el).closest('form').get(0);
  }
};
