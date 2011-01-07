<?php $phonenumbers = $form->getObject()->Phonenumbers ?>
<?php use_javascript('phonenumbers') ?>
<?php use_stylesheet('phonenumbers') ?>
<script type="text/javascript">var phonenumbers = []; var pnid = '#organism_phonenumber_id';</script>
<div class="sf_admin_form_row">
<!--<label><?php echo __('Phone numbers') ?></label>-->
<ul class="form_phonenumbers">
  <script type="text/javascript">
    <?php foreach ( $phonenumbers as $number ): ?>
    phonenumbers.push('<?php echo url_for('organism_phonenumber/edit?id='.$number['id']) ?>');
    <?php endforeach ?>
    phonenumbers.push('<?php echo url_for('organism_phonenumber/new') ?>');
  </script>
</ul>
</div>

