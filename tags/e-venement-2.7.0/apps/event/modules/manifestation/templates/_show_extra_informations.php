<div class="sf_admin_form_row sf_admin_form_field_ExtraInformations show">
  <label><?php echo __('Extra informations') ?>:</label>
  <table>
    <tbody>
      <?php foreach ( $manifestation->ExtraInformations as $info ): ?>
      <?php if ( $info->name ): ?>
      <tr>
        <th><?php echo $info->name ?></th>
        <td><?php echo $info->value ?></td>
        <td><?php echo image_tag($info->checked ? '/sfDoctrinePlugin/images/tick.png' : '/sfDoctrinePlugin/images/delete.png') ?></td>
      </tr>
      <?php endif ?>
      <?php endforeach ?>
    </tbody>
  </table>
</div>
