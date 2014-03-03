<?php
  $q = Doctrine_Query::create()->from('EntryTickets et')
    ->select('et.*, ee.id, me.id')
    ->leftJoin('et.EntryElement ee')
    ->leftJoin('ee.ContactEntry ce')
    ->leftJoin('ee.ManifestationEntry me')
    ->leftJoin('me.Manifestation m')
    ->leftJoin('m.Event e')
    ->andWhereIn('e.meta_event_id',array_keys($sf_data->getRaw('sf_user')->getMetaEventsCredentials()))
    ->andWhere('ce.professional_id = ?',$professional->id)
    ->andWhere('ee.accepted = ?',true)
  ;
  
  $nb = 0;
  $mids = array();
  foreach ( $q->execute() as $tickets )
  {
    $mids[$tickets->EntryElement->ManifestationEntry->id] = 1;
    $nb += $tickets->quantity;
  }
  
  echo round($nb/array_sum($mids));
?>
