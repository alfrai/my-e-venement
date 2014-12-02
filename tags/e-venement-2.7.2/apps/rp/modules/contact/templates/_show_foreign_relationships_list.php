<?php if ( $contact->ForeignRelationships->count() ): ?>
<div class="sf_admin_form_row sf_admin_form_show_foreign_relationships_list">
  <label><?php echo __('Referenced by') ?>:</label>
  <ol><?php foreach ( $contact->ForeignRelationships as $relationship ): ?>
    <?php if ( !$relationship->isNew() ): ?>
    <li><?php echo link_to($relationship,'contact/show?id='.$relationship->Contact->id) ?></li>
    <?php endif ?>
  <?php endforeach ?></ol>
</div>
<?php endif ?>
