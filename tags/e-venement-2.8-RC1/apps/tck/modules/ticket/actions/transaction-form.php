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
    $this->form = new TransactionForm($this->transaction);
    
    // all fields to hide those wanted
    foreach ( $this->form->getWidgetSchema()->getFields() as $name => $widget )
    if ( !in_array($name,$excludes) )
    {
      $this->form->setWidget($name, new sfWidgetFormInputHidden());
    }
    
    // contact
    if ( $parameters )
    {
      $this->form->bind($parameters);
      if ( $this->form->isValid() )
      {
        $event = $this->form->save();
        if ( !is_null($this->transaction->contact_id) )
          $this->form->setWidget('contact_id', new sfWidgetFormInputHidden());
      }
    
      $this->dispatcher->notify(new sfEvent($this, 'admin.save_object', array('object' => $event)));
    }
    
    // professional
    if ( !is_null($this->transaction->contact_id) && in_array('professional_id',$excludes) )
    {
      $cid = $this->transaction->contact_id;
      $query = Doctrine::getTable('Professional')->createQuery('p')
        ->andWhere('p.contact_id = ?',$cid);
      
      $proid = $this->form->getWidget('professional_id')
        ->setOption('query', $query);
      $this->form->getValidator('professional_id')
        ->setOption('query', $query);
    }
    
    return $this->form;
