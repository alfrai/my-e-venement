<?php use_helper('Number') ?>
<?php
  $groups = array();
  foreach ( $manifestation->Gauges as $gauge )
  {
    if ( !isset($groups[$gauge->group_name]) )
      $groups[$gauge->group_name] = array();
    
    foreach ( $manifestation->PriceManifestations as $pm )
    {
      $groups[$gauge->group_name][$pm->price_id] = array(
        'price'   => $pm->Price,
        'values'  => array('manif' => $pm->value),
      );
    }
    
    foreach ( $gauge->PriceGauges as $pg )
    {
      if ( !isset($groups[$gauge->group_name][$pg->price_id]) )
        $groups[$gauge->group_name][$pg->price_id] = array(
          'price'   => $pg->Price,
          'values'  => array(),
        );
      if ( isset($groups[$gauge->group_name][$pg->price_id]['values']['manif']) )
        unset($groups[$gauge->group_name][$pg->price_id]['values']['manif']);
      $groups[$gauge->group_name][$pg->price_id]['values'][$pg->id] = format_currency($pg->value, '€');
    }
  }
  
  // forcing the price order
  foreach ( $groups as $name => $group )
  {
    $arr = array();
    foreach ( $group as $id => $price )
      $arr[$id] = max($price['values']);
    arsort($arr);
    $new = array();
    foreach ( $arr as $id => $value )
      $new[$id] = $group[$id];
    $groups[$name] = $new;
  }
  
  // to be sure...
  ksort($groups);
?>
<ul id="categories" class="ui-widget-content ui-corner-all"><?php foreach ( $groups as $name => $prices ): ?>
  <?php if ( count($prices) > 0 ): ?>
  <li>
    <form action="<?php echo url_for('ticket/addCategorizedTicket') ?>" method="get">
    <span class="category">
      <?php echo $name ?>
      <input type="hidden" name="price_new[group_name]" value="<?php echo $name ?>" />
      <input type="hidden" name="price_new[manifestation_id]" value="<?php echo $manifestation->id ?>" />
    </span>
    <select class="prices" name="price_new[price_id]"><?php foreach ( $prices as $id => $price ): ?>
      <?php if ( $price['price']->isAccessibleBy($sf_user->getRawValue()) ): ?>
      <option value="<?php echo $id ?>">
        <?php echo $price['price']->description ? $price['price']->description : $price['price'] ?>
        <span>(<?php echo implode(', ', array_unique($price['values'])) ?>)</span>
      </option>
      <?php endif ?>
    <?php endforeach ?></select>
    <button><?php echo __('Add') ?></button>
    </form>
  </li>
  <?php endif ?>
<?php endforeach ?></ul>
