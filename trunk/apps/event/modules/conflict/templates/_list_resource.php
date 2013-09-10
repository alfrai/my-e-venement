<?php echo link_to($location = Doctrine::getTable('Location')->findOneById($conflict[0]['location_id']), 'location/show?id='.$location->id) ?>
<?php //echo link_to($manifestation->Location, 'location/show?id='.$manifestation->Location->id) ?>
