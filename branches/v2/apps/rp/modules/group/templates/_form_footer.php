<div id="more">
  <?php include_partial('group/contacts_list', array('group' => $group, 'form' => $form, 'configuration' => $configuration)) ?>
  <?php include_partial('group/professionals_list', array('group' => $group, 'form' => $form, 'configuration' => $configuration)) ?>
  <?php include_partial('group/members_total', array('group' => $group, 'form' => $form, 'configuration' => $configuration)) ?>
</div>
