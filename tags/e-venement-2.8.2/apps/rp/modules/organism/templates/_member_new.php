<div class="sf_admin_edit ui-widget ui-widget-content ui-corner-all professional new">
  <div class="ui-widget-header ui-corner-all fg-toolbar"><h2><?php echo __('Link an existing contact to this organism') ?></h2></div>
  <div id="professional-new" class="sf_admin_form"></div>
  <script type="text/javascript">
    function organism_integrate_pro(data)
    {
      // data
      data = $.parseHTML(data);
      $('#professional-new').append($(data).find('#sf_admin_content'));
      
      // display
      $('#professional-new form').hide();
      $('#professional-new #sf_admin_form_tab_menu').addClass('sf_admin_form_tab_menu').removeAttr('id');
      
      // toggle
      $('#more .professional.new h2').click(function(){
        $('#professional-new form').slideToggle('slow');
      });
      
      // values
      $('#professional-new .sf_admin_form_field_organism_id input[name="professional[organism_id]"]').val($('#organism_id').val());
      $('#professional-new .sf_admin_form_field_organism_id').hide();
      
      // form
      $('#professional-new form').submit(function(){
        $.post($(this).attr('action'),$(this).serialize(),function(data){
          data = $.parseHTML(data);
          $(data).find('#sf_admin_content').prepend($(data).find('.sf_admin_flashes'));
          if ( $(data).find('.sf_admin_flashes .error').length > 0 )
          {
            $('#professional-new #sf_admin_content').remove();
            organism_integrate_pro(data);
          }
          else
          {
            $.get('<?php echo url_for('professional/new') ?>',function(data){
              $('#professional-new #sf_admin_content').remove();
              organism_integrate_pro(data);
            });
            $.get('<?php echo url_for('organism/edit?id='.$form->getObject()->id) ?>',function(data){
              $(data).find('#more .members .contact').hide();
              $('#more .members').replaceWith($(data).find('#more .members'));
              
              $('#more .members h2').click(function(){
                $('#more .members .contacts').slideToggle('slow');
              });
              $('#more .members .contacts').slideDown('slow');
            });
          }
        });
        return false;
      });
      
      // autocomplete
      jQuery('#professional-new input[name="autocomplete_professional[contact_id]"]')
        .autocomplete('<?php echo url_for('contact/ajax') ?>', jQuery.extend({}, {
          dataType: 'json',
          parse:    function(data) {
            var parsed = [];
            for (key in data) {
              parsed[parsed.length] = { data: [ data[key], key ], value: data[key], result: data[key] };
            }
            return parsed;
          }
        }, { }))
        .result(function(event, data) { jQuery('#professional-new input[name="professional[contact_id]"]').val(data[1]); });
    }
    
    $(document).ready(function(){
      $.get('<?php echo url_for('professional/new') ?>',function(data){
        organism_integrate_pro(data);
      });
    });
  </script>
</div>
