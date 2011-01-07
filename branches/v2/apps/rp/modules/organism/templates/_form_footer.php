<?php if ( !$organism->isNew() ): ?>
  <div id="more">
    <?php include_partial('organism/members', array('organism' => $organism, 'form' => $form, 'configuration' => $configuration)) ?>
  </div>
<?php endif ?>
