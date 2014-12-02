<form id="comment" method="get" action="<?php echo url_for('transaction/addComment?id='.$transaction->id) ?>">
<h3><?php echo __('Any comment?') ?></h3>
<script type="text/javascript">$(document).ready(function(){
  $('#comment textarea').change(function(){
    $.ajax({ url: $(this).closest('form').prop('action'), data: $(this).closest('form').serialize() });
  });
});</script>
<?php if ( $transaction->sf_guard_user_id == $sf_user->getId() ): ?>
<?php echo $form['description'] ?>
<?php echo $form->renderHiddenFields() ?>
<?php else: ?>
<p class="textarea"><?php echo nl2br($transaction->description) ?></p>
<?php endif ?>
</form>
