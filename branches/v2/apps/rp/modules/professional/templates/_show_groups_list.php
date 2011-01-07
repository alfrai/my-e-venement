<div class="sf_admin_form_row">
<label><?php echo __('Groups list') ?>:</label>
<ul class="show_groups">
  <?php foreach ( $professional->Groups as $group ): ?>
  <li><a href="<?php echo url_for('group/show?id='.$group->id) ?>"><?php echo $group ?></a></li>
  <?php endforeach ?>
</ul>
</div>
