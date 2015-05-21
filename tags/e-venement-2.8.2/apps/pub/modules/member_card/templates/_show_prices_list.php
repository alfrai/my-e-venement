<?php if ( $member_card->MemberCardPrices->count() > 0 ): ?>
<div class="sf_admin_form_row li-prices-list">
  <label><?php echo __('Associated prices still available') ?>:</label>
  <span class="nb"><?php echo $member_card->MemberCardPrices->count() ?></span>
  <table class="prices_list ui-widget ui-corner-all ui-widget-content" style="margin-bottom: 10px;">
  <tbody>
  <?php foreach ( $member_card->MemberCardPrices as $price ): ?>
    <tr data-price-id="<?php echo $price->id ?>" data-event-id="<?php echo $price->Event->id ?>">
      <td><?php echo $price->Price->description ? $price->Price->description : $price ?></td>
      <td><?php echo $price->Event ?></td>
    </tr>
  <?php endforeach ?>
  </tbody>
  <tfoot>
      <td><?php echo __('%%nb%% slot(s)',array('%%nb%%' => $member_card->MemberCardPrices->count())) ?></td>
      <td></td>
  </tfoot>
  </table>
</div>
<?php endif ?>
