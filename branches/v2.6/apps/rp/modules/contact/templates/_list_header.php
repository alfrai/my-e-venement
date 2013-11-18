<script type="text/javascript">
  $(document).ready(function(){
    $('#sf_admin_filter form').clone(true)
      .attr('id','list-integrated-filter')
      .addClass('fg-button ui-widget ui-corner-all ui-state-default')
      .appendTo('#sf_admin_header');
    $('#sf_admin_filter input[name="contact_filters[_csrf_token]"]').clone(true)
      .prependTo('#list-integrated-filter');
    $('#list-integrated-filter').addClass('no-user-select');
    $('#sf_admin_header #list-integrated-filter .ui-widget').removeClass('ui-widget').removeClass('ui-widget-content');
    $('#sf_admin_header #list-integrated-filter tr.sf_admin_form_row:not(.sf_admin_filter_field_groups_list)').remove();
    $('#sf_admin_header #list-integrated-filter .fieldset thead').remove();
    $('#sf_admin_header #list-integrated-filter tfoot').remove();
    $('#sf_admin_header #list-integrated-filter label').remove();
    $('#sf_admin_header #list-integrated-filter .fieldset tbody:empty').parent().parent().parent().parent().remove();
    $('#sf_admin_header #list-integrated-filter .fieldset table').prepend(
      '<thead><tr><td colspan="2"><h2><?php echo __('Groups') ?>:</h2></td></tr></thead>'
    );
    $('#sf_admin_header #list-integrated-filter .fieldset tbody').hide();
    $('#sf_admin_header #list-integrated-filter h2').click(function(){
      $(this).parent().parent().parent().parent().find('tbody').slideToggle('slow');
    });
    $('#sf_admin_header #list-integrated-filter select').change(function(){
      $('#sf_admin_filter select[name="contact_filters[groups_list][]"] option').removeAttr('selected');
      filter_submit_timeout($(this).find('option:selected'));
    });
    
    <?php if ( !sfConfig::has('app_cards_enable') ): ?>
    $('#sf_admin_filter .sf_admin_filter_field_member_cards').parent().parent().parent().parent().remove();
    <?php endif ?>
  });
  
  function filter_submit_timeout(options)
  {
    setTimeout(function(){
      if ( options.length == 1 )
      if ( options.val() != $('#sf_admin_header #list-integrated-filter select option:selected').val() )
        return false;
      
      if ( options.length != $('#sf_admin_header #list-integrated-filter select option:selected').length )
        return false;
      
      options.each(function(){
        $('#sf_admin_filter select[name="contact_filters[groups_list][]"] option[value="'+$(this).val()+'"]').attr('selected','selected');
      });
      $.post($('#sf_admin_filter form').attr('action'),$('#sf_admin_filter form').serialize(),function(data){
        $('.sf_admin_list').replaceWith($($.parseHTML(data)).find('.sf_admin_list'));
      });
    },1000);
  }
</script>
<?php include_partial('global/list_header') ?>
