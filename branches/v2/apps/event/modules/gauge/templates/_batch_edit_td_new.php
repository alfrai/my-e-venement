<?php
  $g = new Gauge();
  $g->manifestation_id = $sf_request->getParameter('id');
  
  $form = new GaugeForm($g);
  $form->setHidden(array('manifestation_id','value'));
  
  $form['workspace_id']->getWidget()->setOption('query', Doctrine::getTable('Workspace')->createQuery('w')
    ->leftJoin('w.Gauge g')
    ->where('g.id IS NULL')
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
