<div class="sf_admin_edit ui-widget ui-widget-content ui-corner-all members">
<div class="ui-widget-header ui-corner-all fg-toolbar"><h2><?php echo __("Group's individual members") ?></h2></div>
<ul class="contacts">
<?php foreach ( $group->Contacts as $contact ): ?>
<li>
  <strong><a class="file" href="<?php echo url_for('contact/edit?id='.$contact['id']) ?>"><?php echo $contact ?></a></strong>,
</li>
<?php endforeach ?>
</ul>
<p class="nb"><?php echo $group->Contacts->count() ?> <?php echo __('element(s)') ?></p>
</div>
