  <span class="workspace gauge-<?php echo $ticket->gauge_id ?>">
    <?php if ( $nb_gauges > 1 ): ?>
      <span class="ws-gauge"><span class="url"><?php echo cross_app_url_for('event','gauge/state?id='.$ticket->gauge_id) ?></span></span>
      <?php if ( $sf_user->hasCredential('tck-seat-allocation')
        && $ticket->Gauge->Workspace->seated
        && $seated_plan = $ticket->Manifestation->Location->getWorkspaceSeatedPlan($ticket->Gauge->workspace_id) ): ?>
      <a class="ws-name"
         href="<?php echo cross_app_url_for('event','seated_plan/show?gauge_id='.$ticket->gauge_id.'&transaction_id='.$ticket->transaction_id) ?>"
         target="_blank"><?php echo $ticket->Gauge->Workspace->name ?></a>
      <?php else: ?>
      <span class="ws-name"><?php echo $ticket->Gauge->Workspace->name ?></span>
      <?php endif ?>
    <?php endif ?>
  <?php /* </span> */ // trick for multiple gauges, see parent partial ?>
