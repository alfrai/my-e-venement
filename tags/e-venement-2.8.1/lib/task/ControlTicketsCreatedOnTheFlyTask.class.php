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
class ControlTicketsCreatedOnTheFlyTask extends sfBaseTask
{
  protected function configure() {
    $this->addArguments(array(
      new sfCommandArgument('manifestation_id', sfCommandArgument::REQUIRED, "The Manifestation's id"),
      new sfCommandArgument('checkpoint_id', sfCommandArgument::REQUIRED, "The Checkpoint's id"),
      new sfCommandArgument('user_id', sfCommandArgument::REQUIRED, "The checker's id (sf_guard_user.id)"),
    ));
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application', 'event'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environement', 'dev')
    ));
    $this->namespace = 'e-venement';
    $this->name = 'control-tickets-created-on-the-fly';
    $this->briefDescription = 'Control tickets created on-the-fly';
    $this->detailedDescription = <<<EOF
      The [aptaw:control-tickets-created-on-the-fly|INFO] Control automatically all the tickets created on-the-fly the day of a manifestation for this manifestation, which have not been controlled...:
      [./symfony e-venement:control-tickets-created-on-the-fly manifestation_id checkpoint_id user_id --env=dev|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    $databaseManager = new sfDatabaseManager($this->configuration);
    
    // preconditions
    $checkpoint = Doctrine::getTable('Checkpoint')->createQuery('c')
      ->andWhere('c.id = ?',$options['checkpoint_id'])
      ->fetchOne();
    if ( !$checkpoint )
      throw new sfException('Checkpoint does not exist');
    $user = Doctrine::getTable('SfGuardUser')->createQuery('u')
      ->andWhere('u.id = ?',$arguments['user_id'])
      ->fetchOne();
    if ( !$user )
      throw new sfException('User does not exist');
    
    $q = new Doctrine_Query;
    $q->from('Ticket t')
      ->leftJoin('t.Controls ctrl')
      ->leftJoin('t.Cancelling t2')
      ->leftJoin('t.Transaction tr')
      ->leftJoin('tr.Contact c')
      ->leftJoin('t.Manifestation m')
      ->andWhere('t.manifestation_id = ?',$arguments['manifestation_id'])
      ->andWhere('ctrl.id IS NULL')
      ->andWhere('t.duplicate IS NULL')
      ->andWhere('t2.id IS NULL')
      ->orderBy('id');
    $tickets = $q->execute();
    
    $cpt = 0;
    foreach ( $tickets as $ticket )
    if ( date('Y-m-d',strtotime($ticket->updated_at)) == date('Y-m-d',strtotime($ticket->Manifestation->happens_at)) )
    {
      $control = new Control;
      $control->checkpoint_id = $checkpoint->id;
      $control->sf_guard_user_id = $user->id;
      $control->ticket_id = $ticket->id;
      $control->created_at = $ticket->updated_at;
      $this->logSection('controlled', sprintf('Ticket %s for transaction #%s (%s) has been controlled',$control->Ticket,($t = $control->Ticket->Transaction),$t->Contact));
      $control->save();
      $cpt++;
    }
    
    $this->logSection('finished', $cpt.' ticket(s) have been controlled');
  }
}
