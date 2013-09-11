<?php
  $conflicts = Doctrine::getTable('Manifestation')->getConflicts(array('id' => $manifestation->id, 'potentially' => $manifestation->id,));
  if ( isset($conflicts[$manifestation->id]) && isset($conflicts[$manifestation->id][0]) ):
?>
<?php
  $location  = Doctrine::getTable('Location')->findOneById($conflicts[$manifestation->id][0]['location_id']);
  echo link_to($location, ($location->place ? 'location' : 'resource').'/show?id='.$location->id);
?>
<?php endif ?>
