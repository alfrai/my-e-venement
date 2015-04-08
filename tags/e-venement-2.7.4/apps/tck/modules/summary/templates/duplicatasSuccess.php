<?php include_partial('assets') ?>
<?php use_helper('CrossAppLink') ?>

<div class="ui-widget-content ui-corner-all">
  <div class="fg-toolbar ui-widget-header ui-corner-all">
    <h1><?php echo __('Duplicatas') ?></h1>
  </div>
<table class="ui-widget-content ui-corner-all summary" id="duplicatas">
  <tbody>
    <?php foreach ( $transactions as $transaction ): ?>
    <tr class="transaction">
      <td class="id"><?php echo __('Transaction') ?>: #<?php echo $sf_user->hasCredential('tck-transaction') )
        ? link_to($transaction,'ticket/sell?id='.$transaction->id)
        : $transaction
      ?></td>
      <td class="updated_at"><?php echo $transaction->updated_at ?></td>
      <td class="qty"><?php echo $transaction->Tickets->count() ?></td>
      <td class="contact"><?php echo cross_app_link_to($transaction->Contact,'rp','contact/show?id='.$transaction->contact_id) ?></td>
      <td class="organism"><?php echo cross_app_link_to($transaction->Professional->Organism,'rp','organism/show?id='.$transaction->Professional->organism_id) ?></td>
      <td class="user">
        <?php
          $users = array();
          foreach ( $transaction->Tickets as $ticket )
          if ( !is_null($ticket->duplicating) )
            $users[$ticket->sf_guard_user_id] = $ticket->User;
          echo implode(', ',$users)
        ?>
      </td>
    </tr>
    <?php endforeach ?>
  </tbody>
  <thead><tr>
    <td class="id"><?php echo __('Transaction') ?></td>
    <td class="updated_at"><?php echo __('Updated at') ?></td>
    <td class="qty"><?php echo __('Duplicatas') ?></td>
    <td class="contact"><?php echo __('Contact') ?></td>
    <td class="organism"><?php echo __('Organism') ?></td>
    <td class="users"><?php echo __('Operators') ?></td>
  </tr></thead>
</table>

<div class="clear"></div>
</div>
