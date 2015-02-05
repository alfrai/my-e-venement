<a href="<?php echo url_for('hold_transaction/changeRank?id=PUT_THIS_ID_HERE&smaller_than=PUT_SMALLER_ID_HERE&bigger_than=PUT_BIGGER_ID_HERE') ?>"
  id="change-rank"
  data-replace-smaller="PUT_SMALLER_ID_HERE"
  data-replace-bigger="PUT_BIGGER_ID_HERE"
  data-replace-this="PUT_THIS_ID_HERE"
></a>
<input type="hidden" name="nb_seats" value="<?php echo Doctrine::getTable('Seat')->createQuery('s')
  ->leftJoin('s.Holds h')
  ->andWhere('h.id = ?', sfConfig::get('module_hold_id', 0))
  ->count()
?>" />
