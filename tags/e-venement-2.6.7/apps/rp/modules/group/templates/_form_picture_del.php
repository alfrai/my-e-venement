<?php if ( !$form->getObject()->Picture->isNew() ): ?>
<script type="text/javascript"><!--
$(document).ready(function(){
  $('.sf_admin_form_field_Picture').hide();
});

function form_picture_del(anchor)
{
  $.get($(anchor).prop('href'),function(data){
    $(anchor).closest('form').find('[name="group[Picture][id]"]').val('');
    $('#transition').hide();
    $('.sf_admin_form_field_Picture').fadeIn('slow');
    $('.sf_admin_form_field_picture_del').fadeOut('slow');
    $('.sf_admin_form_field_show_picture img').fadeOut('slow',function(){
      $(this).remove();
    });
  });
  
  return false;
}
--></script>
<a  title="<?php echo __('Delete',null,'sf_admin') ?>"
    class="fg-button-mini fg-button ui-state-default fg-button-icon-left ui-priority-secondary sf_admin_form_field_picture_del"
    href="<?php echo url_for('group/delPicture?id='.$form->getObject()->id) ?>"
    onclick="javascript: return form_picture_del(this);">
  <span class="ui-icon ui-icon-trash">&nbsp;</span>
  <?php echo __('Delete',null,'sf_admin') ?>
</a>
<?php endif ?>
