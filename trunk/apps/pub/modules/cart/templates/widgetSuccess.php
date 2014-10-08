<div>
<div id="cart-widget" class="<?php echo $sf_user->isStoreActive() ? 'with-store' : '' ?>">
<a class="show" href="<?php echo url_for('cart/show') ?>">Mon panier</a>
<table>
  <tbody>
  <tr class="tickets">
    <?php include_partial('widget_item',array(
      'objects' => $transac->Tickets,
      'label' => __('Tickets'),
      'price' => $transac->getTicketsPrice(true),
    )) ?>
  </tr>
  <?php if ( $sf_user->isStoreActive() ): ?>
  <tr class="products">
    <?php include_partial('widget_item',array(
      'objects' => $transac->BoughtProducts,
      'label' => __('Store'),
      'price' => $transac->getProductsPrice(true),
    )) ?>
  </tr>
  <?php endif ?>
  <tr class="member_cards">
    <?php if ( $sf_user->getGuardUser()->MemberCards->count() > 0 ): ?>
    <?php include_partial('widget_item',array(
      'objects' => $transac->MemberCards,
      'label' => __('Member cards'),
      'price' => $transac->getMemberCardPrice(true),
    )) ?>
    <?php else: ?>
    <td></td>
    <?php endif ?>
  </tr>
  </tbody>
  <tfoot>
  <tr>
    <?php include_partial('widget_item',array(
      'nb' => $transac->MemberCards->count() + $transac->Tickets->count(),
      'label' => __('Total'),
      'price' => $transac->getMemberCardPrice(true) + $transac->getPrice(true) - $transac->getTicketsLinkedToMemberCardPrice(true),
    )) ?>
  </tr>
  <tr class="timer">
    <?php use_helper('Date') ?>
    <td colspan="5">
      <span class="global"><?php echo __('Order expiration') ?>: <span class="time"><?php echo $global_timeout ?></span></span>
      <?php if ( $older_item_timeout ): ?>
      <span class="older-item"><?php echo __('Older item expiration') ?>: <span class="time"><?php echo $older_item_timeout ?></span></span>
      <?php endif ?>
    </td>
  </tr>
  </tfoot>
</table>
</div>
</div>
