  <?php if ( $sp = $manifestation->Location->getWorkspaceSeatedPlan($gauge->workspace_id) ): ?>
    <?php $conf = sfConfig::get('app_tickets_vel', array()) ?>
    <?php if ( isset($conf['full_seating_by_customer']) && $conf['full_seating_by_customer'] ): ?>
    <?php use_stylesheet('event-seated-plan') ?>
    <?php use_javascript('event-seated-plan') ?>
    <a href="<?php echo url_for('seats/index?id='.$sp->id.(isset($gauge->id) ? '&gauge_id='.$gauge->id : '')) ?>"
       class="picture seated-plan"
       style="background-color: <?php echo $sp->background ?>;"
    >
      <?php echo $sp->getRaw('Picture')->getHtmlTag(array('title' => $sp->Picture, 'width' => $sp->ideal_width)) ?>
    </a>
    <?php else: ?>
    <div class="picture">
      <p><a href="#" onclick="javascript: $(this).closest('.picture').find('.seated-plan').slideToggle('medium'); $(this).toggleClass('opened'); return false;"><?php echo __('Display venue') ?></a></p>
      <p class="seated-plan"><?php echo $sp->getRawValue()->OnlinePicture->getHtmlTag(array('app' => 'pub', 'title' => $gauge->Workspace)) ?></p>
    </div>
    <?php endif ?>
  <?php endif ?>
