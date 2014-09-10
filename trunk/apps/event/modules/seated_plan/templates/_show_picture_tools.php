    <div class="class"><label><?php echo __('Shape') ?></label><input type="text" name="yummy" title="<?php echo __('3 letters minimum') ?>" class="class" pattern="\w\w\w+" value="" /></div>
    <div class="regexp"><label><?php echo __('Regexp') ?></label><input type="text" name="yummy" class="regexp" value="^[A-Za-z\-\.\/']*" /></div>
    <div class="name-hop"><label><?php echo __('Hop') ?></label><input type="text" name="yummy" class="hop" value="1" size="2" /></div>
    <div class="auto"><label><?php echo __('Do not ask') ?></label><input type="checkbox" name="yummy" class="donotask" value="1" /></div>
    <div class="show_links">
      <label><?php echo __('Show neighborhood') ?></label>
      <input type="checkbox" name="yummy" class="show_links" value="1" onclick="javascript: LI.seatedPlanMoreDataInitialization($(this).parent().find('a').prop('href'), $(this).prop('checked'));" />
      <a href="<?php echo url_for('seated_plan/getLinks?id='.$seated_plan->id) ?>"></a>
    </div>
