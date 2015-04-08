<?php foreach ( $pager->getResults() as $product_category ): ?>
<?php if ( $product_category->id == $sf_request->getParameter('cid') && $product_category->Products->count() > 0 ): ?>

<div class="sf_admin_list sf_admin_list_products">
  <table cellspacing="0">
    <thead>
      <tr>
        <th class="sf_admin_text sf_admin_list_th_list_name"><?php echo __('Products') ?></th>
    </thead>
    <tfoot>
      <tr>
        <th colspan="1"><?php echo format_number_choice('[0] no result|[1] 1 result|(1,+Inf] %1% results', array('%1%' => $product_category->Products->count()), $product_category->Products->count(), 'sf_admin') ?></th>
      </tr>
    </tfoot>
    <tbody>
      <?php $odd = false ?>
      <?php
        // ordering category's products
        $pdts = array();
        foreach ( $product_category->Products as $pdt )
        if ( $pdt->online )
          $pdts[$pdt->name.' || '.$pdt->id] = $pdt;
        ksort($pdts);
      ?>
      <?php foreach ( $pdts as $pdt ): ?>
      <tr class="sf_admin_row <?php echo $odd ? 'odd' : ''; $odd = !$odd; ?>">
        <td class="sf_admin_text sf_admin_list_td_list_name">
          <?php echo link_to($pdt, 'store/edit?id='.$pdt->id) ?>
        </td>
      </tr>
      <?php endforeach ?>
    </tbody>
  </table>
</div>

<?php endif ?>
<?php endforeach ?>
