<?php use_helper('Number') ?>
<?php $contact = $form->getObject(); ?>

<table>
  <tbody>

<?php
  $pros = array(NULL);
  foreach ( $contact->Professionals as $pro )
    $pros[] = $pro;
  
  foreach ( $pros as $pro )
  {
  // hack for optimization
  $q = Doctrine_Query::create()
    ->from('MetaEvent me')
    ->leftJoin('me.Events e')
    ->leftJoin('e.Manifestations m')
    ->leftJoin('m.Tickets tck')
    ->leftJoin('tck.Transaction t')
    ->where('t.contact_id = ?',$contact->id)
    ->andWhere('tck.printed = TRUE OR tck.integrated = TRUE')
    ->andWhere('tck.cancelling IS NULL AND tck.duplicating IS NULL')
    ->andWhere('tck.id NOT IN (SELECT tck2.cancelling FROM Ticket tck2 WHERE tck2.cancelling IS NOT NULL)')
    ->orderBy('me.name, e.name, m.happens_at DESC, t.id');

  if ( is_null($pro) )
    $q->andWhere('t.professional_id IS NULL');
  else
    $q->andWhere('t.professional_id = ?',$pro->id);

  $meta_events = $q->execute();
?>
  <?php if ( $meta_events->count() > 0 ): ?>
    <tr class="pro" title="<?php echo $pro ?>">
      <td class="name" colspan="4"><span><?php echo $pro ?></span></td>
    </tr>
  <?php endif ?>
  <?php foreach ( $meta_events as $me ): ?>
    <tr class="meta_event">
      <td class="name" colspan="4"><span><?php echo $me ?></span></td>
    </tr>
    <?php foreach( $me->Events as $event ): ?>
    <tr class="event">
      <td class="name" colspan="4" title="<?php echo $event ?>"><span><?php echo cross_app_link_to($event,'event','event/show?id='.$event->id) ?></span></td>
    </tr>
    <?php foreach( $event->Manifestations as $manif ): ?>
    <tr class="manif">
      <td class="name"><span><?php echo cross_app_link_to(format_date($manif->happens_at),'event','manifestation/show?id='.$manif->id) ?></span></td>
      <td class="tickets_nb"><span><?php echo __('%%nb%% ticket(s)',array('%%nb%%' => $manif->Tickets->count())) ?></span></td>
      <td class="tickets_value"><span><?php $value = 0; foreach ( $manif->Tickets as $ticket ) $value += $ticket->value; echo format_currency($value,'€'); ?></span></td>
      <td class="transactions"><?php $arr = array(); foreach ( $manif->Tickets as $ticket ) $arr[$ticket->transaction_id] = '#'.cross_app_link_to($ticket->transaction_id,'tck','ticket/sell?id='.$ticket->transaction_id); echo implode(', ',$arr) ?></span></td>
    </tr>
    <?php endforeach ?>
    <?php endforeach ?>
  <?php endforeach ?>
<?php } ?>

  </tbody>
</table>
