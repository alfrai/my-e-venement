    <div class="regexp"><label><?php echo __('Regexp') ?></label><input type="text" name="yummy" class="regexp" value="^[A-Za-z\-\.\/']*" /></div>
    <div class="name-hop"><label><?php echo __('Hop') ?></label><input type="text" name="yummy" class="hop" value="1" size="2" /></div>
    <div class="auto"><label><?php echo __('Do not ask') ?></label><input type="checkbox" name="yummy" class="donotask" value="1" /></div>
    <div class="show_links">
      <label><?php echo __('Show Links') ?></label>
      <input type="checkbox" name="yummy" class="show_links" value="1" onclick="javascript: LI.seatedPlanLinksInitialization($(this).parent().find('a').prop('href'), $(this).prop('checked'));" />
      <a href="<?php echo url_for('seated_plan/getLinks?id='.$seated_plan->id) ?>"></a>
    </div>
    <div class="magnify">
      <label><?php echo __('Magnify') ?></label>
      <a href="#" class="magnify-in">+</a>
      /
      <a href="#" class="magnify-zero">0</a>
      /
      <a href="#" class="magnify-out">-</a>
    </div>

