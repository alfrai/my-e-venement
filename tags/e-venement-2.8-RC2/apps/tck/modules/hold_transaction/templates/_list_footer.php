<a href="<?php echo url_for('hold_transaction/changeRank?id=PUT_THIS_ID_HERE&smaller_than=PUT_SMALLER_ID_HERE&bigger_than=PUT_BIGGER_ID_HERE&hold[previous]=PUT_PREV_HOLD_ID_HERE&hold[next]=PUT_NEXT_HOLD_ID_HERE') ?>"
  id="change-rank"
  data-replace-smaller="PUT_SMALLER_ID_HERE"
  data-replace-bigger="PUT_BIGGER_ID_HERE"
  data-replace-this="PUT_THIS_ID_HERE"
  data-replace-hold-before="PUT_PREV_HOLD_ID_HERE"
  data-replace-hold-after="PUT_NEXT_HOLD_ID_HERE"
></a>
<input type="hidden" name="nb_seats" value="<?php echo Doctrine::getTable('Seat')->createQuery('s')
  ->leftJoin('s.Holds h')
  ->andWhere('h.id = ?', sfConfig::get('module_hold_id', 0))
  ->count()
?>" />
<a id="next" href="<?php echo url_for('hold_transaction/index?next_to=PUT_HOLD_ID_HERE') ?>" data-replace-hold="PUT_HOLD_ID_HERE">
  <input type="hidden" name="hold_id" value="<?php echo sfConfig::get('module_hold_id', 0) ?>" />
</a>
<span id="hold_name" style="display: none"><?php echo $hold = Doctrine::getTable('Hold')->find(sfConfig::get('module_hold_id', 0)) ?></span>
<input type="hidden" value="<?php echo $hold->color ?>" name="hold_color" />
