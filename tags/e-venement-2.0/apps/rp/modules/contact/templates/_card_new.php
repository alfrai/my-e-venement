    <ul id="card_type_list">
    <?php foreach ( sfConfig::get('app_cards_types') as $type ): ?>
      <li><input type="radio" value="<?php echo $type ?>" name="member_card[name]"><span style="cursor: default;">&nbsp;<?php echo __($type); ?></span></li>
    <?php endforeach ?>
    </ul>
    <script type="text/javascript">
      $(document).ready(function(){
        $('#card_type_list li > span').unbind().click(function(){
          $(this).parent().find('input').click();
        });
      });
    </script>
    <span id="card_type_actions">
      <input type="checkbox" name="duplicate" value="yes" title="<?php echo __("Duplicates the last card instead of creating a new one") ?>"/>
      <input type="submit" name="submit" value="Ok" />
      <span title="<?php echo __('Optional, printing date') ?>"><?php $date = new liWidgetFormJQueryDateText(); echo $date->render('member_card[created_at]'); ?></span>
      <input type="hidden" name="member_card[_csrf_token]" value="<?php echo $card->getCSRFToken() ?>" />
      <input type="hidden" name="member_card[contact_id]" value="<?php echo $form->getObject()->id ?>" />
    </span>
