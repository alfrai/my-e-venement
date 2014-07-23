<?php echo Doctrine_Query::create()
  ->from('SeatedPlan sp')
  ->select('sp.id, count(s.id) nb_seats')
  ->leftJoin('sp.Seats s')
  ->andWhere('sp.id = ?',$seated_plan->id)
  ->groupBy('sp.id')
  ->fetchOne()
  ->nb_seats ?>
