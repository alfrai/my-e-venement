<ul class="show_groups">
  <?php foreach ( $contact->Groups as $group ): ?>
  <li><a href="<?php echo url_for('group/show?id='.$group->id) ?>"><?php echo $group ?></a></li>
  <?php endforeach ?>
</ul>
