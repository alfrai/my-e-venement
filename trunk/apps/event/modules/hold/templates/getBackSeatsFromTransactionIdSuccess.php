<?php
  $params = array('%%tid%%' => $transaction_id);
  if ( $cpt['expected'] == $cpt['realized'] )
  {
    $type = 'success';
    $message = __('Transfert successful from transaction #%%tid%% to this hold.', $params);
  }
  elseif ( $cpt['expected'] == 0 )
  {
    $type = 'notice';
    $message = __('No seat can be transferred from the transaction #%%tid%%.', $params);
  }
  elseif ( $cpt['realized'] == 0 )
  {
    $type = 'error';
    $message = __('A global error occurred transferring the seats from transaction #%%tid%%', $params);
  }
  else
  {
    $type = 'notice';
    $message = __('Some errors occurred but some seats have been transferred correctly from transaction #%%tid%%', $params);
  }
?>
<?php
  echo json_encode(array(
    'result' => $cpt->getRawValue(),
    'message' => $message,
    'type' => $type,
  ));

