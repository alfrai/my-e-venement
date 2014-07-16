    <?php
      $groups = $sort = array();
      
      $objects = array($object);
      $config = $sf_data->getRaw('config');
      foreach ( $config['subobjects'] as $subobjects => $conf )
      foreach ( $object->$subobjects as $subobject )
        $objects[] = $subobject;
    ?>
    <ul class="tdp-object-groups">
      <?php $cpt = 0 ?>
      <?php foreach ( $objects as $obj ): ?>
      <?php $cpt++ ?>
      <li class="groups-<?php echo $cpt == 1 ? 'object' : 'subobject-'.$obj->id ?>">
        <?php if ( count($objects) > 1 ): ?>
          <h3><?php echo $obj ?></h3>
        <?php endif ?>
        <form action="#" method="get"><ul>
          <?php foreach ( $obj->getGroups() as $group ): ?>
          <?php $users = array(); foreach ( $group->Users as $user ) $users[] = $user->id; ?>
          <?php if ( is_null($group->sf_guard_user_id) && (in_array($sf_user->getId(), $users) || $sf_user->hasCredential(array('super-admin','admin'),false)) || $group->sf_guard_user_id === $sf_user->getId() ): ?>
          <li>
            <?php if ( $group->sf_guard_user_id === $sf_user->getId() || (is_null($group->sf_guard_user_id) && $sf_user->hasCredential('pr-group-common')) ): ?>
            <a href="#" class="remove" onclick="javascript: contact_tdp_group_removing_obj<?php echo $cpt.'_'.$obj->id ?>(this);">x</a>
            <input type="hidden" name="group_id" value="<?php echo $group->id ?>" />
            <?php else: ?>
            <span>&nbsp;&nbsp;</span>
            <?php endif ?>
            <?php echo link_to($group,'group/show?id='.$group->id) ?>
          </li>
          <?php endif ?>
          <?php endforeach ?>
          <li class="empty" <?php if ( $obj->Groups->count() > 0 ): ?>style="display: none"<?php endif ?>><?php echo __('No result',null,'sf_admin') ?></li>
          <?php if ( $sf_user->hasCredential('pr-group-common') || $sf_user->hasCredential('pr-group-perso') ): ?>
          <li class="new">
            <input type="hidden" name="object-id" value="<?php echo $obj->id ?>" />
            <select name="unassociated_professional[groups_list][]">
            </select>
            <script type="text/javascript"><!--
            $(document).ready(function(){
              // display available new groups
              object = <?php echo $cpt == 1 ? "$('.tdp-object')" : "$('#tdp-content [name=\"professional[id]\"][value=".$obj->id."]').closest('.tdp-subobject')" ?>;
              select = <?php echo $cpt == 1 ? "$('.groups-object select')" : "$('.groups-subobject-".$obj->id." select')" ?>;
              select.find('*').remove();
              select.prepend(object.find('.tdp-groups_list select').eq(1).find('option').clone(true))
                .prepend('<option value=""></option>')
                .find('option:first-child').prop('selected',true);
              select.prop('multiple', true);
              
              // adding a new group
              select.change(function(){
                var val = $(this).val();
                var select = this;
                if ( typeof val != 'object' )
                  val = [val];
                
                $.each(val, function(id, value) {
                  if ( value == '' )
                    return;
                  object = <?php echo $cpt == 1 ? "$('.tdp-object')" : "$('#tdp-content [name=\"professional[id]\"][value=".$obj->id."]').closest('.tdp-subobject')" ?>;
                  object.find('.tdp-groups_list select').eq(1).find('option[value='+value+']').prop('selected',true);
                  object.find('.tdp-groups_list a:first-child').click();
                  $('<li class="tmp" title="<?php echo __('Not yet recorded') ?>"><a onclick="javascript: contact_tdp_group_removing_obj<?php echo $cpt.'_'.$obj->id ?>(this);" href="#" class="remove">x</a><input type="hidden" value="'+value+'" name="group_id" /> '+$(select).find('option[value='+value+']').html()+'</li>')
                    .hide().insertBefore($(select).closest('li').closest('ul').find('.new')).fadeIn('slow');
                  $(select).closest('ul').find('.empty').hide();
                  $(select).find('option[value='+value+']').remove();
                  $(select).val('');
                });
              });
              
            });
            
            // removing a group
            function contact_tdp_group_removing_obj<?php echo $cpt.'_'.$obj->id ?> (anchor)
            {
              object = <?php echo $cpt == 1 ? "$('.tdp-object')" : "$('#tdp-content [name=\"professional[id]\"][value=".$obj->id."]').closest('.tdp-subobject')" ?>;
              select = <?php echo $cpt == 1 ? "$('.groups-object select')" : "$('.groups-subobject-".$obj->id." select')" ?>;
              
              object.find('.tdp-groups_list select').eq(0)
                .find(str = 'option[value='+$(anchor).closest('li').find('[name=group_id]').val()+']')
                .prop('selected',true)
                .clone(true).prependTo(select)
                .prop('selected',false).addClass('tmp');
              object.find('.tdp-groups_list a:last-child').click();
              $(anchor).closest('li').fadeOut('medium',function(){
                if ( $(this).closest('ul').find('li:not(.empty):not(.new)').length <= 1 )
                  $(this).closest('ul').find('.empty').fadeIn('slow');
                $(this).remove();
              });
            }
            --></script>
          </li>
          <?php endif ?>
        </ul></form>
      </li>
      <?php endforeach ?>
    </ul>
