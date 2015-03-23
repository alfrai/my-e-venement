<ul>
  <?php if ( $contact->email ): ?>
  <li class="perso" title="<?php echo __("Contact's email") ?>"><a href="mailto:<?php echo $contact->email ?>"><?php echo $contact->email ?></a></li>
  <?php endif ?>
  
  <?php if ( !sfConfig::get('app_options_design',false) ): ?>
  <?php foreach ( $contact->Professionals as $pro ): ?>
  <?php if ( $pro->Organism->email ): ?>
  <li class="org" title="<?php echo __("Organism's email") ?>"><a href="mailto:<?php echo $pro->Organism->email ?>"><?php echo $pro->Organism->email ?></a></li>
  <?php endif ?>
  <?php endforeach ?>
  <?php endif ?>
</ul>
