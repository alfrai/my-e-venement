<div>
<div id="cart-widget">
<a class="show" href="<?php echo url_for('cart/show') ?>">Mon panier</a>
<table>
  <tbody>
  <tr class="tickets">
    <?php include_partial('widget_item',array(
      'objects' => $transac->Tickets,
      'label' => __('Tickets'),
      'price' => $transac->getPrice(true),
    )) ?>
  </tr>
  <tr class="member_cards">
    <?php include_partial('widget_item',array(
      'objects' => $transac->MemberCards,
      'label' => __('Member cards'),
      'price' => $transac->getMemberCardPrice(true),
    )) ?>
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
