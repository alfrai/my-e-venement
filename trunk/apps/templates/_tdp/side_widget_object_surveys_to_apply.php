    <?php
      $objects = array($object);
      $config = $sf_data->getRaw('config');
      foreach ( $config['subobjects'] as $subobjects => $conf )
      foreach ( $object->$subobjects as $subobject )
        $objects[] = $subobject;
      $cpt = 0;
    ?>
    <ul class="tdp-object-surveys-to-apply">
      <?php
        $sort = $qty = $added_surveys = array();
        $total = array('nb' => 0, 'value' => 0);
      ?>
      <?php foreach ( $objects as $obj ): ?>
      <?php $cpt++ ?>
      <?php if ( $obj->SurveyToApply->count() > 0 ): ?>
      <li class="surveys-to-apply-<?php echo $cpt == 1 ? 'object' : 'subobject surveys-to-apply-subobject-'.$obj->id ?>">
        <h3><?php if ( count($objects) > 1 ) echo $obj ?></h3>
        <ul>
          <?php foreach ( $obj->SurveyToApply as $sta ): ?>
          <?php if ( $sta->Survey->Queries->count() > 0 ): ?>
            <?php if ( !in_array($sta->survey_id, $added_surveys) ): ?>
              <?php $added_surveys[] = $sta->survey_id ?>
              <?php $sort[$sta->Survey->updated_at.'#'.$sta->id] = $sta ?>
              <?php $qty[$sta->survey_id] = 0 ?>
            <?php endif ?>
            <?php $qty[$sta->survey_id]++ ?>
            <?php $total['nb']++ ?>
          <?php endif ?>
          <?php endforeach ?>
          <?php ksort($sort); $cpt = 0; foreach ( array_reverse($sort) as $sta ): ?>
            <?php $cpt++ ?>
            <li <?php echo $cpt > 10 ? 'class="archive"' : '' ?>>
              <span class="date"><?php echo format_date($sta->Survey->updated_at) ?></span>
              <?php echo cross_app_link_to($sta->Survey, 'srv', 'answer/index?filters[survey_id]='.$sta->Survey->id.'&filters['.strtolower(get_class($sf_data->getRaw('object'))).'_id]='.$object->id) ?>
              <?php if ( $qty[$sta->survey_id] > 1 ): ?>
                <span class="qty">x <?php echo $qty[$sta->survey_id] ?></span>
              <?php endif ?>
            </li>
          <?php endforeach ?>
        </ul>
      </li>
      <?php endif ?>
      <?php endforeach ?>
      <?php if ( $total['nb'] == 0 || $cpt == 0 ): ?>
      <li><?php echo __('No result',null,'sf_admin') ?></li>
      <?php endif ?>
    </ul>
