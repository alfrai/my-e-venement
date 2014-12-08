<?php
  if ( isset($gauge) && $gauge->getRawValue() instanceof Gauge )
    $param = 'gauge_id='.$gauge->id;
  elseif ( isset($gauges) && count($gauges) > 0 )
  {
    $ids = array();
    foreach ( $gauges as $gauge )
      $ids[] = $gauge->id;
    $param = 'gauges_list[]='.implode('&amp;gauges_list[]=', $ids);
  }
?>
          <a class="occupation"
             href="<?php echo url_for('seated_plan/getSeats?id='.$seated_plan->id) ?>?<?php echo $param ?>"
             onclick="javascript: var plan = $(this).closest('.seated-plan-parent').find('.seated-plan'); LI.seatedPlanLoadData($(this).prop('href'), plan); return false;"
          >
            <?php echo __('Reload') ?>
          </a>
          <?php use_javascript('event-seated-plan-more-data') ?>
          <a class="shortnames"
             href="<?php echo url_for('seated_plan/getShortnames?id='.$seated_plan->id) ?>?<?php echo $param ?>"
             onclick="javascript: var plan = $(this).closest('.seated-plan-parent'); LI.seatedPlanMoreDataInitialization($(this).prop('href'), true, plan); return false;"
          >
            <?php echo __('Spectators') ?>
          </a>
          <a class="ranks"
             href="<?php echo url_for('seated_plan/getRanks?id='.$seated_plan->id) ?>"
             onclick="javascript: var plan = $(this).closest('.seated-plan-parent'); LI.seatedPlanMoreDataInitialization($(this).prop('href'), true, plan); return false;"
          >
            <?php echo __('Ranks') ?>
          </a>
          <a class="debts"
             href="<?php echo url_for('seated_plan/getDebts?id='.$seated_plan->id) ?>?<?php echo $param ?>"
             onclick="javascript: var plan = $(this).closest('.seated-plan-parent'); LI.seatedPlanMoreDataInitialization($(this).prop('href'), plan); return false;"
          >
            <?php echo __('Debts') ?>
          </a>
          <form class="groups"
             method="get"
             action="<?php echo url_for('seated_plan/getGroup') ?>"
             target="_blank"
             onsubmit="javascript: if ( $(this).find('[name=transaction_group]').val() == 'cxsdf') return false; var plan = $(this).closest('.seated-plan-parent'); LI.seatedPlanMoreDataInitialization($(this).prop('action')+'?'+$(this).serialize(), plan); return false;"
          >
            <input type="hidden" name="id" value="<?php echo $seated_plan->id ?>" />
            <?php foreach ( $ids as $id ): ?>
            <input type="hidden" name="gauges_list[]" value="<?php echo $id ?>" />
            <?php endforeach ?>
            <select name="group_id" onchange="javascript: $(this).closest('form').submit();">
              <option>--<?php echo __('Groups') ?>--</option>
              <?php foreach ( Doctrine::getTable('Group')->createQuery('g')->orderBy('u.id IS NULL DESC, u.username, name')->execute() as $group ): ?>
                <option value="<?php echo $group->id ?>"><?php echo $group ?></option>
              <?php endforeach ?>
            </select>
          </form>
