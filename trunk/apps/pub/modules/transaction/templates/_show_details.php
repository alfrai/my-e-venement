<ul>
  <?php $printed = false; foreach ( $transaction->Tickets as $ticket ) if ( !is_null($ticket->printed_at) || !is_null($ticket->integrated_at) ) { $printed = true; break; } ?>
  <?php if ( $printed ): ?>
	<li><?php echo __('Printed (event partially)') ?></li>
  <?php endif ?>
  <?php if ( $transaction->Order->count() > 0 ): ?>
  <li><?php echo __('Booked') ?></li>
  <?php endif ?>
  <?php $get_tickets = true ?>
  <?php if ( $transaction->Order->count() == 0 && !$printed ): ?>
  <?php $get_tickets = false ?>
  <li><?php echo __('In progress...') ?></li>
  <?php endif ?>
  <?php if ( $printed ): ?>
  <li>
  <?php if ( $transaction->Invoice->count() > 0 ): ?>
    <?php echo __('Invoice', null, 'li_accounting') ?> #<?php echo link_to(
    sfConfig::get('app_seller_invoice_prefix', '').$transaction->Invoice[0]->id,
    'transaction/invoice?id='.$transaction->id
    ) ?>
  <?php else: ?>
    <?php echo link_to(__('Generate an invoice'), 'transaction/invoice?id='.$transaction->id, array('onclick' => 'javascript: setTimeout(function(){ window.location.reload(); },3000)', 'target' => '_blank')) ?>
  <?php endif ?>
  </li>
  <?php endif ?>
  <?php if ( $get_tickets ): ?>
  <li>
    <?php echo __('Your tickets') ?>
    <?php echo link_to('PDF', 'transaction/tickets?id='.$transaction->id.'&format=pdf', array('class' => 'pdf')) ?>
    <?php $sf_context->getEventDispatcher()->notify(new sfEvent($this, 'pub.tickets_list_formats', array('transaction' => $transaction))) ?>
  </li>
  <?php endif ?>
</ul>
