<?php use_stylesheet('stats-criterias') ?>
  <div>
    <?php echo $form->renderHiddenFields() ?>
  </div>
  <ul>
    <?php if ( isset($form['by_tickets']) ): ?>
    <li class="by_tickets">
      <?php echo $form['by_tickets']->renderLabel() ?>
      <span><?php echo $form['by_tickets'] ?></span>
    </li>
    <?php endif ?>
    <?php if ( isset($form['dates']) ): ?>
    <li class="dates">
      <?php echo $form['dates']->renderLabel() ?>
      <span><?php echo $form['dates'] ?></span>
    </li>
    <?php endif ?>
    <?php if ( isset($form['workspaces_list']) ): ?>
    <li class="workspaces_list">
      <?php echo $form['workspaces_list']->renderLabel() ?>
      <span><?php echo $form['workspaces_list'] ?></span>
    </li>
    <?php endif ?>
    <?php if ( isset($form['meta_events_list']) ): ?>
    <li class="meta_events_list">
      <?php echo $form['meta_events_list']->renderLabel() ?>
      <span><?php echo $form['meta_events_list'] ?></span>
    </li>
    <?php endif ?>
    <?php if ( isset($form['events_list']) ): ?>
    <?php use_javascript('/sfAdminThemejRollerPlugin/js/jquery-ui.custom.min.js') ?>
    <?php use_javascript('/sfFormExtraPlugin/js/jquery.autocompleter.js') ?>
    <?php use_javascript('/cxFormExtraPlugin/js/cx_open_list.js') ?>
    <?php use_stylesheet('/sfFormExtraPlugin/css/jquery.autocompleter.css') ?>
    <li class="events_list">
      <?php echo $form['events_list']->renderLabel() ?>
      <span><?php echo $form['events_list'] ?></span>
    </li>
    <?php endif ?>
    <?php if ( isset($form['manifestations_list']) ): ?>
    <?php use_javascript('/sfAdminThemejRollerPlugin/js/jquery-ui.custom.min.js') ?>
    <?php use_javascript('/sfFormExtraPlugin/js/jquery.autocompleter.js') ?>
    <?php use_javascript('/cxFormExtraPlugin/js/cx_open_list.js') ?>
    <?php use_stylesheet('/sfFormExtraPlugin/css/jquery.autocompleter.css') ?>
    <li class="manifestations_list">
      <?php echo $form['manifestations_list']->renderLabel() ?>
      <span><?php echo $form['manifestations_list'] ?></span>
    </li>
    <?php endif ?>
    <?php if ( isset($form['with_contact']) ): ?>
    <li class="users">
      <?php echo $form['with_contact']->renderLabel() ?>
      <span><?php echo $form['with_contact'] ?></span>
    </li>
    <?php endif ?>
    <?php if ( isset($form['users']) ): ?>
    <li class="users">
      <?php echo $form['users']->renderLabel() ?>
      <span><?php echo $form['users'] ?></span>
    </li>
    <?php endif ?>
    <?php if ( isset($form['interval']) ): ?>
    <li class="interval">
      <?php echo $form['interval']->renderLabel() ?>
      <span><?php echo $form['interval'] ?> <?php echo __('day(s)') ?></span>
    </li>
    <?php endif ?>
    <?php if ( isset($form['groups_list']) ): ?>
    <li class="interval">
      <?php echo $form['groups_list']->renderLabel() ?>
      <span><?php echo $form['groups_list'] ?></span>
    </li>
    <?php endif ?>
    <li class="submit">
      <span><input type="submit" name="s" value="ok" /></span>
    </li>
  </ul>
