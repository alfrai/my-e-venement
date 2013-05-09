    <?php
      $groups = $sort = array();
      $total = array('nb' => 0, 'value' => 0);
      
      $objects = array($object);
      $config = $sf_data->getRaw('config');
      foreach ( $config['subobjects'] as $subobjects => $conf )
      foreach ( $object->$subobjects as $subobject )
        $objects[] = $subobject;
      $cpt = 0;
    ?>
    <ul class="tdp-object-emails">
      <?php foreach ( $objects as $obj ): ?>
      <?php $cpt++ ?>
      <?php $total['nb'] += $obj->Emails->count() ?>
      <?php if ( $obj->Emails->count() > 0 ): ?>
      <li class="emails-<?php echo $cpt == 1 ? 'object' : 'subobject-'.$obj->id ?>">
        <h3><?php if ( count($objects) > 1 ) echo $obj ?></h3>
        <ul>
          <?php foreach ( $obj->Emails as $email ): ?>
          <li><?php echo link_to($email,'email/show?id='.$email->id) ?></li>
          <?php endforeach ?>
        </ul>
      </li>
      <?php endif ?>
      <?php endforeach ?>
      <?php if ( $total['nb'] == 0 || $cpt == 0 ): ?>
      <li><?php echo __('No result',null,'sf_admin') ?></li>
      <?php endif ?>
    </ul>
