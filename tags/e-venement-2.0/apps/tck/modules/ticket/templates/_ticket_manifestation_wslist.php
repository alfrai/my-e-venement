  <?php if ( $manif->Gauges->count() > 1 ): ?>
  <?php $gauges = array(); foreach ( $manif->Gauges as $gauge ) $gauges[$gauge->workspace_id] = $gauge; ksort($gauges); ?>
  <select name="ticket[gauge_id]">
    <?php foreach ( $gauges as $gauge ): ?>
    <option value="<?php echo $gauge->id ?>"><?php echo $gauge->Workspace ?></option>
    <?php endforeach ?>
  </select>
  <?php else: ?>
    <input type="hidden" value="<?php echo $manif->Gauges[0]->id ?>" name="ticket[gauge_id]" />
  <?php endif ?>
