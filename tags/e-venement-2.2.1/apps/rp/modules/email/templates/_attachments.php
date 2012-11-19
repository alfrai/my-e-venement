<div class="sf_admin_form_row sf_admin_text sf_admin_form_field_attachments">

<div class="label ui-helper-clearfix">
  <label for="email_attachments"><?php echo __('Attachments') ?>:</label>
</div>

<ul>
  <?php foreach ( $form->getObject()->Attachments as $att ): ?>
  <li class="attachment-<?php echo $att->id ?>">
    <a href="<?php echo dirname($_SERVER['SCRIPT_NAME']).$att->getWebUri() ?>" target="_blank"><?php echo $att ?></a>
    <a href="<?php echo url_for('email/deleteAttachment?id='.$form->getObject()->id.'&attachment_id='.$att->id) ?>" class="fg-button-mini fg-button ui-state-default fg-button-icon-left" onclick="javascript: $.get($(this).attr('href')); $(this).closest('li').remove(); return false;"><span class="ui-icon ui-icon-trash"></span>Supprimer</a>
  </li>
  <?php endforeach ?>
  <li class="attachment-new">
    <a href="<?php echo url_for('email/upload?id='.$form->getObject()->id) ?>"><?php echo __('Add an attachment ...') ?></a>
  </li>
</ul>

</div>
