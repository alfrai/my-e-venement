<h2><?php echo __('Confirmed spectators') ?></h2>
<table class="printed">
  <tbody>
  <?php $workspaces = array(); $total = array('qty' => array(), 'value' => 0) ?>
  <?php $overlined = true ?>
  <?php if ( !isset($spectators) ) $spectators = $form->spectators ?>
  <?php foreach ( $spectators as $transac ): ?>
  <?php
    $transaction = $contact = $pro = array();
    $contact = array('value' => array(), 'prices' => array(), 'ticket-ids' => array());
    $contact['transaction'] = $transac;
    $contact['pro'] = $transac->Professional;
    if ( !isset($transac->printed) )
    {
      foreach ( $transac->Tickets as $t )
      if ( $t->printed || $t->integrated )
      {
        $contact['ticket-ids'][] = $t->id;
        if ( !isset($contact['prices'][$t->Gauge->workspace_id]) )
          $contact['prices'][$t->Gauge->workspace_id] = array('name' => $t->Gauge->Workspace->name);
        isset($contact['prices'][$t->Gauge->workspace_id][$t->price_name])
          ? $contact['prices'][$t->Gauge->workspace_id][$t->price_name]++
          : $contact['prices'][$t->Gauge->workspace_id][$t->price_name] = 1;
        if ( !isset($contact['value'][$t->Gauge->workspace_id]) )
          $contact['value'][$t->Gauge->workspace_id] = 0;
        $contact['value'][$t->Gauge->workspace_id] += $t->value;
        if ( !isset($total['qty'][$t->gauge_id]) ) $total['qty'][$t->gauge_id] = 0;
        $total['qty'][$t->gauge_id]++;
        $workspaces[$t->gauge_id] = $t->Gauge->Workspace->name;
        $total['value'] += $t->value;
      }
    }
    elseif ( $transac->printed > 0 )
    {
      $contact['ticket-ids'][] = '-';
      $contact['prices'][''] = $transac->printed;
      $contact['value'] = $transac->printed_value;
      $total[0]['qty'] += $transac->printed;
      $total['value'] += $transac->printed_value;
    }
  ?>
  <?php if ( $contact['ticket-ids'] ): ?>
  <?php foreach ( $contact['prices'] as $wsid => $ws ): ?>
  <tr class="<?php echo ($overlined = !$overlined) ? 'overlined' : '' ?>">
    <td class="name"><?php echo cross_app_link_to($transac->Contact,'rp','contact/show?id='.$transac->contact_id) ?></td>
    <td class="organism"><?php if ( $contact['pro'] ) echo cross_app_link_to($contact['pro']->Organism,'rp','organism/show?id='.$contact['pro']->Organism->id) ?></td>
    <td class="tickets">
      <?php include_partial('show_spectators_list_tickets',array('tickets' => $ws, 'show_workspaces' => $show_workspaces)) ?>
    </td>
    <td class="price"><?php echo format_currency($contact['value'][$wsid],'€') ?></td>
    <td class="accounting"><?php if ( $contact['transaction']->Invoice[0]->id ): ?>#<?php echo $contact['transaction']->Invoice[0]->id ?><?php else: ?>-<?php endif ?></td>
    <td class="transaction" title="<?php echo __('Updated at %%d%% by %%u%%',array('%%d%%' => format_datetime($transac->updated_at), '%%u%%' => $transac->User)) ?>">#<?php echo cross_app_link_to($contact['transaction'],'tck','ticket/sell?id='.$contact['transaction']) ?></td>
    <td class="ticket-ids">#<?php echo implode(', #',$contact['ticket-ids']) ?></td>
  </tr>
  <?php endforeach ?>
  <?php endif ?>
  <?php endforeach ?>
  </tbody>
  <?php include_partial('show_spectators_list_table_footer',array('total' => $total, 'workspaces' => $workspaces)) ?>
  <?php include_partial('show_spectators_list_table_header') ?>
</table>
