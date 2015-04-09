<?php use_helper('Date') ?>
<?php include_partial('filters',array('form' => $form)) ?>
<div class="ui-widget ui-corner-all ui-widget-content cards">
  <div class="ui-widget-header ui-corner-all fg-toolbar">
    <h1><?php echo __('Memberships-like from %%from%% to %%to%%',array('%%from%%' => format_date($dates['from']), '%%to%%' => format_date($dates['to']))) ?></h1>
    <?php include_partial('attendance/filters_buttons') ?>
  </div>
  <div class="chart">
    <?php echo liWidgetOfc::createChart(800, 450, $sf_context->getModuleName().'/data',true); ?>
    <p class="title"><?php $acc = $sf_user->getAttribute('stats.accounting',array(),'admin_module'); if ( is_array($acc) ) foreach ( $acc['price'] as $price ) if ( $price ) { echo __('By value'); break; } ?></p>
  </div>
  <div class="ui-widget-content ui-corner-all accounting">
  <form action="" method="get">
    <p><span><?php echo __('VAT:') ?></span><span><input type="text" name="accounting[vat]" value="<?php echo isset($accounting['vat']) ? $accounting['vat'] : 0 ?>" />%</span></p>
    <?php foreach ( $cards as $card ): ?>
    <p>
      <span><?php echo __('Prices for %%price%%',array('%%price%%' => __($card['name']))) ?>:</span>
      <span><input type="text" name="accounting[price][<?php echo $card['name'] ?>]" value="<?php echo isset($accounting['price'][$card['name']]) ? $accounting['price'][$card['name']] : 0 ?>" />€</span>
    </p>
    <?php endforeach ?>
    <p><span></span><span><input type="submit" name="submit" value="ok" /></span></p>
  </form>
  </div>
  <p class="ui-widget-content ui-corner-all warning">
    <?php echo __('This chart is calculated on the full selected period. If a member card expires or has been created within it, the total quantity will be impacted with a fraction of this member card and not a full one.') ?>
  </p>
  <div class="actions"><?php include_partial('global/chart_actions') ?></div>
</div>

