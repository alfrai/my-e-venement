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
          <a class="close"
             href="#"
             onclick="javascript: $(this).closest('.seated-plan-parent').closest('.gauge').removeClass('active'); console.error('close'); return false;"
             title="<?php echo __('Close') ?>"
          ></a>
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
             onclick="javascript: var plan = $(this).closest('.seated-plan-parent'); LI.seatedPlanMoreDataInitialization($(this).prop('href'), true, plan); return false;"
          >
            <?php echo __('Debts') ?>
          </a>
          <form class="groups"
             method="get"
             action="<?php echo url_for('seated_plan/getGroup') ?>"
             target="_blank"
             onsubmit="javascript: if ( $(this).find('[name=group_id]').val() == '0' ) { $(this).find('option').css('background-color', 'transparent'); $(this).closest('.seated-plan-parent').find('.more-data.group').remove(); return false; } var plan = $(this).closest('.seated-plan-parent'); LI.seatedPlanMoreDataInitialization($(this).prop('action')+'?'+$(this).serialize(), true, plan); return false;"
          >
            <input type="hidden" name="id" value="<?php echo $seated_plan->id ?>" />
            <?php if ( isset($ids) ): ?>
            <?php foreach ( $ids as $id ): ?>
            <input type="hidden" name="gauges_list[]" value="<?php echo $id ?>" />
            <?php endforeach ?>
            <?php else: ?>
            <input type="hidden" name="gauges_list[]" value="<?php echo $gauge->id ?>" />
            <?php endif ?>
            <select name="group_id" onchange="javascript: $(this).closest('form').submit();">
              <option value="0">--<?php echo __('Clear groups') ?>--</option>
              <?php foreach ( Doctrine::getTable('Group')->createQuery('g')->orderBy('u.id IS NULL DESC, u.username, name')->execute() as $group ): ?>
                <option value="<?php echo $group->id ?>"><?php echo $group ?></option>
              <?php endforeach ?>
            </select>
          </form>
          <form class="transactions"
             method="get"
             action="<?php echo url_for('seated_plan/getTransaction') ?>"
             target="_blank"
             onsubmit="javascript: if ( $(this).find('[name=transaction_id]').val() == '0' ) { $(this).find('option').css('background-color', 'transparent'); $(this).closest('.seated-plan-parent').find('.more-data.transaction').remove(); return false; } var plan = $(this).closest('.seated-plan-parent'); LI.seatedPlanMoreDataInitialization($(this).prop('action')+'?'+$(this).serialize(), true, plan); return false;"
          >
            <input type="hidden" name="id" value="<?php echo $seated_plan->id ?>" />
            <?php if ( isset($ids) ): ?>
            <?php foreach ( $ids as $id ): ?>
            <input type="hidden" name="gauges_list[]" value="<?php echo $id ?>" />
            <?php endforeach ?>
            <?php else: ?>
            <input type="hidden" name="gauges_list[]" value="<?php echo $gauge->id ?>" />
            <?php endif ?>
            <select name="transaction_id" onchange="javascript: $(this).closest('form').submit();">
              <option value="0">--<?php echo __('Clear transactions') ?>--</option>
              <?php foreach ( Doctrine::getTable('Transaction')->createQuery('t')->andWhere('tck.seat_id IS NOT NULL')->groupBy($group = 't.id, t.contact_id, t.professional_id')->having('count(tck.id) > ?', 1)->select($group)->orderBy('t.id DESC')->execute() as $transaction ): ?>
                <option value="<?php echo $transaction->id ?>">
                  #<?php echo $transaction->id ?>
                  <?php if ( $transaction->contact_id ): ?>
                    <?php echo $transaction->Contact ?>
                  <?php endif ?>
                </option>
              <?php endforeach ?>
            </select>
          </form>
          
          <?php use_helper('CrossAppLink') ?>
          <a href="<?php echo cross_app_url_for('tck', 'transaction/find') ?>" class="transaction" style="display: none"></a>
