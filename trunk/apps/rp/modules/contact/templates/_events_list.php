<?php use_helper('Date') ?>
<ul>
<?php foreach ($contact->Transactions as $transaction): ?>
  <li>
    #<?php echo link_to($transaction,'tansaction/sell?id='.$transaction->id) ?>
    le <?php format_date($transaction->created_at) ?>
  </li>
<?php endforeach ?>
</ul>
