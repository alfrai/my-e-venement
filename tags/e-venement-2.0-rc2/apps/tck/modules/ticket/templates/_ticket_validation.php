<form action="<?php echo url_for('ticket/validate?id='.$transaction->id) ?>" method="get">
  <p>
    <input type="submit" value="<?php echo __('Verify and validate') ?>" name="verify" />
    <?php echo link_to(__('New transaction'),'ticket/sell') ?> (<?php echo __('with the same initial selections') ?>)
  </p>
</form>
<script type="text/javascript">
  $(document).ready(function(){
    $('#validation a').click(function(){
      var hashtag = [];
      $('.manifestations_list .manif input[name="ticket[manifestation_id]"]').each(function(){
        hashtag.push('#manif-'+$(this).val());
      });
      $(this).attr('href',$(this).attr('href')+hashtag.join(','));
      return true;
    });
  });
</script>
