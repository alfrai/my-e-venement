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

require_once dirname(__FILE__).'/../lib/groupGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/groupGeneratorHelper.class.php';

/**
 * group actions.
 *
 * @package    e-venement
 * @subpackage group
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class groupActions extends autoGroupActions
{
  public function executeEmailing(sfWebRequest $request)
  {
    $q = new Doctrine_Query;
    $group = $q->from('Group g')
      ->leftJoin('g.Contacts c')
      ->leftJoin('g.Professionals p')
      ->leftJoin('g.Organisms o')
      ->andWhere('g.id = ?',$request->getParameter('id'))
      ->fetchOne();

    $email = new Email;
    foreach ( array('Contacts','Professionals','Organisms') as $type )
    foreach ( $group->$type as $obj )
    {
      $coll =& $email->$type;
      $coll[] = $obj;
    }
    $email->field_from = $this->getUser()->getGuardUser()->email_address;
    $email->field_subject = '-*-*-*-*-*-*-*-*-*-*-';
    $email->content = '<p>-*-*-*-*-*-*-*-*-*-*-</p>';
    $email->save();
    
    $this->redirect('email/edit?id='.$email->id);
  }
  
  public function executeDelPicture(sfWebRequest $request)
  {
    $q = Doctrine_Query::create()->from('Picture p')
      ->where('p.id IN (SELECT g.picture_id FROM Group g WHERE g.id = ?)',$request->getParameter('id'))
      ->delete()
      ->execute();
    return $this->redirect('group/edit?id='.$request->getParameter('id'));
  }
  
  public function executeEdit(sfWebRequest $request)
  {
    parent::executeEdit($request);
    
    /**
      * if the user cannot modify anything
      * if the user cannot modify common groups and this group is common
      * if the group is not his own
      *
      **/
    if ( !$this->getUser()->hasCredential('pr-group-perso')
      && !$this->getUser()->hasCredential('pr-group-common')
      || is_null($this->group->sf_guard_user_id)
      && !$this->getUser()->hasCredential('pr-group-common')
      || $this->group->sf_guard_user_id !== $this->getUser()->getId()
      && !is_null($this->group->sf_guard_user_id) )
    $this->setTemplate('show');
  }

  public function executeIndex(sfWebRequest $request)
  {
    parent::executeIndex($request);
    if ( !$this->sort[0] )
    {
      $this->sort = array('name','');
      $this->pager->getQuery()->orderby('username IS NULL DESC, username, name');
    }
  }

  public function executeCsv(sfWebRequest $request)
  {
    $criterias = array(
      'groups_list'           => array(sfContext::getInstance()->getRequest()->getParameter('id')),
      'organism_id'           => NULL,
      'organism_category_id'  => NULL,
      'professional_type_id'  => NULL,
    );
    $this->getUser()->setAttribute('contact.filters', $criterias, 'admin_module');
    
    $this->forward('contact','index');
    return sfView::NONE;
    
    $q = $this->createQueryByRoute()
      ->leftJoin('c.Phonenumbers cpn')
      ->limit(1);
    $groups = $q->execute();
    $this->group = $groups[0];
    
    $this->outstream = 'php://output';
    $this->delimiter = $request->hasParameter('ms') ? ';' : ',';
    $this->enclosure = '"';
    $this->charset   = sfConfig::get('software_internals_charset');
    
    $criterias = $this->getUser()->getAttribute('contact.filters', $this->configuration->getFilterDefaults(), 'admin_module');
    $this->options = array(
      'ms'        => $request->hasParameter('ms'),
      'noheader'  => $request->hasParameter('noheader'),
    );
    
    sfConfig::set('sf_web_debug', false);
    sfConfig::set('sf_escaping_strategy', false);
    sfConfig::set('sf_charset', $this->options['ms'] ? $this->charset['ms'] : $this->charset['db']);
    
    if ( !$request->hasParameter('debug') )
    {
      $this->getResponse()->clearHttpHeaders();
      $this->getResponse()->setContentType('text/comma-separated-values');
      $this->getResponse()->addHttpMeta('content-disposition', 'attachment; filename="'.$this->getModuleName().'s-list.csv"',true);
      $this->getResponse()->sendHttpHeaders();
    }
    
    $this->setLayout(false);
  }
  
  protected function createQueryByRoute()
  {
    $q = Doctrine_Query::create()
      ->from('Group g')
      ->leftJoin("g.User u")
      ->leftJoin("g.Contacts c")
      ->leftJoin("g.Professionals p")
      ->leftJoin("p.ProfessionalType pt")
      ->leftJoin("p.Contact pc")
      ->leftJoin("p.Organism o")
      ->orderBy('c.name, c.firstname, pc.name, pc.firstname, o.name, pt.name, p.name');
    if ( sfContext::getInstance()->getRequest()->getParameter('id') )
    $q->where('id = ?',sfContext::getInstance()->getRequest()->getParameter('id'));
    
    return $q;
  }
  protected function getObjectByRoute()
  {
    $groups = $this->createQueryByRoute()->limit(1)->execute();
    return $groups[0];
  }
}

