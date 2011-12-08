<?php include_partial('global/flashes') ?>
<?php echo $card->renderFormTag(url_for('contact/card')) ?><p>
  <select name="member_card[name]">
    <?php foreach ( sfConfig::get('app_cards_types') as $type ): ?>
    <option value="<?php echo $type ?>"><?php echo __($type); ?></option>
    <?php endforeach ?>
  </select>
  <input type="checkbox" name="duplicate" value="yes" title="<?php echo __("Duplicates the card instead of creating a new one") ?>"/>
  <input type="submit" name="submit" value="Ok" />
  <input type="hidden" name="member_card[_csrf_token]" value="<?php echo $card->getCSRFToken() ?>" />
  <input type="hidden" name="member_card[contact_id]" value="<?php echo $form->getObject()->id ?>" />
</p></form>
