<?php echo $web_origin->Transaction->contact_id
  ? cross_app_link_to($web_origin->Transaction->Contact, 'rp', 'contact/edit?id='.$web_origin->Transaction->contact_id)
  : ''
?>
