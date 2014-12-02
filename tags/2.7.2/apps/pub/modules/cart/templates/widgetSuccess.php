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
  </tfoot>
</table>
</div>
</div>
