<?php foreach ( sfConfig::get('tdp_transaction_selling',array()) as $id => $detail ): ?>
<div id="li_transaction_<?php echo $id ?>" class="bunch">
  <h2 class="ui-widget-header ui-corner-all"><?php echo $detail['title'] ?></h2>
  <?php if ( isset($form[$id]) && $form->getRaw($id) instanceof sfForm ): ?>
  <?php echo $form[$id]->renderFormTag(url_for('transaction/getManifestations'), array(
    'autocomplete' => false,
    'method' => 'get',
    'target' => '_blank',
    'class'  => 'new-family',
  )) ?>
    <?php echo $form[$id] ?>
  </form>
  <?php endif ?>
  <?php include_partial('form_field_content_bunch', array(
    'form' => $form,
    'transaction' => $transaction,
    'detail' => $detail,
    'id' => $id,
  )) ?>
</div>
<?php endforeach ?>
<script type="text/javascript" style="display: none;">
  $(document).ready(function(){
    $('#li_transaction_field_content .new-family [name=manifestation_id]').change(function(){
      if ( $(this).val() )
      {
        console.log('submit new manifestation');
        $(this).closest('form').submit();
      }
    });
  });
</script>
