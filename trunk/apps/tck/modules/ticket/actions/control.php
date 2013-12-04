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
    sfContext::getInstance()->getConfiguration()->loadHelpers(array('CrossAppLink','I18N'));
    $this->form = new ControlForm();
    $this->form->getWidget('checkpoint_id')->setOption('default', $this->getUser()->getAttribute('control.checkpoint_id'));
    $q = Doctrine::getTable('Checkpoint')->createQuery('c');
    
    $past = sfConfig::get('app_control_past') ? sfConfig::get('app_control_past') : '6 hours';
    $future = sfConfig::get('app_control_future') ? sfConfig::get('app_control_future') : '1 day';
    
    $q->leftJoin('c.Event e')
      ->leftJoin('e.Manifestations m')
      ->andWhere('m.happens_at < ?',date('Y-m-d H:i',strtotime('now + '.$future)))
      ->andWhere('m.happens_at >= ?',date('Y-m-d H:i',strtotime('now - '.$past)));
    $this->form->getWidget('checkpoint_id')->setOption('query',$q);
    
    // retrieving the configurate field
    switch ( sfConfig::get('app_tickets_id') ) {
    case 'qrcode':
      $field = 'barcode';
      break;
    case 'othercode':
      $field = 'othercode';
      break;
    default:
      $field = 'id';
      break;
    }
    
    if ( count($request->getParameter($this->form->getName())) > 0 )
    {
      $params = $request->getParameter($this->form->getName());
      
      // creating tickets ids array
      if ( $field != 'othercode' )
      {
        $tmp = explode(',',$params['ticket_id']);
        if ( count($tmp) == 1 )
          $tmp = preg_split('/\s+/',$params['ticket_id']);
        $params['ticket_id'] = array();
        foreach ( $tmp as $key => $ids )
        {
          $ids = explode('-',$ids);
          
          if ( count($ids) > 0 && isset($ids[1]) )
          for ( $i = intval($ids[0]) ; $i <= intval($ids[1]) ; $i++ )
            $params['ticket_id'][$i] = $i;
          else
            $params['ticket_id'][] = intval($ids[0]);
        }
        
        // decode EAN if it exists
        if ( $field == 'id' )
        {
          foreach ( $params['ticket_id'] as $key => $value )
          if ( (strlen($value) == 13 || strlen($value) == 12 ) && substr($value,0,1) === '0' )
          {
            try { $value = liBarcode::decode_ean($value); }
            catch ( sfException $e )
            { $value = intval($value); }
            $params['ticket_id'][$key] = $value;
          }
        }
      }
      else
        $params['ticket_id'] = array($params['ticket_id']);
      
      // filtering the checkpoints
      if ( isset($params['ticket_id'][0]) && $params['ticket_id'][0] )
      {
        $q->leftJoin('m.Tickets t')
          ->whereIn('t.'.$field,$params['ticket_id']);
      }
      
      if ( intval($params['checkpoint_id'])."" == $params['checkpoint_id'] && count($params['ticket_id']) > 0 )
      {
        $q = Doctrine::getTable('Checkpoint')->createQuery('c')
          ->leftJoin('c.Event e')
          ->leftJoin('e.Manifestations m')
          ->leftJoin('m.Tickets t')
          ->andWhereIn('t.'.$field,$params['ticket_id'])
          ->andWhere('c.id = ?',$params['checkpoint_id']);
        $checkpoint = $q->fetchOne();
        
        $cancontrol = $checkpoint instanceof Checkpoint;
        if ( $cancontrol && $checkpoint->legal )
        {
          $q = Doctrine::getTable('Control')->createQuery('c')
            ->leftJoin('c.Checkpoint c2')
            ->leftJoin('c2.Event e')
            ->leftJoin('e.Manifestations m')
            ->leftJoin('m.Tickets t')
            ->leftJoin('c.Ticket tc')
            ->andWhereIn('tc.'.$field,$params['ticket_id'])
            ->andWhere("tc.$field = t.$field")
            ->andWhere('c.checkpoint_id = ?',$params['checkpoint_id'])
            ->orderBy('c.id DESC');
          $controls = $q->execute();
          $cancontrol = $controls->count() == 0;
        }
        
        $this->getUser()->setAttribute('control.checkpoint_id',$params['checkpoint_id']);
        
        if ( $cancontrol )
        {
          $this->comment = $params['comment'];
          
          if ( $checkpoint->id )
          {
            if ( sfConfig::get('app_tickets_id') != 'id' )
            {
              $params['ticket_id'] = $params['ticket_id'][0];
              $this->form->bind($params);
              if ( $this->form->isValid() )
              {
                $this->form->save();
                $this->setTemplate('passed');
              }
              else
              {
                unset($params['ticket_id']);
                $this->form->bind($params);
              }
            }
            else
            {
              $ids = $params['ticket_id'];
              $err = array();
              foreach ( $ids as $id )
              {
                $params['ticket_id'] = $id;
                $this->form = new ControlForm;
                $this->form->bind($params,$request->getFiles($this->form->getName()));
                if ( $this->form->isValid() )
                  $this->form->save();
                else
                  $err[] = $id;
              }
              $this->errors = $err;
              $this->setTemplate('passed');
            }
          }
          else
          {
            if ( !$params['checkpoint_id'] )
            {
              $this->getUser()->setFlash('error',__("Don't forget to specify a checkpoint"));
              //unset($params['checkpoint_id']);
              $params['ticket_id'] = implode(',',$params['ticket_id']);
              $this->form->bind($params);
            }
            else
              $this->setTemplate('failed');
          }
        }
        else
        {
          $this->setTemplate('failed');
        }
      }
      else
      {
        $this->getUser()->setFlash('error',__("Don't forget to specify a checkpoint and a ticket id"));
      }
    }
