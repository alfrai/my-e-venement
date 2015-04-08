  <?php
    $events = $sort = array();
    
    $objects = array();
    if ( $object->hasRelation('EventArchives') )
      $objects[] = $object;
  ?>
  <ul class="events">
    <?php foreach ( $objects as $obj ): ?>
    <?php if ( $obj->EventArchives->count() > 0 ): ?>
    <li class="events-archives-<?php echo $cpt == 1 ? 'object' : 'subobject-'.$obj->id ?>">
      <?php if ( count($objects) > 1 ): ?>
      <h3><?php echo $obj ?></h3>
      <?php endif ?>
      <ul>
        <?php
          foreach ( $obj->EventArchives as $archive )
            $events[$archive->happens_at] = $archive;
          
          // sorting by manifestation's date
          ksort($events);
          $events = array_reverse($events);
        ?>
        <?php foreach ( $events as $event ): ?>
          <li title="<?php echo $event ?>"><time datetime="<?php echo $event->happens_at ?>"><?php echo format_date($event->happens_at,'MM/yyyy') ?></time>: <span><?php echo $event ?></span></li>
        <?php endforeach ?>
      </ul>
    </li>
    <?php endif ?>
    <?php endforeach ?>
  </ul>
