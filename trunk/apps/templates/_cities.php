<div class="sf_admin_form_row sf_admin_text sf_admin_form_field_cities">
  <select name="cities" size="3">
    <option></option>
  </select>
  <script type="text/javascript">
    $(document).ready(function(){
      $('.sf_admin_form_field_cities select').keyup(function(e){
        if ( e.which == 13 )
          $('.sf_admin_form_field_country input[type=text]').focus();
      });
      $('.sf_admin_form_field_postalcode input, .tdp-postalcode input').keyup(function(e){
        if ( $(this).val().length > 2 )
        {
          $.getJSON('<?php echo cross_app_url_for('rp','postalcode/ajax') ?>?q='+$(this).val(),function(json){
            $('.sf_admin_form_field_cities select').html('');
            $.each(json, function(key, val) {
              $('.sf_admin_form_field_cities select')
                .append('<option value="'+key+'">'+val+'</option>')
                .find('option:first-child').attr('selected',true);
            });
          });
        }
        if ( e.which == 40 )
          $('.sf_admin_form_field_cities select').focus();
      });
      $('.sf_admin_form_field_cities select').change(function(){
        $('.sf_admin_form_field_city input, .tdp-city input').val($(this).val());
      });
    });
  </script>
</div>
