<?php use_stylesheet('stats-criterias') ?>
  <div>
    <?php echo $form->renderHiddenFields() ?>
  </div>
  <ul>
    <li class="dates">
      <label for="dates"><?php echo __('Dates:') ?></label>
      <span><?php echo $form['dates'] ?></span>
    </li>
    <li class="workspaces_list">
      <label for="workspaces_list"><?php echo __('Workspaces:') ?></label>
      <span><?php echo $form['workspaces_list'] ?></span>
    </li>
    <li class="meta_events_list">
      <label for="meta_events_list"><?php echo __('Meta events:') ?></label>
      <span><?php echo $form['meta_events_list'] ?></span>
    </li>
    <li class="users">
      <label for="users"><?php echo __('Users:') ?></label>
      <span><?php echo $form['users'] ?></span>
    </li>
    <li class="submit">
      <span><input type="submit" name="s" value="ok" /></span>
    </li>
  </ul>
