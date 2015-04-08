<?php
  $q = Doctrine::getTable('Event')->createQuery('e')
    ->leftJoin('e.Manifestations m')
    ->leftJoin('m.ManifestationEntries mme')
    ->leftJoin('mme.Entries meee') // EntryElement
    ->leftJoin('meee.ContactEntry ce')
    ->andWhere('ce.professional_id = ?', $professional->id)
    ->andWhere('meee.accepted = ?', true)
    ->andWhereIn('e.meta_event_id', array_keys($sf_data->getRaw('sf_user')->getMetaEventsCredentials()))
    ->select('e.*, translation.*')
    ->orderBy('translation.name');
  $events = $q->execute();
?>
<ul>
<?php foreach ( $events as $event ): ?>
  <li><?php echo cross_app_link_to($event, 'event', 'event/show?id='.$event->id) ?>
<?php endforeach ?>
</ul>
