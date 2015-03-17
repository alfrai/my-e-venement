<?php $fieldName = strtolower($str['collection']).'_list'; ?>
<div class="sf_admin_form_row sf_admin_text sf_admin_form_field_<?php echo $fieldName ?>">
  <div class="label ui-helper-clearfix">
    <label for="email_<?php echo $fieldName ?>"><?php echo $str['title'] ?></label>
  </div>
  <div class="setbyfilter">
    <?php if ( isset($form[$fieldName]) ): ?>
      <?php echo $form[$fieldName] ?>
    <?php else: ?>
      <h3><?php echo __('Set by filter').' ('.$str['nb'].')' ?></h3>
      <?php $cpt = 0 ?>
      <ol><?php foreach ( $collection = $form->getObject()->get($str['collection']) as $object ): ?>
        <li><?php echo $object ?> <?php echo $object->groups_picto ?></li>
        <?php $cpt++; if ( $cpt >= 50 ) break; ?>
        <?php endforeach ?>
        <?php if ( $cpt >= 50 ): ?>
          <li class="hole">...</li>
          <script type="text/javascript"><!--
            $(document).ready(function(){
              for ( i = 0 ; i < <?php echo $collection->count()-51 ?> ; i++ )
                $('<li style="visibility: hidden; height: 0;"></li>').insertAfter('.sf_admin_form_field_<?php echo $fieldName ?> ol li.hole');
            });
          --></script>
          <li><?php echo $collection[$collection->count()-1] ?></li>
        <?php endif ?>
      </ol>
    <?php endif ?>
  </div>
</div>
