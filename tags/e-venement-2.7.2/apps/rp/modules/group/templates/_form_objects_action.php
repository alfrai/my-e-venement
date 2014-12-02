<div class="sf_admin_form_row sf_admin_form_direct_action_<?php echo $action ?> <?php echo $type ?>">

<label><?php echo $label ?>:</label>
<?php
  $widget = new sfWidgetFormDoctrineJQueryAutocompleter(array(
    'url' => url_for($type.'/ajax'),
    'model' => ucfirst($type),
  ));
  echo $widget->render($type.'s_'.$action);
?>
<button
  name="<?php echo $type ?>s_<?php echo $action ?>_submit"
  value="<?php echo url_for('group/member?id='.$form->getObject()->id) ?>"
  onclick="javascript: return group_<?php echo $type ?>s_<?php echo $action ?>(this);"
><?php echo $action_label ?></button>

<script type="text/javascript"><!--
  function group_<?php echo $type ?>s_<?php echo $action ?>(button)
  {
    url = $(button).val();
    $.get(url, {
      object_id: $(button).closest('form').find('input[name="<?php echo $type ?>s_<?php echo $action ?>"]').val(),
      type: '<?php echo $type ?>',
      modifier: '<?php echo $action ?>',
      _csrf_token: '<?php echo $form->getCSRFToken() ?>'
    }, function(data){
      
      // DISPLAYING FLASHES
      if ( data['success'] )
      {
        $('form input[name=<?php echo $type ?>s_<?php echo $action ?>]').val('');
        $('form input[name=autocomplete_<?php echo $type ?>s_<?php echo $action ?>]').val('');
        $('.sf_admin_flashes').append('<div style="display:none" class="success ui-state-success ui-corner-all"><span class="ui-icon ui-icon-info floatleft"></span>&nbsp;'+data['success']+'</div>');
      }
      if ( data['error'] )
        $('.sf_admin_flashes').append('<div style="display: none" class="error ui-state-error ui-corner-all"><span class="ui-icon ui-icon-alert floatleft"></span>&nbsp;'+data['error']+'</div>');
      
      // FOCUS ON AUTOCOMPLETE FIELD
      $('form input[name=autocomplete_<?php echo $type ?>s_<?php echo $action ?>]').focus();
      
      // REMOVING FLASHES
      $('.sf_admin_flashes > div').fadeIn('slow',function(){
        setTimeout(function(){
          $('.sf_admin_flashes > div').fadeOut('slow',function(){
            $(this).remove();
          });
        },3500);
      });
    }, 'json');
    return false;
  }
  
  $(document).ready(function(){
    $('form input[name=autocomplete_<?php echo $type ?>s_<?php echo $action ?>]').keypress(function(e){
      if ( e.which == 13 )
      {
        $(this).closest('form').find('button[name=<?php echo $type ?>s_<?php echo $action ?>_submit]').click();
        return false;
      }
    });
  });
--></script>

</div>
