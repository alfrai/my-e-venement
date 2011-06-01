<div class="sf_admin_form_row sf_admin_text sf_admin_form_field_cities">
  <select name="cities" size="3">
    <option></option>
  </select>
  <script type="text/javascript">
    $(document).ready(function(){
      $('#contact_postalcode').keyup(function(e){
        if ( $(this).val().length > 3 )
        {
          $.getJSON('<?php echo cross_app_url_for('rp','postalcode/ajax') ?>?q='+$(this).val(),function(json){
            $('.sf_admin_form_field_cities select').html('');
            $.each(json, function(key, val) {
              $('.sf_admin_form_field_cities select')
                .append('<option value="'+key+'">'+val+'</option>');
            });
          });
        }
      });
      $('.sf_admin_form_field_cities select').change(function(){
        $('#contact_city').val($(this).val());
      });
    });
  </script>
</div>
