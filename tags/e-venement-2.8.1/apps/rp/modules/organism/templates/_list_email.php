<?php if ( $organism->email ): ?>
<a title="<?php echo $organism->email ?>" href="mailto:<?php echo $organism->email ?>">
  <?php if ( $organism->email_npai ): ?>
    <span class="alert"><?php echo $organism->email ?></span>
  <?php else: ?>
    <?php echo $organism->email ?>
  <?php endif ?>
</a>
<?php endif ?>
