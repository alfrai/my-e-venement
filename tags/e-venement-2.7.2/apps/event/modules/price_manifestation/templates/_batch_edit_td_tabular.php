<td class="sf_admin_text sf_admin_list_td_Price">
  <?php echo $price_manifestation->getPrice() ?>
</td>
<td class="sf_admin_text sf_admin_list_td_price_description">
  <?php echo $price_manifestation->getPrice()->description ?>
</td>
<td class="sf_admin_text sf_admin_list_td_value">
  <?php
    $pm = new PriceManifestation();
    $pm->id = $price_manifestation->getRaw('id');
    $pm->price_id = $price_manifestation->getRaw('price_id');
    $pm->manifestation_id = $price_manifestation->getRaw('manifestation_id');
    $pm->value = $price_manifestation->getRaw('value');
    $form = new PriceManifestationForm($pm);
    $form->setHidden();
    $form['value']->getWidget()->setLabel('');
  ?>
  <form action="<?php echo url_for('price_manifestation/update?id='.$pm->id) ?>" method="post" title="<?php echo __("This field is updated automagically") ?>">
  <input name="sf_method" value="put" type="hidden">
  <?php foreach ( $form as $field ) echo $field; ?>
  </form>
</td>
