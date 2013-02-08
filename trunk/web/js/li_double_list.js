var liDoubleList =
{
  init: function(id, className)
  {
    form = liDoubleList.get_current_form(id);
    $(form).submit(function(){
      liDoubleList.submit(form, className);
    });
  },

  move: function(jqsrc, jqdest)
  {
    jqdest.prepend(jqsrc.find('option:selected').removeAttr('selected'));
  },

  submit: function(form, className)
  {
    $(form).find('select[multiple] option').attr('selected',true);
  },

  get_current_form: function(el)
  {
    if ("form" != el.tagName.toLowerCase())
      return $(el).closest('form').get(0);
    return el;
  }
};
