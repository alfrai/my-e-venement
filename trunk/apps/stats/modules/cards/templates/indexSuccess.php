<?php use_helper('Date') ?>
<?php include_partial('filters',array('form' => $form)) ?>
<div class="ui-widget ui-corner-all ui-widget-content cards">
  <div class="ui-widget-header ui-corner-all fg-toolbar">
    <h1><?php echo __('Memberships-like from %%from%% to %%to%%',array('%%from%%' => format_date($dates['from']), '%%to%%' => format_date($dates['to']))) ?></h1>
    <?php include_partial('attendance/filters_buttons') ?>
  </div>
  <div class="chart">
    <?php echo liWidgetOfc::createChart(800, 450, $sf_context->getModuleName().'/data',true); ?>
  </div>
  <div class="ui-widget-content ui-corner-all accounting">
  <form action="" method="get">
    <p><span><?php echo __('VAT:') ?></span><span><input type="text" name="accounting[vat]" value="<?php echo $accounting['vat'] ?>" />%</span></p>
    <?php foreach ( $cards as $card ): ?>
    <p>
      <span><?php echo __('Prices for %%price%%',array('%%price%%' => __($card['name']))) ?>:</span>
      <span><input type="text" name="accounting[price][<?php echo $card['name'] ?>]" value="<?php echo $accounting['price'][$card['name']] ?>" />€</span>
    </p>
    <?php endforeach ?>
    <p><span></span><span><input type="submit" name="submit" value="ok" /></span></p>
  </form>
  </div>
  <div class="actions"><?php include_partial('global/chart_actions') ?></div>
</div>

