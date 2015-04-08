<div class="sf_admin_form_row sf_admin_form_meta_event_id">
  <label for="meta_event_id"><?php echo __('Meta-event') ?></label>
  <div class="label ui-helper-clearfix"></div>
  <div class="widget">
    <?php $widget = new sfWidgetFormDoctrineChoice(array(
      'model' => 'MetaEvent',
      'order_by' => array('name', ''),
      'add_empty' => true,
    )) ?>
    <?php echo $widget->render('meta_event_id') ?>
    <a href="<?php echo cross_app_url_for('event', 'event/ajax?meta_event_id=CHANGE_ME_ID') ?>"
       data-replace="CHANGE_ME_ID"
       data-max="1000"
    ></a>
    <script type="text/javascript">
      $(document).ready(function(){
        $('.sf_admin_form_field_event_id select option').addClass('orig');
        
        $('.sf_admin_form_meta_event_id select').change(function(){
          // no meta event
          if ( !$(this).val() )
          {
            $('.sf_admin_form_field_event_id select option:not(.orig)').remove();
            $('.sf_admin_form_field_event_id select option.orig').show();
            return;
          }
          
          // a meta event is specified
          var url = $('.sf_admin_form_meta_event_id a').prop('href')
            .replace($('.sf_admin_form_meta_event_id a').attr('data-replace'), $(this).val());
          $.ajax({
            type: 'get',
            url: url,
            data: { limit: $('.sf_admin_form_meta_event_id a').attr('data-max') },
            success: function(data){
              $('.sf_admin_form_field_event_id select option.orig:not(:first-child)')
                .hide();
              $.each(data, function(id, name){
                $('<option></option>').val(id).text(name)
                  .appendTo($('.sf_admin_form_field_event_id select'));
              });
            }
          });
        });
      });
    </script>
  </div>
</div>
