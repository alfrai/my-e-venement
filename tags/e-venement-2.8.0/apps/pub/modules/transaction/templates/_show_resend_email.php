<div id="actions">
<div class="actions resend">
<?php echo link_to(sfConfig::get('app_texts_cart_resend_email', false) ? pubConfiguration::getText('app_texts_cart_resend_email') : __('Resend the tickets as they were modified'), 'transaction/sendEmail?id='.$transaction->id) ?>
</div>
</div>
