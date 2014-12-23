<?php foreach ( array('deposit') as $field ): ?>
<?php echo $form->renderFormTag(url_for('transaction/complete?id='.$transaction->id), array(
  'target' => '_blank',
  'method' => 'get',
)) ?>
<p>
  <?php echo $form->renderHiddenFields() ?>
  <?php echo $form[$field]->renderLabel() ?>
  <input type="hidden" name="transaction[<?php echo $field ?>]" value="off" />
  </span><?php echo $form[$field] ?></span>
</p>
</form>
<?php endforeach ?>
<?php if ( $transaction->SurveyAnswersGroups->count() > 0 ): ?>
<a href="<?php echo cross_app_url_for('srv', 'answer/index?filters[transaction_id]='.$transaction->id) ?>" class="tdp-surveys" target="_blank">
  <?php echo format_number_choice('[1]1 answer to a survey|(1,+Inf]%%nb%% answers to surveys', array('%%nb%%' => $transaction->SurveyAnswersGroups->count()), $transaction->SurveyAnswersGroups->count()) ?>
</a>
<?php endif ?>
