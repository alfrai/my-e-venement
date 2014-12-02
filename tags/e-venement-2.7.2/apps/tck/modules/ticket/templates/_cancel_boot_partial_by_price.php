<?php use_helper('CrossAppLink') ?>
<?php use_stylesheet('ticket-cancel') ?>
  <form action="<?php echo url_for('ticket/cancelPartial') ?>" method="get" class="ui-widget-content ui-corner-all partial-by-price">
    <div class="fg-toolbar ui-widget-header ui-corner-all">
      <h2><?php echo __('Partial cancellation by price') ?></h2>
    </div>
    <p>
      <label for="manifestation_id"><?php echo __('Manifestation') ?></label>
      <?php
        $select = new sfWidgetFormDoctrineJQueryAutocompleter(array(
          'model' => 'Manifestation',
          'url'   => cross_app_url_for('event','manifestation/ajax'),
        ));
        echo $select->render('manifestation_id');
      ?>
    </p>
    <p class="short">
      <label for="price_name"><?php echo __('Price') ?></label>
      <input type="text" name="price_name" value="" autocomplete="off" />
      <label for="qty"><?php echo __('Quantity') ?></label>
      <input type="text" name="qty" value="" autocomplete="off" />
    </p>
    <p class="short">
      <label for="id"><?php echo __('Transaction') ?></label>
      #<input type="text" name="id" value="" autocomplete="off" />
    </p>
    <p>
      <label for=""></label>
      &nbsp;&nbsp;<input type="submit" name="" value="<?php echo __('Cancel') ?>" />
    </p>
  </form>
