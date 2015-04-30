<?php if ( $survey_answer->Group->transaction_id ): ?>
#<?php echo cross_app_link_to(
  $survey_answer->Group->transaction_id,
  'tck',
  'transaction/edit?id='.$survey_answer->Group->transaction_id
) ?>
<?php endif ?>
