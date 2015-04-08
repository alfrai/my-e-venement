<?php use_helper('CrossAppLink') ?>
<?php $tickets = $sf_data->getRaw('tickets') ?>
<?php ksort($tickets) ?>
<span class="tickets">
  <?php foreach ( $tickets as $key => $id ) if ( $key != 'name' ): ?>
    <a href="<?php echo cross_app_url_for('tck', 'ticket/show?id='.$id) ?>"><?php echo $num.$id.PHP_EOL ?></a>
    <?php if ( $show_workspaces ): ?>
      <span class="workspace"><?php echo $tickets['name'] ?></span>
    <?php endif ?>
  <?php endif ?>
</span>
