<?php
/**********************************************************************************
*
*	    This file is part of e-venement.
*
*    e-venement is free software; you can redistribute it and/or modify
*    it under the terms of the GNU General Public License as published by
*    the Free Software Foundation; either version 2 of the License.
*
*    e-venement is distributed in the hope that it will be useful,
*    but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*    GNU General Public License for more details.
*
*    You should have received a copy of the GNU General Public License
*    along with e-venement; if not, write to the Free Software
*    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*
*    Copyright (c) 2006-2011 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2011 Ayoub HIDRI <ayoub.hidri AT gmail.com>
*    Copyright (c) 2006-2011 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php
class CorrectMemberCardsPricesTask extends sfBaseTask
{
  protected function configure() {
    $this->addArguments(array(
    ));
    $this->addOptions(array(
      new sfCommandOption('execute', null, sfCommandOption::PARAMETER_NONE, "Execute the DB update"),
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application', 'event'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environement', 'dev')
    ));
    $this->namespace = 'e-venement';
    $this->name = 'correct-member-cards-prices';
    $this->briefDescription = 'Correct member cards (printed tickets / available prices)';
    $this->detailedDescription = <<<EOF
      The [correct-member-cards-prices|INFO] Correct automatically member cards regarding to the printed tickets and available prices: 
      [./symfony e-venement:correct-member-cards-prices --execute|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    $databaseManager = new sfDatabaseManager($this->configuration);
    
    $q = Doctrine_Query::create()->from('Ticket tck')
      ->leftJoin('tck.Price p')
      ->leftJoin('tck.Transaction t')
      ->leftJoin('t.Contact c')
      ->leftJoin('c.MemberCards mc')
      ->leftJoin('mc.MemberCardPrices mcp')
      ->andWhere('p.member_card_linked = true')
      ->andWhere('tck.member_card_id IS NULL')
      ->andWhere('tck.printed = true')
      ->andWhere('mcp.price_id = p.id');
    $tickets = $q->execute();
    
    if ( !$options['execute'] )
    {
      $cpt = $tickets->count();
      $this->logSection('finished', $cpt.' ticket(s) and their member cards have to be corrected.');
      return true;
    }
    
    $ok = $ko = 0;
    foreach ( $tickets as $ticket )
    if ( !( $ticket->Transaction->Contact->MemberCards[0] && $ticket->Transaction->Contact->MemberCards[0]->MemberCardPrices[0] ) )
      $ko++;
    else
    {
      $ticket->Transaction->Contact->MemberCards[0]->MemberCardPrices[0]->delete();
      $ticket->member_card_id = $ticket->Transaction->Contact->MemberCards[0]->id;
      $ticket->save();
      $ok++;
    }
    
    $this->logSection('error', $ko.' ticket(s) and their member could not be corrected.');
    $this->logSection('finished', $ok.' ticket(s) and their member cards have been corrected.');
  }
}
