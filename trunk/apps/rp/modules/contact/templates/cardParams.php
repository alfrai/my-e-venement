<?php include_partial('contact/assets') ?>
<?php include_partial('global/flashes') ?>
<?php echo $card->renderFormTag(url_for('contact/card'),array('class' => 'ui-widget-content ui-corner-all', 'id' => 'member_cards')) ?>
  <div class="fg-toolbar ui-widget-header ui-corner-all">
    <h1><?php echo __("%%c%%'s member cards",array('%%c%%' => $contact)) ?></h1>
  </div>
  <div class="ui-widget-content ui-corner-all new">
    <select name="member_card[name]">
      <?php foreach ( sfConfig::get('app_cards_types') as $type ): ?>
      <option value="<?php echo $type ?>"><?php echo __($type); ?></option>
      <?php endforeach ?>
    </select>
    <input type="checkbox" name="duplicate" value="yes" title="<?php echo __("Duplicates the card instead of creating a new one") ?>"/>
    <input type="submit" name="submit" value="Ok" />
    <input type="hidden" name="member_card[_csrf_token]" value="<?php echo $card->getCSRFToken() ?>" />
    <input type="hidden" name="member_card[contact_id]" value="<?php echo $form->getObject()->id ?>" />
  </div>
  <ul class="ui-widget-content ui-corner-all list">
    <?php require_once(dirname(__FILE__).'/../lib/MemberCardHelper.class.php'); ?>
    <?php $cpt = 0 ?>
    <?php foreach ( $contact->MemberCards as $card ): ?>
    <?php if ( strtotime($card->expire_at) > strtotime('now') ): ?>
      <li class="card"><?php echo $card ?></li>
      <?php $card_helper = new MemberCardHelper(); echo $card_helper->linkToDelete($card, array(  'params' =>   array(  ),  'confirm' => 'Are you sure?',  'class_suffix' => 'delete',  'label' => 'Delete',)) ?></li>
      <?php $cpt++ ?>
    <?php endif ?>
    <?php endforeach ?>
    <?php if ( $cpt == 0 ): ?>
      <li><?php echo __('No card') ?></li>
    <?php endif ?>
  </ul>
</form>
