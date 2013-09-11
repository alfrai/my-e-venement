<?php
  $conflicts = Doctrine::getTable('Manifestation')->getConflicts(array('id' => $manifestation->id));
  $location  = Doctrine::getTable('Location')->findOneById($conflicts[$manifestation->id][0]['location_id']);
?>
<?php echo link_to($location, ($location->place ? 'location' : 'resource').'/show?id='.$location->id);
