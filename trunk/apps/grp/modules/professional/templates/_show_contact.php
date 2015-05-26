  <p class="contact">
    <?php echo cross_app_link_to($professional->Contact,'rp','contact/show?id='.$professional->Contact->id) ?>
    (<?php echo cross_app_link_to($professional->Organism,'rp','organism/show?id='.$professional->Organism->id) ?>
    -
    <?php echo $professional->name ?>)
    <span class="picto"><?php echo $professional->getRaw('groups_picto') ?></span>
  </p>

