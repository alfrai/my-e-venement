<?php
  $pm = new PriceManifestation();
  $pm->manifestation_id = $sf_request->getParameter('id');
  
  $form = new PriceManifestationForm($pm);
  $form->setHidden(array('manifestation_id','value'));
  
  $form['price_id']->getWidget()->setOption('query', Doctrine::getTable('Price')->createQuery('p')
    ->leftJoin('p.PriceManifestations pm')
    ->where('pm.id IS NULL')
    ->orderBy('p.name')
  );
?>
<td class="sf_admin_text sf_admin_list_td_Price">
  <form action="<?php echo url_for('price_manifestation/create') ?>" method="post" class="sf_admin_new">
    <?php foreach ( $form as $field ) echo $field; ?>
  </form>
</td>
<td class="sf_admin_text sf_admin_list_td_price_description">
  <?php echo __('-- new price --') ?>
</td>
<td class="sf_admin_text sf_admin_list_td_value">
</td>
