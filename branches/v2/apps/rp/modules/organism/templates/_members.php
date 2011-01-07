<div class="sf_admin_edit ui-widget ui-widget-content ui-corner-all members">
<div class="ui-widget-header ui-corner-all fg-toolbar"><h2><?php echo __("Organism's members") ?></h2></div>
<ul class="contacts">
<?php foreach ( $organism->Professionals as $professional ): ?>
<li>
  <a class="file" href="<?php echo url_for('contact/edit?id='.$professional->Contact['id']) ?>"><?php echo $professional->Contact ?></a>
  <span class="professional"><?php echo $professional ?> (<?php echo $professional->ProfessionalType ?>)</span>
</li>
<?php endforeach ?>
</ul>
<p class="nb"><?php echo $organism->Professionals->count() ?> <?php echo __('element(s)') ?></p>
</div>
