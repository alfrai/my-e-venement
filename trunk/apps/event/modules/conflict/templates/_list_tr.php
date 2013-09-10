<?php foreach ( $columns as $column => $label ): ?>
      <?php if ( $show_nb || !in_array($column, array('resource', 'date')) ): ?>
        <td class="sf_admin_text conflicts_td_<?php echo $column ?>" <?php if ( in_array($column, array('resource', 'date')) ): ?>rowspan="<?php echo $show_nb ? $show_nb : 1 ?>"<?php endif ?>>
          <?php include_partial('list_'.$column, array('manifestation' => $manifestations[$i], 'conflict' => $conflict)) ?>
        </td>
      <?php endif ?>
<?php endforeach ?>
