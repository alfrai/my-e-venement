<?php $workspaces = array() ?>
<?php foreach ( $seated_plan->Workspaces as $ws ) $workspaces[] = (string)$ws; ?>
<?php echo implode(', ',$workspaces) ?>
