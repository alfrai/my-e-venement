  <a href="<?php echo url_for('seated_plan/getSeats?id='.$seated_plan->id) ?>" class="picture seated-plan" style="background-color: <?php echo $seated_plan->background ?>;">
    <?php echo $seated_plan->getRaw('Picture')->getHtmlTag(array('title' => $seated_plan->Picture)) ?>
  </a>

