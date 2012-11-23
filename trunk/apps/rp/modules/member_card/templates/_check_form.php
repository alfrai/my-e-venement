<form method="get" action="<?php echo url_for('member_card/check') ?>">
  <p><input type="text" name="id" value="" /></p>
  <p class="submit"><button value="" name="s"><?php echo __('Search') ?></button></p>
</form>
<script type="text/javascript"><!--
  $(document).ready(function(){
    $('input[type=text]:first-child').focus();
  });
--></script>
