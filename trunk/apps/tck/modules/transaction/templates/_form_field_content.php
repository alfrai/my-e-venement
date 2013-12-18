<?php foreach ( sfConfig::get('tdp_transaction_selling',array()) as $id => $detail ): ?>
<div id="li_transaction_<?php echo $id ?>" class="bunch" data-bunch-id="<?php echo $id ?>">
  <h2 class="ui-widget-header ui-corner-all"><?php echo $detail['title'] ?></h2>
  <?php if ( isset($form[$id]) && $form->getRaw($id) instanceof sfForm ): ?>
  <?php echo $form[$id]->renderFormTag(url_for('transaction/getManifestations'), array(
    'autocomplete' => false,
    'method' => 'get',
    'target' => '_blank',
    'class'  => 'new-family highlight ui-corner-all ui-widget-content board.alpha',
  )) ?><p>
    <?php echo $form[$id] ?>
    <input type="text" name="autocompleter" value="" />
    <select name="manifestation_id[]" multiple="multiple" data-content-url="<?php echo cross_app_url_for('event', 'manifestation/ajax?except_transaction='.$transaction->id) ?>"><option></option></select>
    <input type="submit" name="s" value="<?php echo __('Ok') ?>" onclick="javascript: return false;" />
  </p></form>
  <?php endif ?>
  <?php include_partial('form_field_content_bunch', array(
    'form' => $form,
    'transaction' => $transaction,
    'detail' => $detail,
    'id' => $id,
  )) ?>
</div>
<?php endforeach ?>
<?php use_javascript('tck-touchscreen-new-family') ?>
