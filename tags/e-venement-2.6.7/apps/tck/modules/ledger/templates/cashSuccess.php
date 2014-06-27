<?php include_partial('assets') ?>
<?php include_partial('global/flashes') ?>

<div class="ui-widget-content ui-corner-all" id="cash-ledger">
  <div class="fg-toolbar ui-widget-header ui-corner-all">
    <h1>
      <?php echo __('Cash Ledger') ?>
      (<?php echo __('from %%from%% to %%to%%',array('%%from%%' => format_date(strtotime($dates[0])), '%%to%%' => format_date(strtotime($dates[1])))) ?>)
    </h1>
    <a class="fg-button fg-button-icon-left ui-state-default" href="<?php echo url_for('ledger/extract?type=cash') ?>">
      <span class="ui-icon ui-icon-cart"></span>
      <?php echo __('Extract') ?>
    </a>
  </div>

<?php echo include_partial('criterias',array('form' => $form, 'ledger' => 'cash')) ?>

<?php if ( $users ): ?>
<?php include_partial('users',array('users' => $users)) ?>
<?php endif ?>

<table class="ui-widget-content ui-corner-all" id="ledger">
  <?php $total = array('qty' => 0, 'value' => 0) ?>
  <tbody><?php foreach ( $methods as $method ): ?>
    <tr class="method">
      <?php
        $value = 0;
        foreach ( $method->Payments as $payment )
          $value += $payment->value;
        $total['value'] += $value;
        $total['qty']   += $method->Payments->count();
      ?>
      <td class="method"><?php echo $method ?></td>
      <td class="see-more"><a href="#<?php echo $method->id ?>">+</a></td>
      <td class="id-qty"><?php echo $qty = $method->Payments->count() ?></td>
      <td class="value"><?php echo format_currency($value,'€'); $value += $payment->value ?></td>
      <td class="ref">#<?php echo $method->account ?></td>
      <td class="date"></td>
      <td class="user"></td>
    </tr>
    <?php foreach ( $method->Payments as $payment ): ?>
    <tr class="payment method-<?php echo $method->id ?>">
      <td class="method"></td>
      <td class="see-more"></td>
      <td class="id-qty">#<?php echo link_to($payment->Transaction,'ticket/sell?id='.$payment->Transaction->id) ?></td>
      <td class="value"><?php echo format_currency($payment->value,'€'); $value += $payment->value ?></td>
      <td class="ref">
        <?php
          $transaction = $payment->getRaw('Transaction');
          $professional = $transaction->Professional;
          $organism = $transaction->professional_id ? $professional->Organism : NULL;
          $contact = $transaction->contact_id ? $transaction->Contact : NULL;
        ?>
        <?php if ( !is_null($organism) ): ?>
          <a href="<?php echo cross_app_url_for('rp','organism/show?id='.$organism->id) ?>">
            <?php echo $contact.' @ '.$organism ?>
          </a>
        <?php elseif ( !is_null($contact) ): ?>
          <a href="<?php echo cross_app_url_for('rp','contact/show?id='.$contact->id) ?>">
            <?php echo $contact ?>
          </a>
        <?php endif ?>
      </td>
      <td class="date"><?php echo format_date($payment->created_at) ?></td>
      <td class="user"><?php echo $payment->User ?></td>
    </tr>
    <?php endforeach; endforeach ?>
  </tbody>
  <tfoot><tr class="total">
    <td class="method"><?php echo __('Total') ?></td>
    <td class="see-more"></td>
    <td class="id-qty"><?php echo $total['qty'] ?></td>
    <td class="value"><?php echo format_currency($total['value'],'€'); ?></td>
    <td class="ref"></td>
    <td class="date"></td>
    <td class="user"></td>
  </tr></tfoot>
  <thead><tr>
    <td class="method"><?php echo __('Payment Method') ?></td>
    <td class="see-more"></td>
    <td class="id-qty"><?php echo __('id/qty') ?></td>
    <td class="value"><?php echo __('Value') ?></td>
    <td class="ref"><?php echo __('Reference') ?></td>
    <td class="date"><?php echo __('Date') ?></td>
    <td class="user"><?php echo __('User') ?></td>
  </tr></thead>
</table>
<div class="clear"></div>
</div>
