<div id="customer">
  <p class="name inline-modifiable"><?php echo $transaction->Contact ?></p>
  <p class="phonenumber inline-modifiable"><?php
    echo is_null($transaction->professional_id)
    ? $transaction->Contact->Phonenumbers[0]->number
    : ($transaction->Professional->contact_number ? $transaction->Professional->contact_number : $transaction->Professional->Organism->Phonenumbers[0]->number)
  ?></p>
  <p class="orgname inline-modifiable"><?php echo $transaction->Professional->Organism->name ?></p>
  <p class="address inline-modifiable"><?php echo nl2br(ucwords(strtolower($transaction->professional_id ? $transaction->Professional->Organism->address : $transaction->Contact->address))) ?></p>
  <p class="postalcode inline-modifiable"><?php echo $transaction->professional_id ? $transaction->Professional->Organism->postalcode : $transaction->Contact->postalcode ?></p>
  <p class="city inline-modifiable"><?php echo strtoupper($transaction->professional_id ? $transaction->Professional->Organism->city : $transaction->Contact->city) ?></p>
  <p class="country inline-modifiable"><?php echo strtoupper($transaction->professional_id ? $transaction->Professional->Organism->country : $transaction->Contact->country) ?></p>
  <p class="email inline-modifiable"><?php echo $transaction->professional_id ? $transaction->Professional->Organism->email : $transaction->Contact->email ?></p>
</div>
