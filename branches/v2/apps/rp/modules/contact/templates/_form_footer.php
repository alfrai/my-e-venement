<?php if ( !$contact->isNew() ): ?>
<div id="more">
  <?php include_partial('contact/professionals_edit', array('contact' => $contact, 'form' => $form, 'configuration' => $configuration)) ?>
</div>
<?php endif ?>
