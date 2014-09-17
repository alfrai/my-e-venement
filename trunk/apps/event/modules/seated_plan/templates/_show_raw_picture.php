  <a href="<?php echo url_for('seated_plan/getSeats?id='.$seated_plan->id.(isset($form->gauge_id) ? '&gauge_id='.$form->gauge_id : '').(isset($form->transaction_id) ? '&transaction_id='.$form->transaction_id : '')) ?>"
     class="picture seated-plan"
     style="background-color: <?php echo $seated_plan->background ?>;">
    <?php echo $seated_plan->getRaw('Picture')->getHtmlTag(array(
      'title' => $seated_plan->Picture,
      'width' => $seated_plan->ideal_width ? $seated_plan->ideal_width : ''
    )) ?>
    <?php use_stylesheet('/private/event-seated-plan?'.date('Ymd')) ?>
  </a>
  <?php if ( isset($form->transaction_id) ): ?>
  <?php use_helper('CrossAppLink') ?>
  <form action="<?php echo cross_app_url_for('tck', 'ticket/resetASeat?id='.$form->transaction_id) ?>" class="reset-a-seat" method="get" style="display: none;">
    <input type="hidden" name="ticket[_csrf_token]" value="<?php $f = new sfForm; echo $f->getCSRFToken() ?>" />
    <input type="hidden" name="ticket[numerotation]" value="" />
    <input type="hidden" name="ticket[gauge_id]" value="<?php echo $form->gauge_id ?>" />
    <span class="confirm"><?php echo __('Are you sure?',null,'sf_admin') ?></span>
  </form>
  <?php endif ?>
