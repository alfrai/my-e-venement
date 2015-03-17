<?php foreach ( $contact->Professionals as $pro ): ?>
<div class="pro pro-<?php echo $pro->id ?>">
<ul>
  <?php if ( $pro->contact_email ): ?>
  <li class="perso" title="<?php echo __("Contact's email") ?>"><a href="mailto:<?php echo $pro->contact_email ?>"><?php echo $pro->contact_email ?></a></li>
  <?php endif ?>
  
  <?php if ( $pro->Organism->email ): ?>
  <li class="org" title="<?php echo __("Organism's email") ?>"><a href="mailto:<?php echo $pro->Organism->email ?>"><?php echo $pro->Organism->email ?></a></li>
  <?php endif ?>
</ul>
</div>
<?php endforeach ?>
