<?php

/**
 * Transaction
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    e-venement
 * @subpackage model
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class Transaction extends PluginTransaction
{
  public function getNotPrinted()
  {
    $toprint = 0;
    foreach ( $this->Tickets as $ticket )
    if ( is_null($ticket->duplicate) && !$ticket->printed && !$ticket->integrated && is_null($ticket->cancelling) )
      $toprint++;
    return $toprint;
  }
  public function getPrice()
  {
    $price = 0;
    foreach ( $this->Tickets as $ticket )
    if ( is_null($ticket->duplicate) && ($ticket->printed || $ticket->integrated || !is_null($ticket->cancelling)) )
      $price += $ticket->value;
    return $price;
  }
  public function getPaid()
  {
    $paid = 0;
    foreach ( $this->Payments as $payment )
      $paid += $payment->value;
    return $paid;
  }
}
