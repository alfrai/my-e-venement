<?php

$this->dispatcher->connect(
    'user.method_not_found',
    array(
        'nvDoctrineSessionStorage',
        'listenToUserMethodNotFoundEvent'
    )
);
