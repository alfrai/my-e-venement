<span class="arrow"></span>
<a href="#<?php echo isset($anchor) ? $anchor : '' ?>" title="<?php echo __('Chart') ?>" class="chart ui-corner-all"><span><?php echo __('Chart') ?></span></a>
<?php if ( $sf_user->hasCredential('stats-csv') ): ?>
<a target="_blank" href="<?php echo url_for($sf_context->getModuleName().'/csv?'.(isset($id) ? 'id='.$id : '').(isset($type) ? '&type='.$type : '')) ?>" title="<?php echo __('Record') ?>" class="record ui-corner-all"><span><?php echo __('Record') ?></span></a>
<?php endif ?>
