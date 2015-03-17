<div class="sf_admin_form_row sf_admin_form_show_relationships_list">
  <label><?php echo __('Linked contacts') ?>:</label>
  <?php if ( $contact->Relationships->count() ): ?>
  <ol><?php foreach ( $contact->Relationships as $relationship ): ?>
    <?php if ( !$relationship->isNew() ): ?>
    <li><?php echo link_to($relationship,'contact/show?id='.$relationship->Contact->id) ?></li>
    <?php endif ?>
  <?php endforeach ?></ol>
  <?php endif ?>
</div>
