<div id="customer">
  <p class="name"><?php echo $transaction->Contact ?></p>
  <p class="orgname"><?php echo $transaction->Professional->Organism ?></p>
  <p class="address"><?php echo $transaction->professional_id ? $transaction->Professional->Organism->address : $transaction->Contact->address ?></p>
  <p class="postalcode"><?php echo $transaction->professional_id ? $transaction->Professional->Organism->postalcode : $transaction->Contact->postalcode ?></p>
  <p class="city"><?php echo $transaction->professional_id ? $transaction->Professional->Organism->city : $transaction->Contact->city ?></p>
  <p class="country"><?php echo $transaction->professional_id ? $transaction->Professional->Organism->country : $transaction->Contact->country ?></p>
  <p class="email"><?php echo $transaction->professional_id ? $transaction->Professional->Organism->email : $transaction->Contact->email ?></p>
</div>
