<?php echo $form->renderFormTag(url_for('@ticket_contact')) ?>
  <div class="open"></div>
  <div id="micro-show" class="ui-widget-content ui-corner-all">
    <div class="fg-toolbar ui-widget-header ui-corner-all">
      <h3><?php echo __('Contact') ?></h3>
    </div>
  </div>
  <p>
    <span class="title"><?php echo __('Contact') ?>:</span>
    <?php echo $form->renderHiddenFields() ?>
    <span class="contact">
      <?php if ( !is_null($transaction->contact_id) && $transaction->Contact->confirmed ): ?>
        <a href="<?php echo cross_app_url_for('rp','contact/show?id='.$transaction->contact_id) ?>"><?php echo $transaction->Contact ?></a> <span class="picto"><?php echo $transaction->getRaw('Contact')->groups_picto ?></span>
        <?php $form->setWidget('contact_id',new sfWidgetFormInputHidden) ?>
      <?php endif ?>
      <?php echo $form['contact_id'] ?>
      <a href="<?php echo cross_app_url_for('rp','contact/ajax') ?>" style="display: none;"></a>
    </span>
    <?php if ( !is_null($transaction->contact_id) ): ?>
    -
    <span class="professional">
    <?php if ( is_null($transaction->professional_id) ): ?>
      <?php echo $form['professional_id'] ?>
    <?php else: ?>
      <?php echo $transaction->Professional ?>
      <span class="picto"><?php echo $transaction->getRaw('Professional')->groups_picto ?></span>
    <?php endif ?>
    </span>
    <?php endif ?>
    <?php if ( !is_null($transaction->contact_id) ): ?>
      <a href="#" class="delete-contact"></a>
    <?php else: ?>
      <a href="<?php echo cross_app_url_for('rp','contact/new') ?>?close" target="_blank" class="create-contact"></a>
    <?php endif ?>
  </p>
</form>
