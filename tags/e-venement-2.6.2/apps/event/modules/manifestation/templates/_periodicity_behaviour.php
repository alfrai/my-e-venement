<?php use_helper('I18N') ?>

    <div id="periodicity_behaviour" class="ui-corner-all ui-widget-content">
      <h2><?php echo __('Repeat') ?>:</h2>
      <p>
        <input checked="checked" type="radio" name="periodicity[behaviour]" value="nb" />
        <input type="text" size="2" maxlength="2" name="periodicity[nb]" value="1" id="periodicity_nb" class="number" />
        <label for="periodicity_nb"><?php echo __('times') ?></label>
      </p>
      <p>
        <input type="radio" name="periodicity[behaviour]" value="until" />
        <label for="periodicity_until"><?php echo __('Until') ?></label>
        <?php
          $widget = new liWidgetFormJQueryDateText(array('culture' => 'fr'));
          echo $widget->render('periodicity[until]', null, array('id' => 'periodicity_until'));
        ?>
      </p>
      <p>
        <input type="radio" name="periodicity[behaviour]" value="one_occurrence" />
        <label for="periodicity_one_occurrence"><?php echo __('Only one occurrence') ?>:</label>
        <?php
          $widget = new liWidgetFormJQueryDateText(array('culture' => 'fr'));
          echo $widget->render('periodicity[one_occurrence]', null, array('id' => 'periodicity_one_occurrence'));
        ?>
      </p>
    </div>
