<?php
  $versions = Doctrine::getTable('ManifestationVersion')
    ->createQuery('mv')
    ->andWhere('mv.id = ?',$manifestation->id)
    ->orderBy('mv.version ASC')
    ->execute();
  
  $last_mod = 0;
  for ( $i = 1 ; $i < $versions->count() ; $i++ )
  foreach ( array(
    'happens_at',
    'duration',
    'reservation_begins_at',
    'reservation_ends_at',
    'blocking',
    'location_id',
  ) as $field )
  if ( $versions[$i]->$field !== $versions[$i-1]->$field )
  {
    $last_mod = $i;
    break;
  }
  
  echo format_datetime($versions[$last_mod]->updated_at,'dd/MM/yyyy HH:mm');
?>
