<?php use_helper('Date') ?>
<td class="version"><?php echo $version->version ?></td>
<td class="user"><?php echo $version->user ?></td>
<td class="date"><?php echo format_datetime($version->updated_at) ?></td>
<td class="price_name"><?php echo $version->price_name ?></td>
<td class="seat_name"><?php echo $version->seat_id ? Doctrine::getTable('Seat')->find($version->seat_id) : '-' ?></td>
<td class="printed"><?php echo image_tag( $version->printed_at || $version->integrated_at ? '/sfDoctrinePlugin/images/tick.png' : '/sfDoctrinePlugin/images/delete.png') ?></td>
