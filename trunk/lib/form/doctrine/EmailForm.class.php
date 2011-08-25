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

/**
 * Email form.
 *
 * @package    e-venement
 * @subpackage form
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class EmailForm extends BaseEmailForm
{
  public function configure()
  {
    // disabling cc / bbc fields
    unset($this->widgetSchema   ['field_cc']);
    unset($this->validatorSchema['field_cc']);
    unset($this->widgetSchema   ['field_bcc']);
    unset($this->validatorSchema['field_bcc']);
    $this->widgetSchema['field_from']->setOption('default',
      sfContext::getInstance()->getUser()->getGuardUser()->getEmailAddress()
    );
    
    // organism
    $this->widgetSchema['organisms_list'] = new cxWidgetFormDoctrineJQuerySelectMany(array(
      'model' => 'Organism',
      'url'   => url_for('organism/ajax?email=true'),
      'order_by' => array('name',''),
    ));
    $this->widgetSchema['contacts_list'] = new cxWidgetFormDoctrineJQuerySelectMany(array(
      'model' => 'Contact',
      'url'   => url_for('contact/ajax?email=true'),
      'order_by' => array('name,firstname',''),
    ));
    $this->widgetSchema['professionals_list'] = new cxWidgetFormDoctrineJQuerySelectMany(array(
      'model' => 'Professional',
      'url'   => url_for('professional/ajax?email=true'),
      'method'=> 'getFullName',
      'order_by' => array('c.name,c.firstname,o.name,t.name,p.name',''),
    ));
    
    $this->widgetSchema['content'] = new sfWidgetFormTextareaTinyMCE(array(
      'width'   => 650,
      'height'  => 420,
      'config'  => 'extended_valid_elements: "hr[class|width|size|noshade],iframe[src|width|height|name|align],style"',
    ));
    
    $this->widgetSchema   ['load'] = new sfWidgetFormInputText();
    $this->validatorSchema['load'] = new sfValidatorUrl(array(
      'required' => false,
    ));
    
    // validation / test forms
    $this->widgetSchema   ['test_address'] = new sfWidgetFormInputText();
    $this->validatorSchema['test_address'] = new sfValidatorEmail(array(
      'required'    => true,
    ));
  }
  
  public function getFields()
  {
    $fields = parent::getFields();
    $fields['validation']   = 'Validation';
    $fields['test_address'] = 'TestAdress';
  }
}
