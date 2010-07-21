<?php /*

The procedure :

== getting informations ==

1. vet/get-infos.php
auth key given in _GET[key] (required)
optionnally manifestations can be given through the _GET[manifs][] array to do a focus on them


== the customer procedure ==

... the customer fill in his cart ...

1. identification : do-identification.php
auth key given in _GET[key] (required)
email given in _GET[email] (required)
last name (family name) given in _GET[name] (required)
password given in _GET[passwd] (required)
if ok, continues

... the customer fill in his cart ...

2. reservation (doing the payment possible) : do-reservation.php
auth key given in _GET[key] (required)
json array given in _POST[json] (required)
it represents the customer's command :
  ex:
  array(
    array(
      manifid => int8,
      tarif   => char5 (key),
      qty     => int8,
    ),
    [...]
  );

... the customer pay online his command ...

3. payment (if the online procedure was ok) : do-payment.php
auth key given in _GET[key] (required)
the amount paid given in a string of the amount in _GET[paid] (required)

4. done !!

*/ ?>
