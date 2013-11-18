<?php echo $form->renderFormTag(url_for('ticket/commit')) ?>
<?php foreach ( $gauges as $gauge ): ?>
<div class="gauge" id="gauge-<?php echo $gauge->id ?>">
  <?php $form->setGaugeId($gauge->id) ?>
  <?php if ( $gauges->count() > 1 ): ?>
    <h3><?php echo $gauge ?></h3>
  <?php endif ?>
  <?php if ( ($free = $gauge->value - $gauge->printed - $gauge->ordered - (sfConfig::get('app_tickets_count_demands',false) ? $gauge->asked : 0) - $manifestation->online_limit) > 0 ): ?>
    <?php include_partial('show_prices',array('gauge' => $gauge, 'free' => $free, 'form' => $form, 'mcp' => $mcp, )) ?>
  <?php else: ?>
    <?php include_partial('show_full') ?>
  <?php endif ?>
</div>
<?php endforeach ?>
<?php include_partial('show_prices_js') ?>
<p class="submit"><input type="submit" name="submit" value="<?php echo __('Confirm') ?>" /></p>
</form>
