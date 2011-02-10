<td class="sf_admin_text sf_admin_list_td_name">
  <a href="<?php echo url_for('checkpoint/show?id='.$checkpoint->id) ?>"><?php echo $checkpoint->name ?>
</td>
<td class="sf_admin_text sf_admin_list_td_Organism">
  <a href="<?php echo url_for('organism/show?id='.$checkpoint->organism_id) ?>"><?php echo $checkpoint->Organism ?></a>
</td>
<td class="sf_admin_text sf_admin_list_td_legal">
  <?php echo $checkpoint->legal ?>
</td>
