<?php if ( $sf_user->hasCredential('tck-transaction') ): ?>
<a style="display: inline-block;" class="fg-button-mini fg-button-left ui-state-default ui-priority-secondary" href="<?php echo url_for('summary/deleteDemands?id='.$transaction->id) ?>">
  <span class="ui-icon ui-icon-trash"></span>
</a>
<?php endif ?>
