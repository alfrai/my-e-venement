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
*    Foundation, Inc., 5'.$rank.' Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*
*    Copyright (c) 2006-2011 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2011 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php
    sfContext::getInstance()->getConfiguration()->loadHelpers('I18N');
    
    $this->form = new BaseForm;
    $periodicity = $request->getParameter('periodicity');
    $this->form->bind(array(
      $this->form->getCSRFFieldName() => $periodicity[$this->form->getCSRFFieldName()]
    ));
    if ( $this->form->isValid() && $request->getParameter('periodicity',array()) )
    {
      $this->manifestation = Doctrine::getTable('Manifestation')->findOneById($periodicity['manifestation_id']);
      switch ( $periodicity['behaviour'] ) {
      case 'nb':
        throw new liEvenementException('Not yet implemented');
        break;

      case 'until':
        throw new liEvenementException('Not yet implemented');
        break;

      case 'one_occurrence':
        // preconditions
        foreach ( array('day', 'month', 'year') as $period )
        if (!( isset($periodicity['one_occurrence'][$period]) && $periodicity['one_occurrence'][$period] ))
        {
          $this->getUser()->setFlash('error',__('Try again with valid informations.'));
          $this->redirect('manifestation/periodicity?id='.$this->manifestation->id);
        }
        
        // happens_at and reservation fields updating
        $time = strtotime($periodicity['one_occurrence']['year'].'-'.$periodicity['one_occurrence']['month'].'-'.$periodicity['one_occurrence']['day'].' '.date('H:i',strtotime($this->manifestation->happens_at)));
        $diff = $time - strtotime($this->manifestation->happens_at);
        
        $manif = $this->manifestation->duplicate(false); // duplicating w/o saving (for the moment)
        $manif->happens_at            = date('Y-m-d H:i',$time);
        $manif->reservation_ends_at   = date('Y-m-d H:i',strtotime($manif->reservation_ends_at)+$diff);
        $manif->reservation_begins_at = date('Y-m-d H:i',strtotime($manif->reservation_begins_at)+$diff);
        $manif->save();
        
        $this->redirect('manifestation/edit?id='.$manif->id);
        break;
      }
      
      $this->redirect('event/edit?id='.$this->manifestation->event_id);
    }
    else
    {
      $this->manifestation = $this->getRoute() instanceof sfObjectRoute
        ? $this->getRoute()->getObject()
        : Doctrine::getTable('Manifestation')->findOneById($request->getParameter('id'));
    }
