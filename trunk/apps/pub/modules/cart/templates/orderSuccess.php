<?php include_partial('global/ariane',array('active' => 4)) ?>
<?php include_partial('global/oplog') ?>
<script type="text/javascript"><!--
  $(document).ready(function(){
    $('form.autosubmit').submit();
  });
--></script>
<h1><?php echo __('Payment of your order') ?></h1>
<h3><?php echo __('Choose your payment method') ?> :</h3></br>
<?php echo $sf_data->getRaw('online_payment') ?>
