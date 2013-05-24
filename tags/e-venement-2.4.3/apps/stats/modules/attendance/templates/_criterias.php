<?php use_stylesheet('stats-criterias') ?>
  <div>
    <?php echo $form->renderHiddenFields() ?>
  </div>
  <ul>
    <li class="dates">
      <label for="dates"><?php echo __('Dates:') ?></label>
      <span><?php echo $form['dates'] ?></span>
    </li>
    <?php if ( isset($form['workspaces_list']) ): ?>
    <li class="workspaces_list">
      <label for="workspaces_list"><?php echo __('Workspaces:') ?></label>
      <span><?php echo $form['workspaces_list'] ?></span>
    </li>
    <?php endif ?>
    <?php if ( isset($form['meta_events_list']) ): ?>
    <li class="meta_events_list">
      <label for="meta_events_list"><?php echo __('Meta events:') ?></label>
      <span><?php echo $form['meta_events_list'] ?></span>
    </li>
    <?php endif ?>
    <?php if ( isset($form['with_contact']) ): ?>
    <li class="users">
      <label for="users"><?php echo __('Tickets with contact:') ?></label>
      <span><?php echo $form['with_contact'] ?></span>
    </li>
    <?php endif ?>
    <?php if ( isset($form['users']) ): ?>
    <li class="users">
      <label for="users"><?php echo __('Users:') ?></label>
      <span><?php echo $form['users'] ?></span>
    </li>
    <?php endif ?>
    <?php if ( isset($form['interval']) ): ?>
    <li class="interval">
      <label for="interval"><?php echo __('Interval:') ?></label>
      <span><?php echo $form['interval'] ?> <?php echo __('day(s)') ?></span>
    </li>
    <?php endif ?>
    <li class="submit">
      <span><input type="submit" name="s" value="ok" /></span>
    </li>
  </ul>
