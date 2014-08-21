<?php if ( $sp = $manifestation->Location->getWorkspaceSeatedPlan($gauge->workspace_id) ): ?>
  <?php $conf = sfConfig::get('app_tickets_vel', array()) ?>
  <?php if ( isset($conf['full_seating_by_customer']) && $conf['full_seating_by_customer'] ): ?>
  <?php use_stylesheet('event-seated-plan?'.date('Ymd')) ?>
  <?php use_javascript('event-seated-plan?'.date('Ymd')) ?>
  <div class="full-seating">
    <a href="<?php echo url_for('seats/index?id='.$sp->id.($gauge->id ? '&gauge_id='.$gauge->id : '')) ?>"
       class="picture seated-plan on-demand" <?php if ( $gauge->id ): ?>id="seated-plan-gauge-<?php echo $gauge->id ?>"<?php endif ?>
       style="background-color: <?php echo $sp->background ?>;"
    >
      <?php echo $sp->getRaw('Picture')->getHtmlTag(array(
        'title' => $sp->Picture,
        'width' => $sp->ideal_width,
        'app'   => 'pub',
      )) ?>
    </a>
    <button
      name="load-data"
      class="load-data"
      onclick="javascript: LI.seatedPlanLoadData($(this).parent().find('.seated-plan').attr('data-href'), '#'+$(this).parent().find('.seated-plan').prop('id')); $(this).fadeOut(); return false;"
    >
      <?php echo __('Display free seats') ?>
    </button>
    <a href="<?php echo url_for('ticket/addSeat?id='.$gauge->id) ?>" class="add-seat"></a>
    <a href="<?php echo url_for('ticket/removeTicket?id='.$gauge->id) ?>" class="remove-ticket"></a>
  </div>
  <?php else: ?>
  <div class="picture">
    <p><a href="#" onclick="javascript: $(this).closest('.picture').find('.seated-plan').slideToggle('medium'); $(this).toggleClass('opened'); return false;"><?php echo __('Display venue') ?></a></p>
    <p class="seated-plan"><?php echo $sp->getRawValue()->OnlinePicture->getHtmlTag(array('app' => 'pub', 'title' => $gauge->Workspace)) ?></p>
  </div>
  <?php endif ?>
<?php endif ?>
