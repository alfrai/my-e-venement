<?php use_helper('CrossAppLink') ?>
<td><?php echo __('Contact') ?></td>
<td>
  <?php if ( $ticket->contact_id ): ?>
    <?php echo cross_app_link_to($ticket->DirectContact, 'rp', 'contact/show?id='.$ticket->contact_id) ?>
    <span class="picto"><?php echo $sf_data->getRaw('ticket')->DirectContact->groups_picto ?></span>
  <?php else: ?>
  <?php echo cross_app_link_to($ticket->Transaction->Contact, 'rp', 'contact/show?id='.$ticket->Transaction->contact_id) ?>
  <span class="picto"><?php echo $sf_data->getRaw('ticket')->Transaction->Contact->groups_picto ?></span>
  <?php endif ?>
</td>
<td>
  <?php if ( $ticket->contact_id && $ticket->Transaction->contact_id): ?>
    <?php echo __('Offered by') ?>
    <?php echo cross_app_link_to($ticket->Transaction->Contact, 'rp', 'contact/show?id='.$ticket->Transaction->contact_id) ?>
    <span class="picto"><?php echo $sf_data->getRaw('ticket')->Transaction->Contact->groups_picto ?></span>
  <?php endif ?>
  <?php if ( $ticket->Transaction->professional_id ): ?>
    <?php echo $ticket->Transaction->Professional->name ? $ticket->Transaction->Professional->name : $ticket->Transaction->Professional->ProfessionalType ?>
    <span class="picto"><?php echo $sf_data->getRaw('ticket')->Transaction->Professional->groups_picto ?></span>
    <br/>
    <?php echo $ticket->Transaction->Professional->Organism ?>
  <?php endif ?>
</td>
<td></td>

