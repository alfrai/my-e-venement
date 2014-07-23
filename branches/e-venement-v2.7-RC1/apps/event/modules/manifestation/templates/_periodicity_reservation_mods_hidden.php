<?php
  $fields = array(
    'reservation_optional' => __('Optional'),
    'blocking' => __('Blocking'),
  );
  if ( $sf_user->hasCredential('event-reservation-confirm') )
    $fields['reservation_confirmed'] = __('Confirmed');
?>
<?php foreach ( $fields as $field => $label ): ?>
<input type="hidden"
  name="periodicity[options][<?php echo $field ?>]"
  value="true"
  id="periodicity_options_<?php echo $field ?>"
/>
<?php endforeach ?>
