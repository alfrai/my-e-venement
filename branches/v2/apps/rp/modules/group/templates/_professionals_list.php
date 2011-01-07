<div class="sf_admin_edit ui-widget ui-widget-content ui-corner-all members">
<div class="ui-widget-header ui-corner-all fg-toolbar"><h2><?php echo __("Group's professional members") ?></h2></div>
<ul class="professionals">
<?php foreach ( $group->Professionals as $professional ): ?>
<li>
  <strong><a class="file" href="<?php echo url_for('contact/edit?id='.$professional->Contact['id']) ?>"><?php echo $professional->Contact ?></a></strong>,
  <span class="professional"><?php echo $professional->name ? $professional : $professional->ProfessionalType ?></span>
  <?php echo __('at') ?>
  <a href="<?php echo url_for('organism/show?id='.$professional->Organism['id']) ?>"><?php echo $professional->Organism ?></a>
</li>
<?php endforeach ?>
</ul>
<p class="nb"><?php echo $group->Professionals->count() ?> <?php echo __('element(s)') ?></p>
</div>
