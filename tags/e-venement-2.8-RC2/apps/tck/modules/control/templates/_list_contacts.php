<?php if ( intval($control->ticket_id).'' === ''.$control->ticket_id ): ?>
<ul>

<?php if ( $control->Ticket->contact_id ): ?>
<li class="direct_contact">
  <?php echo cross_app_link_to($control->Ticket->DirectContact, 'rp', 'contact/show?id='.$control->Ticket->contact_id) ?>
</li>
<?php endif ?>

<?php if ( $control->Ticket->Transaction->contact_id ): ?>
<li class="contact">
  <?php echo cross_app_link_to($control->Ticket->Transaction->Contact, 'rp', 'contact/show?id='.$control->Ticket->Transaction->contact_id) ?>
</li>
<?php endif ?>

</ul>
<?php endif ?>
