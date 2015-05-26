<div class="sf_admin_form_row sf_admin_text sf_admin_form_field_link_to_contact">
<a
  target="_blank"
  data-href="<?php echo cross_app_url_for('rp', 'professional/show?id=_YOUR_ID_HERE_', true) ?>"
  data-replace="_YOUR_ID_HERE_"
  href="#"
>
  <span class="ui-icon ui-icon-person"></span>
  <span class="name"></span>
</a>
<script type="text/javascript"><!--
  $(document).ready(function(){
    $('.sf_admin_form_field_link_to_contact').hide();
    $('[name="contact_entry_new[professional_id]"]').change(function(){
      var val = parseInt($(this).val(),10);
      var a = $('.sf_admin_form_field_link_to_contact a');
      if ( val > 0 )
      {
        a.prop('href', a.attr('data-href').replace(a.attr('data-replace'), val));
        a.closest('.sf_admin_form_row').show();
      }
      else
        a.closest('.sf_admin_form_row').hide();
    }).change();
  });
--></script>
</div>
