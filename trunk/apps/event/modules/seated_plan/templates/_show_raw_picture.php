  <a href="<?php echo url_for('seated_plan/getSeats?id='.$seated_plan->id.(isset($form->gauge_id) ? '&gauge_id='.$form->gauge_id : '').(isset($form->transaction_id) ? '&transaction_id='.$form->transaction_id : '')) ?>"
     class="picture seated-plan"
     style="background-color: <?php echo $seated_plan->background ?>;">
    <?php echo $seated_plan->getRaw('Picture')->getHtmlTag(array('title' => $seated_plan->Picture)) ?>
  </a>

