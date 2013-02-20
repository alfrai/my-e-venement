    <?php
      $groups = $sort = array();
      $total = array('nb' => 0, 'value' => 0);
      
      $objects = array($object);
      $config = $sf_data->getRaw('config');
      foreach ( $config['subobjects'] as $subobjects => $conf )
      foreach ( $object->$subobjects as $subobject )
        $objects[] = $subobject;
    ?>
    <ul class="tdp-object-emails">
      <?php $cpt = 0 ?>
      <?php foreach ( $objects as $obj ): ?>
      <?php $cpt++ ?>
      <?php $total['nb'] += $obj->Emails->count() ?>
      <li class="emails-<?php echo $cpt == 1 ? 'object' : 'subobject-'.$obj->id ?>">
        <?php if ( count($objects) > 1 ) echo $obj ?>
        <ul>
          <?php foreach ( $obj->Emails as $email ): ?>
          <li>
            <?php echo link_to($email,'email/show?id='.$email->id) ?>
          </li>
          <?php endforeach ?>
        </ul>
      </li>
      <?php endforeach ?>
      <?php if ( $total['nb'] == 0 ): ?>
      <li><?php echo __('No result',null,'sf_admin') ?></li>
      <?php endif ?>
    </ul>
