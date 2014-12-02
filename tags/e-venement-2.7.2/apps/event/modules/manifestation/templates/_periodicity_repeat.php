<?php use_helper('I18N') ?>

    <div id="periodicity_repeat" class="ui-corner-all ui-widget-content">
      <h2><?php echo __('Repeat every') ?>:</h2>
      <?php foreach ( array(
        'hours' => __('hours'),
        'days'  => __('days'),
        'weeks' => __('weeks'),
        'month' => __('month'),
        'years' => __('years'),
      ) as $fieldName => $label ): ?>
      <p><input type="text" name="periodicity[repeat][<?php echo $fieldName ?>]" value="0" maxlength="2" size="2" class="number" id="periodicity_<?php echo $fieldName ?>" /> <label for="periodicity_<?php echo $fieldName ?>"><?php echo $label ?></label></p>
      <?php endforeach ?>
    </div>
