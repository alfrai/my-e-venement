<?php // ALSO USED BY the pending module, in its index... ?>

<?php
  $options = array('id' => $manifestation->id);
  $config = sfConfig::get('app_manifestation_reservations', array());
  if ( isset($potentially) && $potentially
    || !( isset($config['focus_on_potentialities']) && !$config['focus_on_potentialities'] ) )
    $options['potentially'] = $manifestation->id;
  $conflicts = Doctrine::getTable('Manifestation')->getConflicts($options)
?>

<?php if ( count($conflicts) > 0 ): ?>
<?php
  $locations  = Doctrine::getTable('Location')->createQuery('l')
    ->andWhereIn('l.id',array_keys($conflicts[$manifestation->id]))
    ->execute();
  
  $arr = array();
  foreach ( $locations as $location )
    $arr[] = link_to($location, ($location->place ? 'location' : 'resource').'/show?id='.$location->id);
?>
<?php echo implode(', ', $arr) ?>
<?php endif ?>
