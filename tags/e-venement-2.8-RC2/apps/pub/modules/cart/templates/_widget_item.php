<?php use_helper('Number') ?>

    <td class="type"><?php echo $label ?></td>
    <td class="operand">x</td>
    <td class="qty"><?php echo isset($nb) ? $nb : $objects->count() ?></td>
    <td class="operand">=</td>
    <td class="value"><?php echo format_currency($price,'€') ?></td>
