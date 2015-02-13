<ol class="show_checkpoint">
  <?php foreach ( $event->Checkpoints as $checkpoint ): ?>
  <li><?php echo $checkpoint ?> <?php echo $checkpoint->legal ? '('.__('Legal').')' : '' ?></li>
  <?php endforeach ?>
</ol>
