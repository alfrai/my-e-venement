<div class="links_links">
  <a href="<?php echo url_for('seated_plan/linksRemove?id='.$form->getObject()->id) ?>" class="links_exceptions_to_remove">
    <?php echo __('Link(s) removed') ?>
  </a>
  <a href="<?php echo url_for('seated_plan/linksAdd?id='.$form->getObject()->id) ?>" class="links_exceptions_to_add">
    <?php echo __('Link(s) created') ?>
  </a>
  <a href="<?php echo url_for('seated_plan/linksBuild?id='.$form->getObject()->id) ?>" class="links_build">
    <?php echo __('%%qty%% links between seats have been created') ?>
  </a>
  <a href="<?php echo url_for('seated_plan/linksClear?id='.$form->getObject()->id) ?>" class="links_clear">
    <?php echo __('Links between seats have been cleared') ?>
  </a>
</div>

