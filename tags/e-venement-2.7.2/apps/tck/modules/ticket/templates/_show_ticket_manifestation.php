<?php use_helper('Date') ?>
<?php use_helper('CrossAppLink') ?>

<td><?php echo __('Manifestation') ?></td>
<td><a href="<?php echo cross_app_url_for('event','event/show?id='.$ticket->Manifestation->event_id) ?>"><?php echo $ticket->Manifestation->Event ?></a></td>
<td><a href="<?php echo cross_app_url_for('event','manifestation/show?id='.$ticket->Manifestation->id) ?>"><?php echo format_datetime($ticket->Manifestation->happens_at) ?></a></td>
<td></td>

