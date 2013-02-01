    <?php
      $groups = $sort = array();
      $total = array('nb' => 0, 'value' => 0);
      
      $objects = array($object);
      $config = $sf_data->getRaw('config');
      foreach ( $config['subobjects'] as $subobjects => $conf )
      foreach ( $object->$subobjects as $subobject )
        $objects[] = $subobject;
    ?>
    <ul class="groups">
      <?php foreach ( $objects as $obj ): ?>
      <?php $total['nb'] += $obj->Groups->count() ?>
      <li>
        <?php if ( count($objects) > 1 ) echo $obj ?>
        <ul>
          <?php foreach ( $obj->Groups as $group ): ?>
          <li><?php echo link_to($group,'group/show?id='.$group->id) ?></li>
          <?php endforeach ?>
          <li><?php echo __('New') ?> - TODO + del</li>
        </ul>
      </li>
      <?php endforeach ?>
      <?php if ( $total['nb'] == 0 ): ?>
      <li><?php echo __('No result',null,'sf_admin') ?></li>
      <?php endif ?>
    </ul>
