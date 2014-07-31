    <?php
      $objects = array($object);
      $config = $sf_data->getRaw('config');
      foreach ( $config['subobjects'] as $subobjects => $conf )
      foreach ( $object->$subobjects as $subobject )
        $objects[] = $subobject;
      $cpt = 0;
    ?>
    <ul class="tdp-object-surveys-answered">
      <?php
        $sort = $qty = $added_surveys = array();
        $total = array('nb' => 0, 'value' => 0);
      ?>
      <?php foreach ( $objects as $obj ): ?>
      <?php $cpt++ ?>
      <?php if ( $obj->SurveyAnswersGroups->count() > 0 ): ?>
      <li class="surveys-answered-<?php echo $cpt == 1 ? 'object' : 'subobject surveys-answered-subobject-'.$obj->id ?>">
        <h3><?php if ( count($objects) > 1 ) echo $obj ?></h3>
        <ul>
          <?php foreach ( $obj->SurveyAnswersGroups as $group ): ?>
          <?php if ( $group->Answers->count() > 0 ): ?>
            <?php if ( !in_array($group->survey_id, $added_surveys) ): ?>
              <?php $added_surveys[] = $group->survey_id ?>
              <?php $sort[$group->updated_at.'#'.$group->id] = $group ?>
              <?php $qty[$group->survey_id] = 0 ?>
            <?php endif ?>
            <?php $qty[$group->survey_id]++ ?>
            <?php $total['nb']++ ?>
          <?php endif ?>
          <?php endforeach ?>
          <?php ksort($sort); $cpt = 0; foreach ( array_reverse($sort) as $group ): ?>
            <?php $cpt++ ?>
            <li <?php echo $cpt > 10 ? 'class="archive"' : '' ?>>
              <span class="date"><?php echo format_date($group->updated_at) ?></span>
              <?php echo cross_app_link_to($group->Survey, 'srv', 'answer/index?filters[survey_id]='.$group->Survey->id.'&filters['.strtolower(get_class($sf_data->getRaw('object'))).'_id]='.$object->id) ?>
              <?php if ( $qty[$group->survey_id] > 1 ): ?>
                <span class="qty">x <?php echo $qty[$group->survey_id] ?></span>
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
