<?php foreach ( sfConfig::get('tdp_transaction_selling',array()) as $id => $detail ): ?>
<?php
  // SECURITY
  $go = false;
  if ( isset($detail['credentials']) && $detail['credentials'] )
  foreach ( is_array($detail['credentials']) ? $detail['credentials'] : array($detail['credentials']) as $creds )
  {
    if ( is_array($creds) )
    {
      $go = false;
      foreach ( $creds as $cred )
        $go = $go || $sf_user->hasCredential($cred);
    }
    else
      $go = $sf_user->hasCredential($creds);
    
    if ( !$go ) return;
  }
?>
<div id="li_transaction_<?php echo $id ?>" class="bunch" data-bunch-id="<?php echo $id ?>">
  <h2 class="ui-widget-header ui-corner-all"><?php echo $detail['title'] ?></h2>
  <?php if ( isset($form[$id]) && $form->getRaw($id) instanceof sfForm ): ?>
  <?php echo $form[$id]->renderFormTag(url_for($detail['data_url']), array(
    'autocomplete' => 'off',
    'method' => 'get',
    'target' => '_blank',
    'class'  => 'new-family highlight ui-corner-all ui-widget-content board-alpha',
  )) ?><p>
    <?php echo $form[$id] ?>
    <input type="text" name="autocompleter" value="" />
    <?php $opt = sfConfig::get('app_transaction_'.$id,array()) ?>
    <select name="<?php echo strtolower($detail['model']) ?>_id[]" multiple="multiple" data-content-url="<?php echo cross_app_url_for($detail['choices_url'][0], $detail['choices_url'][1].'?except_transaction='.$transaction->id.'&display_by_default=1') ?>" data-content-qty="<?php echo isset($opt['max_display']) ? $opt['max_display'] : 10 ?>"><option></option></select>
    <input type="submit" name="s" onclick="javascript: return false;" value="<?php echo __('Go') ?>" />
  </p></form>
  <?php endif ?>
  <?php include_partial('form_field_content_bunch', array(
    'form' => $form,
    'transaction' => $transaction,
    'detail' => $detail,
    'id' => $id,
  )) ?>
  <div class="footer">
  <?php try { include_partial('form_field_content_'.$id.'_footer', array(
    'form' => $form,
    'transaction' => $transaction,
    'detail' => $detail,
    'id' => $id,
  )); } catch ( sfRenderException $e ) { } ?>
  </div>
</div>
<?php endforeach ?>
<?php use_javascript('tck-touchscreen-new-family') ?>
