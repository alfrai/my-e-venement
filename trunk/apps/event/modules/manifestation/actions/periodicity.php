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
    $errmsg = __('Try again with valid informations.');
    
    if ( $this->form->isValid() && $request->getParameter('periodicity',array()) )
    {
      $details = array('blocking' => false, 'reservation_optional' => NULL, 'reservation_confirmed' => NULL);
      if ( $this->getUser()->hasCredential('event-reservation-confirm') )
        $details['blocking'] = NULL;
      
      $this->manifestation = Doctrine::getTable('Manifestation')->findOneById($periodicity['manifestation_id']);
      switch ( $periodicity['behaviour'] ) {
      case 'one_occurrence':
        // preconditions
        foreach ( array('day', 'month', 'year') as $period )
        if (!( isset($periodicity['one_occurrence'][$period]) && $periodicity['one_occurrence'][$period] ))
        {
          $this->getUser()->setFlash('error', $errmsg);
          $this->redirect('manifestation/periodicity?id='.$this->manifestation->id);
        }
        
        // happens_at and reservation fields updating
        $time = strtotime($periodicity['one_occurrence']['year'].'-'.$periodicity['one_occurrence']['month'].'-'.$periodicity['one_occurrence']['day'].' '.date('H:i',strtotime($this->manifestation->happens_at)));
        $diff = $time - strtotime($this->manifestation->happens_at);
        
        // periodicity stuff
        $manif = $this->manifestation->duplicate(false); // duplicating w/o saving (for the moment)
        $manif->happens_at            = date('Y-m-d H:i',$time);
        $manif->reservation_ends_at   = date('Y-m-d H:i',strtotime($manif->reservation_ends_at)+$diff);
        $manif->reservation_begins_at = date('Y-m-d H:i',strtotime($manif->reservation_begins_at)+$diff);
        
        // booking details
        foreach ( $details as $field => $value )
          $manif->$field = is_null($value) ? isset($periodicity['options'][$field]) : $value;
        
        $manif->save();
        
        // redirect
        $this->getUser()->setFlash('success',__('Manifestation duplicated successfully.'));
        $this->redirect('manifestation/edit?id='.$manif->id);
        break;
      
      case 'until':
        // particular preconditions
        $max = array();
        foreach ( $fields = array('day', 'month', 'year') as $fieldname )
        if ( !(isset($periodicity['until'][$fieldname]) && intval($periodicity['until'][$fieldname])) > 0 )
        {
          $this->getUser()->setFlash('error',$errmsg);
          $this->redirect('manifestation/periodicity?id='.$this->manifestation->id);
        }
        
        // removing extra-fields
        foreach ( $periodicity['until'] as $key => $value )
        if ( !in_array($key,$fields) )
          unset($periodicity['until'][$key]);
        
        // calculating the time that the duplication has to stop before...
        $maxtime = strtotime('+ 1 day',strtotime(implode('-',array_reverse($periodicity['until']))));
        
      case 'nb':
        // particular preconditions
        if ( $periodicity['behaviour'] == 'nb'
        && !(isset($periodicity['nb']) && intval($periodicity['nb']) > 0) )
        {
          $this->getUser()->setFlash('error',$errmsg);
          $this->redirect('manifestation/periodicity?id='.$this->manifestation->id);
        }
        
        // general preconditions
        if ( !(isset($periodicity['repeat']['hours']) && intval($periodicity['repeat']['hours']) > 0)
          && !(isset($periodicity['repeat']['days'])  && intval($periodicity['repeat']['days']) > 0)
          && !(isset($periodicity['repeat']['weeks']) && intval($periodicity['repeat']['weeks']) > 0)
          && !(isset($periodicity['repeat']['month']) && intval($periodicity['repeat']['month']) > 0)
          && !(isset($periodicity['repeat']['years']) && intval($periodicity['repeat']['years']) > 0)
        )
        {
          $this->getUser()->setFlash('error',$errmsg);
          $this->redirect('manifestation/periodicity?id='.$this->manifestation->id);
        }
        
        // interval calculation
        $interval = 0;
        foreach ( array('days', 'weeks', 'month', 'years') as $fieldname )
        if ( intval($periodicity['repeat'][$fieldname]) > 0 )
          $interval = strtotime('+'.intval($periodicity['repeat'][$fieldname]).' '.$fieldname,$interval);
        
        // duplication
        $cpt = 0;
        $manif = $this->manifestation->duplicate(false);
        
        // booking details
        foreach ( $details as $field => $value )
          $manif->$field = is_null($value) ? isset($periodicity['options'][$field]) : $value;
        
        // date / periodicity related stuff
        for (
          $i = 0 ;
          $periodicity['behaviour'] == 'nb'
            ? $i < intval($periodicity['nb'])
            : strtotime($manif->happens_at) + $interval < $maxtime ;
          $i++
        )
        {
          foreach ( array('happens_at', 'reservation_begins_at', 'reservation_ends_at') as $field )
          {
            // to avoid timezone (winter/summer times) mistakes duplicating a manifestation
            $local_interval = $interval;
            if ( ($from = date('P',strtotime($manif->$field))) != ($to = date('P', strtotime($manif->$field) + $interval)) )
              $local_interval = $interval + strtotime($to) - strtotime($from);

            $manif->$field = date('Y-m-d H:i:s',strtotime($manif->$field) + $local_interval);
          }
          
          $next_manif = $manif->duplicate(false);
          $manif->save();
          $manif = $next_manif;
          $cpt++;
        }
        
        // redirect
        $this->getUser()->setFlash('success',__('%%nb%% manifestation(s) have been created during the duplication process.',array('%%nb%%',$cpt)));
        $this->redirect('event/edit?id='.$this->manifestation->event_id);
        break;
      }
      
      $this->getUser()->setFlash('error',$errmsg);
      $this->redirect('manifestation/periodicity?id='.$this->manifestation->id);
    }
    else
    {
      try {
        $this->manifestation = $this->getRoute() instanceof sfObjectRoute
          ? $this->getRoute()->getObject()
          : Doctrine::getTable('Manifestation')->findOneById($request->getParameter('id'));
      }
      catch ( Doctrine_Table_Exception $e )
      {
        $this->getUser()->setFlash('error',__('Unknown manifestation.'));
        $this->redirect('@event');
      }
    }
