<td class="event"><?php echo cross_app_link_to($event,'event','event/show?id='.$event->id) ?></td>
<td class="see-more"><a href="#event-<?php echo $event->id ?>">-</a></td>
<td class="id-qty"><?php echo $qty ?></td>
<td class="value"><?php echo format_currency($value,'€') ?></td>
<td class="extra-taxes"><?php echo format_currency($taxes,'€') ?></td>
<?php foreach ( $vat as $name => $v ): ?>
<td class="vat">
  <?php
    $tmp = 0;
    if ( isset($v[$event->id]) )
    foreach ( $v[$event->id] as $m )
      $tmp += round($m,2);
    $local_vat += $tmp;
  ?>
  <?php echo format_currency($tmp,'€') ?>
</td>
<?php endforeach ?>
<td class="vat total"><?php echo format_currency($local_vat,'€'); ?></td>
<td class="tep"><?php echo format_currency($value + $taxes - round($local_vat,2),'€') ?></td>
