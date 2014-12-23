<div class="ui-corner-all ui-widget-content">

<?php echo $form['store']->integrate->renderFormTag(url_for('transaction/complete?id='.$transaction->id), array(
  'method' => 'get',
  'target' => '_blank',
  'autocomplete' => 'off',
)) ?>
  <?php echo $form['store']->integrate ?>
  <input class="ui-widget-content ui-state-default ui-corner-all ui-widget fg-button" type="submit" value="<?php echo __('Products delivered') ?>" name="integrate"></input>
</form>

</div>
