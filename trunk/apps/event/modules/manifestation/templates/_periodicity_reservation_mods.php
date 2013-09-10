<?php use_helper('I18N') ?>
    
    <div id="reservation-reservation-mods" class="ui-corner-all ui-widget-content">
      <h2><?php echo __('Change the details of the reservation') ?>:</h2>
      <?php foreach ( array(
        'blocking' => __('Blocking'),
        'reservation_confirmed' => __('Confirmed'),
        'reservation_optional' => __('Optional'),
      ) as $field => $label ): ?>
      <p>
        <input type="checkbox"
               name="periodicity[options][<?php echo $field ?>]"
               value="true"
               id="periodicity_options_<?php echo $field ?>"
               <?php echo $manifestation->$field ? 'checked="checked"' : '' ?>
        />
        <label for="periodicity_options_<?php echo $field ?>"><?php echo $label ?></label>
      </p>
      <?php endforeach ?>
    </div>
