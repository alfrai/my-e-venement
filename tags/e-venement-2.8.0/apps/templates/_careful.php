<?php $client = sfConfig::get('project_about_client', array()) ?>
<?php
  foreach ( array('name', 'address', 'url',) as $attr )
  if ( !isset($client[$attr]) )
    $client[$attr] = '';
?>

<?php if ( isset($client['logo']) && $client['logo'] ): ?>
<?php if (!( isset($client['logo_attributes']) && is_array($client['logo_attributes']) )) $client['logo_attributes'] = array(); ?>
<p class="logo"><?php echo link_to(image_tag($client['logo'], array_merge($client['logo_attributes'], array('alt' => $client['name']))), $client['url'], array('target' => '_blank')) ?></p>
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
