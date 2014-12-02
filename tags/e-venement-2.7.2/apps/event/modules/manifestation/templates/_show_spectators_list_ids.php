<?php $tickets = $sf_data->getRaw('tickets') ?>
<?php ksort($tickets) ?>
<span class="tickets">
  <?php foreach ( $tickets as $key => $id ) if ( $key != 'name' ): ?>
    <?php echo $num.$id.PHP_EOL ?>
    <?php if ( $show_workspaces ): ?>
      <span class="workspace"><?php echo $tickets['name'] ?></span>
    <?php endif ?>
  <?php endif ?>
</span>
