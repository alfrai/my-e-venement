<?php use_javascript('jquery', 'first') ?>
<script type="text/javascript">
  <?php if ( !$stop ): ?>
  w = window.open('<?php echo url_for('organism/labels') ?>?limit=<?php echo $limit ?>&offset=<?php echo $offset+$limit ?>');
  <?php endif ?>
  window.location = '<?php echo url_for('organism/labels') ?>?limit=<?php echo $limit ?>&offset=<?php echo $offset ?>&go';
</script>
