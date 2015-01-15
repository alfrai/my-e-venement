<?php if ( intval($control->ticket_id).'' === ''.$control->ticket_id ): ?>
<?php
  $groups = array();
  foreach ( $control->Ticket->DirectContact->Groups as $group )
    $groups[$group->id] = $group;
  foreach ( $control->Ticket->Transaction->Contact->Groups as $group )
    $groups[$group->id] = $group;
  foreach ( $control->Ticket->Transaction->Professional->Groups as $group )
    $groups[$group->id] = $group;
  foreach ( $control->Ticket->Transaction->Professional->Organism->Groups as $group )
    $groups[$group->id] = $group;
?>
<?php if ( count($groups) > 0 ): ?>
<ul>
  <?php foreach ( $groups as $group ): ?>
  <li><?php echo $group->getRawValue()->getHtmlTag() ?></li>
  <?php endforeach ?>
</ul>
<?php endif ?>
<?php endif ?>
