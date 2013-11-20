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
*    Copyright (c) 2006-2011 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php
  sfContext::getInstance()->getConfiguration()->loadHelpers(array('I18N','CrossAppLink'));
  
  $control = $request->getParameter('control');
  
  $form = new BatchControlForm();
  $form->bind($control);
  
  if ( !$form->isValid() )
  {
    $this->getUser()->setFlash('error',__("Attaque CSRF détectée. Impossible d'effectuer le contrôle global demandé."));
    $this->redirect($this->getRequest()->getReferer());
  }
  else
  {
    if ( $control['type'] == 'cancel' )
    {
      $q = Doctrine::getTable('Control')->createQuery('ctrl')
        ->andWhere('ctrl.checkpoint_id = ?',$control['checkpoint_id'])
        ->delete();
      $q->execute();
      $this->getUser()->setFlash('notice',__("Tous les billets ont été retiré du point de contrôle."));
    }
    else
    {
      $q = Doctrine::getTable('Ticket')->createQuery('tck')
        ->andWhere('manifestation_id = ?',$control['manifestation_id']);
      $tickets = $q->execute();
      
      foreach ( $tickets as $ticket )
      {
        $ctrl = new Control();
        $ctrl->ticket_id = $ticket->id;
        $ctrl->checkpoint_id = $control['checkpoint_id'];
        $ctrl->save();
      }
    } // if ( $control['type'] == 'cancel' )
    $this->redirect(cross_app_url_for('event','manifestation/show?id='.$control['manifestation_id']));
  }
  
