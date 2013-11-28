<td class="sf_admin_date sf_admin_list_td_happens_at">
  <a href="<?php echo url_for('manifestation/show?id='.$manifestation->id) ?>">
    <?php echo false !== strtotime($manifestation->getHappensAt()) ? format_date($manifestation->getHappensAt()) : '&nbsp;' ?>
  </a>
</td>
<td class="sf_admin_text sf_admin_list_td_list_location">
  <?php echo get_partial('manifestation/list_location', array('type' => 'list', 'manifestation' => $manifestation)) ?>
</td>
<td class="sf_admin_text sf_admin_list_td_list_description">
  <?php echo get_partial('manifestation/list_description', array('type' => 'list', 'manifestation' => $manifestation)) ?>
</td>
<td class="sf_admin_text sf_admin_list_td_list_gauge">
  <?php echo get_partial('manifestation/list_gauge', array('type' => 'list', 'manifestation' => $manifestation)) ?>
</td>
<?php if ( $sf_user->hasCredential('event-manif-edit') ): ?>
  <?php echo get_partial('manifestation/list_td_actions', array('manifestation' => $manifestation, 'helper' => $helper,)) ?>
<?php endif ?>
