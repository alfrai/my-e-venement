<?php echo $form->renderFormTag(url_for('@ticket_contact')) ?>
  <p>
    <span class="title"><?php echo __('Contact') ?>:</span>
    <?php echo $form->renderHiddenFields() ?>
    <span class="contact">
      <?php if ( is_null($transaction->contact_id) ): ?>
        <?php echo $form['contact_id'] ?>
      <?php else: ?>
        <a href="<?php echo cross_app_url_for('rp','contact/show?id='.$transaction->contact_id) ?>"><?php echo $transaction->Contact ?></a>
      <?php endif ?>
    </span>
    <?php if ( !is_null($transaction->contact_id) ): ?>
    -
    <span class="professional">
    <?php if ( is_null($transaction->professional_id) ): ?>
      <?php echo $form['professional_id'] ?>
    <?php else: ?>
      <?php echo $transaction->Professional ?>
    <?php endif ?>
    </span>
    <?php endif ?>
  </p>
</form>
