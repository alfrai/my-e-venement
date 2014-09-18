<?php
  $g = new Gauge();
  $g->manifestation_id = $sf_request->getParameter('id');
  
  $form = new GaugeForm($g);
  $form->setHidden(array('manifestation_id','value','online', 'group_name'));
  
  $form['workspace_id']->getWidget()->setOption('query', Doctrine::getTable('Workspace')->createQuery('w')
    ->leftJoin('w.Gauge g ON g.workspace_id = w.id AND g.manifestation_id = ?',$g->manifestation_id)
    ->andWhere('g.id IS NULL')
    ->leftJoin('w.Users u')
    ->andWhere('u.id = ?',$sf_user->getId())
    ->orderBy('w.name')
  );
?>
<td class="sf_admin_text sf_admin_list_td_Price">
  <form action="<?php echo url_for('gauge/create') ?>" method="post" class="sf_admin_new">
    <?php foreach ( $form as $field ) echo $field; ?>
  </form>
</td>
<td class="sf_admin_text sf_admin_list_td_value">
</td>
<td class="sf_admin_text sf_admin_list_td_online">
</td>
