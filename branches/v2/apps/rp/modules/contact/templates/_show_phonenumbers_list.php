<div class="sf_admin_form_row">
<!--<label><?php echo __('Phone numbers') ?></label>-->
<ul class="show_phonenumbers">
  <?php foreach ( $form->getObject()->Phonenumbers as $number ): ?>
  <li class="phones">
    <span class="phonename"><?php echo $number->name ?></span>
    <span class="phonenumber"><?php echo $number->number ?></span>
  </li>
  <?php endforeach ?>
</ul>
</div>
