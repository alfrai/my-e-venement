          <a class="occupation"
             href="<?php echo url_for('seated_plan/getSeats?id='.$seated_plan->id.'&gauge_id='.$gauge->id) ?>"
             onclick="javascript: var plan = $(this).closest('.seated-plan-parent').find('.seated-plan'); LI.seatedPlanLoadData($(this).prop('href'), plan); return false;"
          >
            <?php echo __('Show occupation') ?>
          </a>
          <?php use_javascript('event-seated-plan-more-data') ?>
          <a class="shortnames"
             href="<?php echo url_for('seated_plan/getShortnames?id='.$seated_plan->id.'&gauge_id='.$gauge->id) ?>"
             onclick="javascript: var plan = $(this).closest('.seated-plan-parent'); LI.seatedPlanMoreDataInitialization($(this).prop('href'), true, plan); return false;"
          >
            <?php echo __('Show short names') ?>
          </a>
          <a class="ranks"
             href="<?php echo url_for('seated_plan/getRanks?id='.$seated_plan->id) ?>"
             onclick="javascript: var plan = $(this).closest('.seated-plan-parent'); LI.seatedPlanMoreDataInitialization($(this).prop('href'), true, plan); return false;"
          >
            <?php echo __('Show ranks') ?>
          </a>
