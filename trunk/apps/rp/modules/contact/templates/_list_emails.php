<ul>
  <?php if ( $contact->email ): ?>
  <li class="perso" title="<?php echo __("Contact's email") ?>"><a href="mailto:<?php echo $contact->email ?>">
    <?php if ( $contact->email_npai ): ?>
      <span class="alert"><?php echo $contact->email ?></span>
    <?php else: ?>
      <?php echo $contact->email ?>
    <?php endif ?>
  </a></li>
  <?php endif ?>
  
  <?php if ( !sfConfig::get('app_options_design',false) ): ?>
  <?php foreach ( $contact->Professionals as $pro ): ?>
  <?php if ( $pro->Organism->email ): ?>
  <li class="org" title="<?php echo __("Organism's email") ?>"><a href="mailto:<?php echo $pro->Organism->email ?>">
    <?php if ( $organism->email_npai ): ?>
      <span class="alert"><?php echo $organism->email ?></span>
    <?php else: ?>
      <?php echo $organism->email ?>
    <?php endif ?>
  </a></li>
  </a></li>
  <?php endif ?>
  <?php endforeach ?>
  <?php endif ?>
</ul>
