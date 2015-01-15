<?php use_helper('I18N') ?>
    
    <div id="reservation-reservation-mods" class="ui-corner-all ui-widget-content">
      <h2><?php echo __('Change the details of the reservation') ?>:</h2>
      <?php
        $fields = array(
          'reservation_optional' => __('Optional'),
          'blocking' => __('Blocking'),
        );
        if ( $sf_user->hasCredential('event-reservation-confirm') )
          $fields['reservation_confirmed'] = __('Confirmed');
      ?>
      <?php foreach ( $fields as $field => $label ): ?>
      <p class="mods_<?php echo $field ?>">
        <input type="checkbox"
               name="periodicity[options][<?php echo $field ?>]"
               value="true"
               id="periodicity_options_<?php echo $field ?>"
               <?php echo $manifestations->count() == 1 && $manifestations[0]->$field ? 'checked="checked"' : '' ?>
        />
        <label for="periodicity_options_<?php echo $field ?>"><?php echo $label ?></label>
      </p>
      <?php endforeach ?>
    </div>
