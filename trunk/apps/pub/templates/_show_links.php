<?php use_stylesheet('pub-links?'.date('Ymd')) ?>
<?php use_javascript('pub-links?'.date('Ymd')) ?>
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
  <?php if ( $object->getTable()->hasRelation($rel = 'LinkedProducts') ): ?>
  <div class="products">
    <?php
      $links = $object->$rel->getData()->getRawValue();
      shuffle($links);
    ?>
    <?php foreach ( $links as $link ): ?>
    <?php if ( in_array($link->id, $collection[$rel]) ) continue ?>
    <?php $collection[$rel][] = $link->id ?>
    <?php if (!( $link instanceof liUserAccessInterface && !$link->isAccessibleBy($sf_user->getRawValue()) )): ?>
    <div class="link">
      <h4><a href="<?php echo url_for('store/edit?id='.$link->id) ?>"><?php echo $link ?></a></h4>
      <div>
        <?php if ( $link->picture_id ): ?>
          <img src="<?php echo url_for('picture/display?id='.$link->picture_id) ?>" alt="<?php echo $link ?>" title="<?php echo $link ?>" />
        <?php endif ?>
        <div title="<?php echo __('Read more...') ?>"><?php echo $link->description ?></div>
      </div>
    </div>
    <?php endif ?>
    <?php endforeach ?>
  </div>
  <?php endif ?>
  <?php endforeach ?>
  
  <?php foreach ( $objects as $object ): ?>
  <?php if ( $object->getTable()->hasRelation($rel = 'LinkedManifestations') ): ?>
  <div class="manifestations">
    <?php
      $links = $object->$rel->getData()->getRawValue();
      shuffle($links);
    ?>
    <?php foreach ( $links as $link ): ?>
    <?php if ( in_array($link->id, $collection[$rel]) ) continue ?>
    <?php $collection[$rel][] = $link->id ?>
    <?php if (!( $link instanceof liUserAccessInterface && !$link->isAccessibleBy($sf_user->getRawValue()) )): ?>
    <div class="link">
      <h4><a href="<?php echo url_for('manifestation/edit?id='.$link->id) ?>">
        <?php echo $link->Event ?>
        <br/>
        <?php echo $link->getShortenedDate() ?>
      </a></h4>
      <div title="<?php echo __('Read more...') ?>"><?php echo $link->Event->description ?></div>
    </div>
    <?php endif ?>
    <?php endforeach ?>
  </div>
  <?php endif ?>
  <?php endforeach ?>
</div>
