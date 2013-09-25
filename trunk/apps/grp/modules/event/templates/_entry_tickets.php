<?php $form->restrictGaugeIdQuery() ?>
<?php $form->restrictPriceIdQuery() ?>
<?php echo form_tag_for($form,'@entry_tickets') ?>
  <p title="<?php echo __('Press ENTER to add those tickets') ?>">
    <span class="quantity"><?php echo $form['quantity'] ?></span><span class="price_id"><?php echo $form['price_id'] ?></span><!--<input type="submit" name="submit" value="<?php echo __('Save',null,'sf_admin') ?>" />--><?php if ( !$form->isNew() ): ?><a class="delete" href="<?php echo url_for('entry_tickets/del?id='.$form->getObject()->id) ?>" title="<?php echo __('Delete') ?>">X</a><?php endif ?><?php echo $form['gauge_id'] ?>
    <?php echo $form->renderHiddenFields() ?>
    <input type="hidden" name="<?php echo $form['entry_element_id']->renderName() ?>" value="<?php echo $entry_element->id ?>" />
  </p>
</form>
