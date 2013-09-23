<div class="sf_admin_edit ui-widget ui-widget-content ui-corner-all gmap">
  <div class="ui-widget-header ui-corner-all fg-toolbar">
    <h2><?php echo __('Homonyms') ?></h2>
  </div>
<div class="duplicates"></div>
<script type="text/javascript">
    $(document).ready(function(){
      $('.sf_admin_form_field_name input,.sf_admin_form_field_firstname input').change(function(){
        $('#more .duplicates').html('<ul></ul>');
        if ( $.trim($('.sf_admin_form_field_name input').val()) != '' || $.trim($('.sf_admin_form_field_firstname input').val()) != '' )
        {
          search = $.trim($('.sf_admin_form_field_name input').val())+' '+$.trim($('.sf_admin_form_field_firstname input').val());
          $.getJSON('<?php echo url_for('contact/ajax')?>?limit=10&q='+encodeURI($.trim(search.toLowerCase())),function(json){
            $.each(json, function(key, val) {
              $('#more .duplicates ul')
               .append('<li><a href="<?php echo url_for('contact/show') ?>?id='+key+'">'+val+'</a></li>');
            });
          });
        }
      });
    });
</script>
</div>
