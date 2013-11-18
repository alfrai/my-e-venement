  <?php if ( $manif->Gauges->count() > 1 ): ?>
    <?php $gauges = array(); foreach ( $manif->Gauges as $gauge ) $gauges[(is_null($gauge->Workspace->Order[0]->rank) ? '999999' : $gauge->Workspace->Order[0]->rank).'-'.$gauge->workspace_id] = $gauge; ksort($gauges); ?>
  <select name="ticket[gauge_id]">
    <?php foreach ( $gauges as $gauge ): ?>
    <?php $authws = $sf_user->getWorkspacesCredentials(); if ( isset($authws[$gauge->workspace_id]) ): ?>
    <option value="<?php echo $gauge->id ?>"><?php echo $gauge->Workspace ?></option>
    <?php endif ?>
    <?php endforeach ?>
  </select>
  <?php else: ?>
    <input type="hidden" value="<?php echo $manif->Gauges[0]->id ?>" name="ticket[gauge_id]" />
  <?php endif ?>
