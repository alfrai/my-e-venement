<td class="sf_admin_text sf_admin_list_td_name">
  <a href="<?php echo url_for('checkpoint/show?id='.$checkpoint->id) ?>"><?php echo $checkpoint->name ?>
</td>
<td class="sf_admin_text sf_admin_list_td_Organism">
  <a href="<?php echo cross_app_url_for('rp','organism/show?id='.$checkpoint->organism_id) ?>"><?php echo $checkpoint->Organism ?></a>
</td>
<td class="sf_admin_text sf_admin_list_td_legal">
  <?php
    echo $checkpoint->legal
      ? image_tag('/sfDoctrinePlugin/images/tick.png')
      : image_tag('/sfDoctrinePlugin/images/delete.png');
  ?>
</td>
