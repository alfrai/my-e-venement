<?php use_helper('Number') ?>
  <table><tbody>
    <tr>
      <th><?php echo __('Number of persons') ?></th>
      <td><?php echo format_number($values['nb']) ?></td>
    </tr>
    <tr>
      <th><?php echo __('Number of tickets') ?></th>
      <td><?php echo format_number($values['tickets']) ?></td>
    </tr>
    <tr>
      <th><?php echo __('Number of tickets by person') ?></th>
      <td><?php echo $values['nb'] != 0 ? format_number(round($values['tickets']/$values['nb'],2)) : 'N/A' ?></td>
    </tr>
    <tr>
      <th><?php echo __('Number of events by person') ?></th>
      <td><?php echo $values['nb'] != 0 ? format_number(round($values['events']/$values['nb'],2)) : 'N/A' ?></td>
    </tr>
    <tr>
      <th><?php echo __('Number of unique persons') ?>*</th>
      <td><?php echo $values['events'] != 0 ? format_number(round($values['nb']*$values['tickets']/$values['events'],0)) : 'N/A' ?></td>
    </tr>
  </tbody></table>
