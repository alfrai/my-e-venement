<?php $client = sfConfig::get('project_about_client', array()) ?>

<?php if ( isset($client['logo']) && $client['logo'] ): ?>
<p class="logo"><?php echo link_to(image_tag($client['logo'], array('alt' => $client['name'])), $client['url'], array('target' => '_blank')) ?></p>
<?php endif ?>

<p class="name"><?php echo $client['name'] ?></p>

<?php if ( $client['address'] ): ?>
<p class="address">
  <?php echo nl2br(trim($client['address'])) ?>
  <br/>
  <?php echo link_to($client['url'], $client['url'], array('target' => '_blank')) ?>
</p>
<?php endif ?>

<p style="clear: both"></p>
