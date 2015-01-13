<?php use_helper('Date', 'CrossAppLink') ?>
<?php
  $json = array(
    'success' => $success,
    'message' => $success ? __('Checkpoint: success.') : __('Checkpoint: failure!'),
    'timestamp' => format_datetime(date('Y-m-d H:i:s'), 'dd/MM/yyyy HH:mm:ss'),
    'tickets' => array(),
    'details' => array(
      'control' => array(
        'comment' => isset($comment) ? $comment : null,
        'errors' => array(),
      ),
      'contacts' => array(),
    ),
  );
  
  foreach ( $tickets as $object )
  if ( is_object($object) && $object->getRawValue() instanceof Doctrine_Record )
  {
    if ( $object->getRawValue() instanceof Control )
      $ticket = $object->Ticket;
    else
      $ticket = $object;
    
    $contact = array();
    $contact['contact'] = array(
      'id'      => $ticket->Transaction->contact_id,
      'name'    => (string)$ticket->Transaction->Contact,
      'comment' => $ticket->Transaction->Contact->description,
      'url'     => cross_app_url_for('rp', 'contact/edit?id='.$ticket->Transaction->Contact->id, true),
      'flash'   => $ticket->Transaction->Contact->flash_on_control,
    );
    
    // reset flash
    if ( $ticket->Transaction->Contact->getRawValue()->flash_on_control )
    {
      $ticket->Transaction->Contact->getRawValue()->flash_on_control = NULL;
      $ticket->Transaction->Contact->getRawValue()->save();
    }
    
    if ( $ticket->Transaction->Contact->picture_id )
      $contact['contact']['picture_url'] = cross_app_url_for('default', 'picture/display?id='.$ticket->Transaction->Contact->picture_id, true);
    
    // if any specific contact is specified
    if ( $ticket->contact_id
      && $ticket->DirectContact->identifier() != $ticket->Transaction->Contact->identifier() )
    {
      $contact['direct_contact'] = array(
        'id'      => $ticket->contact_id,
        'name'    => (string)$ticket->DirectContact,
        'comment' => $ticket->DirectContact->description,
        'url'     => cross_app_url_for('rp', 'contact/edit?id='.$ticket->DirectContact->id, true),
        'flash'   => $ticket->DirectContact->flash_on_control,
      );
      
      // reset flash
      if ( $ticket->DirectContact->getRawValue()->flash_on_control )
      {
        $ticket->DirectContact->getRawValue()->flash_on_control = NULL;
        $ticket->DirectContact->getRawValue()->save();
      }
      echo $ticket->DirectContact->flash_on_control;
      
      if ( $ticket->DirectContact->picture_id )
        $contact['direct_contact']['picture_url'] = cross_app_url_for('default', 'picture/display?id='.$ticket->DirectContact->picture_id, true);
    }
    
    if ( $ticket->Transaction->professional_id )
      $contact['contact'] = $contact['contact'] + array(
        'professional' => array(
          'name' => $ticket->Transaction->Professional->name_type,
          'comment' => $ticket->Transaction->Professional->description,
        ),
        'organism' => array(
          'name' => (string)$ticket->Transaction->Professional->Organism,
          'comment' => $ticket->Transaction->Professional->description,
          'url' => cross_app_url_for('rp', 'organism/show?id='.$ticket->Transaction->Professional->Organism->id, true),
        ),
      );
    
    $json['details']['contacts'][$ticket->id] = $contact;
  }
  
  // errors
  foreach ( $errors as $e )
    $json['details']['control']['errors'][] = $e;
  foreach ( $tickets->getRawValue() as $ticket )
  if ( $ticket instanceof Ticket )
    $json['tickets'][] = array(
      'id' => $ticket->id,
      'url' => url_for('ticket/show?id='.$ticket->id, true),
    );
  elseif ( $ticket )
    $json['tickets'][] = array('id' => $ticket);
?>
<?php if ( sfConfig::get('sf_web_debug', false) ): ?>
<pre><?php print_r($json) ?></pre>
<?php else: ?>
<?php echo json_encode($json) ?>
<?php endif ?>
