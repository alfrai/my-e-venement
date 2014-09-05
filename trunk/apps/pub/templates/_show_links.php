<?php use_stylesheet('pub-links') ?>
<?php use_javascript('pub-links') ?>
<?php
  $collection = array('LinkedProducts' => array(), 'LinkedManifestations' => array());
  foreach ( $sf_user->getTransaction()->BoughtProducts as $bp )
    $collection['LinkedProducts'][] = $bp->Declination->product_id;
  foreach ( $sf_user->getTransaction()->Tickets as $ticket )
    $collection['LinkedManifestations'][] = $ticket->manifestation_id;
?>
<?php if ( !is_array($sf_data->getRaw('objects')) && !$sf_data->getRaw('objects') instanceof Doctrine_Collection ) $objects = array($objects); ?>
<div class="links">
  <h3 class="intro"><?php echo __('We also recommend...') ?></h3>
  <?php foreach ( $objects as $object ): ?>
  <?php if ( $object->getTable()->hasRelation('LinkedProducts') ): ?>
  <div class="products">
    <?php foreach ( $object->LinkedProducts as $link ): ?>
    <?php if ( in_array($link->id, $collection['LinkedProducts']) ) continue ?>
    <?php $collection['LinkedProducts'][] = $link->id ?>
    <?php if (!( $link->getRawValue() instanceof liUserAccessInterface && !$link->isAccessibleBy($sf_user->getRawValue()) )): ?>
    <div class="link">
      <h4><a href="<?php echo url_for('store/edit?id='.$link->id) ?>"><?php echo $link ?></a></h4>
      <div>
        <?php if ( $link->picture_id ): ?>
          <img src="<?php echo url_for('picture/display?id='.$link->picture_id) ?>" alt="<?php echo $link ?>" title="<?php echo $link ?>" />
        <?php endif ?>
        <div title="<?php echo __('Read more...') ?>"><?php echo $link->getRawValue()->description ?></div>
      </div>
    </div>
    <?php endif ?>
    <?php endforeach ?>
  </div>
  <?php endif ?>
  <?php endforeach ?>
  
  <?php foreach ( $objects as $object ): ?>
  <?php if ( $object->getTable()->hasRelation('LinkedManifestations') ): ?>
  <div class="manifestations">
    <?php foreach ( $object->LinkedManifestations as $link ): ?>
    <?php if ( in_array($link->id, $collection['LinkedManifestations']) ) continue ?>
    <?php $collection['LinkedManifestations'][] = $link->id ?>
    <?php if (!( $link->getRawValue() instanceof liUserAccessInterface && !$link->isAccessibleBy($sf_user->getRawValue()) )): ?>
    <div class="link">
      <h4><a href="<?php echo url_for('manifestation/edit?id='.$link->id) ?>">
        <?php echo $link->Event ?>
        <br/>
        <?php echo $link->getShortenedDate() ?>
      </a></h4>
      <div title="<?php echo __('Read more...') ?>"><?php echo $link->Event->getRawValue()->description ?></div>
    </div>
    <?php endif ?>
    <?php endforeach ?>
  </div>
  <?php endif ?>
  <?php endforeach ?>
</div>
