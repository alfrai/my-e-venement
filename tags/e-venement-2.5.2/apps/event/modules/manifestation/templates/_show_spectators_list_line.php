    <td class="name"><?php echo cross_app_link_to($transac->Contact,'rp','contact/show?id='.$transac->contact_id) ?> <span class="pictos"><?php echo $sf_data->getRaw('transac')->Contact->groups_picto ?></span></td>
    <td class="organism"><?php if ( $contact['pro'] ) echo cross_app_link_to($contact['pro']->Organism,'rp','organism/show?id='.$contact['pro']->Organism->id) ?></td>
    <td class="tickets">
      <?php include_partial('show_spectators_list_tickets',array('tickets' => $ws, 'show_workspaces' => $show_workspaces)) ?>
    </td>
    <td class="price"><?php echo format_currency($contact['value'][$wsid],'€') ?></td>
    <td class="accounting"><?php if ( $contact['transaction']->Invoice[0]->id ): ?>#<?php echo $contact['transaction']->Invoice[0]->id ?><?php else: ?>-<?php endif ?></td>
    <td class="transaction" title="<?php echo __('Updated at %%d%% by %%u%%',array('%%d%%' => format_datetime($transac->updated_at), '%%u%%' => $transac->User)) ?>">#<?php echo cross_app_link_to($contact['transaction'],'tck','ticket/sell?id='.$contact['transaction']) ?></td>
    <td class="ticket-ids">#<?php if ( is_array($contact['ticket-ids']) ) echo implode(', #',$contact['ticket-ids']) ?></td>
