<ul>
<?php foreach ( $event->Companies as $cie ): ?>
<?php if ( !sfConfig::get('app_show_company_id', false) || $cie->Category->id == sfConfig::get('app_show_company_id', false) ): ?>
  <li><?php echo $cie ?></li>
<?php endif ?>
<?php endforeach ?>
</ul>
