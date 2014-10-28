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
        'values'  => array('manif' => format_currency($pm->value,'€')),
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
      $groups[$gauge->group_name][$pg->price_id]['values'][$pg->id] = floatval($pg->value);
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
<h3><?php echo __('Choose your tickets on the best seats') ?></h3>
<ul><?php foreach ( $groups as $name => $prices ): ?>
  <?php if ( count($prices) > 0 ): ?>
  <li>
    <form action="<?php echo url_for('ticket/addCategorizedTicket') ?>" method="get">
    <span class="category" title="<?php echo $name ?>">
      <?php echo $name ?>
      <input type="hidden" name="price_new[group_name]" value="<?php echo $name ?>" />
      <input type="hidden" name="price_new[manifestation_id]" value="<?php echo $manifestation->id ?>" />
    </span>
    <select class="prices" name="price_new[price_id]"><?php foreach ( $prices as $id => $price ): ?>
      <?php if ( $price['price']->isAccessibleBy($sf_user->getRawValue()) ): ?>
      <option value="<?php echo $id ?>">
        <?php echo $price['price']->description ? $price['price']->description : $price['price'] ?>
        <?php foreach ( $price['values'] as $key => $value ) $price['values'][$key] = format_currency($value,'€'); ?>
        (<?php echo implode(', ', array_unique($price['values'])) ?>)
      </option>
      <?php endif ?>
    <?php endforeach ?></select>
    <span class="qty">
      <a href="#" data-val="-1" class="minus">-</a><input type="text" pattern="\d+" name="price_new[qty]" value="1" /><a href="#" class="plus" data-val="1">+</a>
    </span>
    <button name="add" value=""><?php echo __('Add') ?></button>
    </form>
  </li>
  <?php endif ?>
<?php endforeach ?></ul>
