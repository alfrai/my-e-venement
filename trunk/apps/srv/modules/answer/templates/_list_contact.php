<?php if ( $survey_answer->Group->contact_id ): ?>
<?php echo cross_app_link_to(
  $survey_answer->Group->professional_id ? $survey_answer->Group->Professional : $survey_answer->Group->Contact,
  'rp',
  'contact/edit?id='.$survey_answer->Group->contact_id
) ?>
<?php endif ?>
