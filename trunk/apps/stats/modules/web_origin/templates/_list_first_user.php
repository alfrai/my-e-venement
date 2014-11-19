<?php $transaction = Doctrine::getTable('Transaction')->createQuery('t')
  ->leftJoin('t.Version v WITH v.version = ?', 1)
  ->select('t.id, (SELECT u.username FROM sfGuardUser u WHERE u.id = v.sf_guard_user_id) AS user')
  ->andWhere('t.id = ?', $web_origin->transaction_id)
  ->fetchOne();
?>
<?php echo $transaction->user ?>
