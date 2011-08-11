<script type="text/javascript">
  $(document).ready(function(){
    $('#sf_admin_filter form').clone(true)
      .attr('id','integrated-filter')
      .appendTo('#sf_admin_header');
    $('#sf_admin_header #integrated-filter tr.sf_admin_form_row:not(.sf_admin_filter_field_groups_list)').remove();
    $('#sf_admin_header #integrated-filter .fieldset thead').remove();
    $('#sf_admin_header #integrated-filter tfoot').remove();
    $('#sf_admin_header #integrated-filter label').remove();
    $('#sf_admin_header #integrated-filter .fieldset tbody:empty').parent().parent().parent().parent().remove();
  });
</script>
<?php include_partial('global/list_header') ?>
