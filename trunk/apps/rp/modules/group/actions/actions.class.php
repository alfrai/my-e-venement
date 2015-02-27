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
  public function executeMember(sfWebRequest $request)
  {
    $this->getContext()->getConfiguration()->loadHelpers('I18N');
    $this->executeEdit($request);
    
    /*
    if ( $this->form->getCSRFToken() !== $request->getParameter('_csrf_token') )
      throw new liEvenementException('CSRF Attack detected: '.$this->form->getCSRFToken().' - '.$request->getParameter('_csrf_token'));
    */
    
    $r = array();
    
    //try
    {
      // is the asked model is supported
      $validator = new sfValidatorChoice(array(
        'choices' => array('contact', 'professional', 'organism'),
      ), array('required' => 'Required.', 'invalid' => sprintf('Unsupported model %s.',$request->getParameter('type','unknown'))) );
      $type = $validator->clean($request->getParameter('type'));
      
      // is the asked action is supported
      $validator = new sfValidatorChoice(array(
        'choices' => array('remove', 'add'),
      ),array('required' => 'Required.', 'invalid' => sprintf('Unsupported modifier %s.',$request->getParameter('modifier','unknown'))) );
      $modifier = $validator->clean($request->getParameter('modifier'));
      
      // tweaking the error messages
      $invalid = array(
        'remove' => __('Invalid or impossible to remove from this groupe because not a part of.'),
        'add' => __('Invalid or impossible to add to this group because already a part of.'),
      );
      
      $q = Doctrine_Query::create()->from(ucfirst($type).' o')
        ->leftJoin('o.Groups g');
      
      // validating the current targetted object
      $relations = array('contact' => 'ContactGroups', 'organism' => 'OrganismGroups', 'professional' => 'ProfessionalGroups');
      $validator = new sfValidatorDoctrineChoice(array(
        'model' => ucfirst($type),
        'required' => true,
        'query' => $q->copy()->select('o.id')
          ->leftJoin(sprintf('g.%s og ON og.group_id = ? AND og.group_id = g.id AND og.%s_id = o.id', $relation = $relations[$type], $type), $this->form->getObject()->id)
          ->having(sprintf('count(og.group_id) %s',$modifier == 'add' ? '= 0' : '= 1'))
          ->groupBy('o.id') // big but beautiful SQL hack...
      ), array('required' => 'Required.', 'invalid' => $invalid[$modifier]));
      $object_id = $validator->clean($request->getParameter('object_id')); // throws an exception if it doesn't validate
      
      // adding / removing the object from the group
      $object = $q->andWhere('o.id = ?',$object_id)->select('o.*, g.*')->fetchOne();
      if ( $modifier == 'add' )
      {
        $object->Groups[] = $this->form->getObject();
        $object->save();
      }
      else
      {
        $rel = $object->$relation;
        $del = new GroupDeleted; // save the deletion for stats
        $del->created_at = $rel[0]->created_at;
        $del->group_id   = $rel[0]->group_id;
        //$del->information = $rel[0]->information;
        $rel[0]->delete();
        $del->save();
      }
      
      // messages
      $r['success'] = __(ucfirst($type).' '.($modifier == 'add' ? 'added' : 'removed'));
      $r['object_id'] = $object->id;
    }
    //catch ( sfValidatorError $e )
    {
      //$r['error'] = __($e->getMessage(), null, 'sf_admin');
    }
    
    $this->content = $r;
    if (!( sfConfig::get('sf_web_debug', false) && $request->hasParameter('debug') ))
      return 'Json';
  }
  
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
  
  public function executeShow(sfWebRequest $request)
  {
    $this->group = $this->getRoute()->getObject();
    $this->form = $this->configuration->getForm($this->group);
  }
  public function executeEdit(sfWebRequest $request)
  {
    $this->group = Doctrine::getTable('Group')->createQuery('g')
      ->addSelect('g.*, u.*')
      ->andWhere('g.id = ?', $request->getParameter('id'))
      ->fetchOne();
    $this->form = $this->configuration->getForm($this->group);
    
    if ( !$this->getUser()->hasCredential(array('pr-group-common', 'admin-users', 'admin-power'), false)
      && in_array($this->getUser()->id, $this->form->getObject()->Users->toKeyValueArray('id', 'id')) )
      $this->form->removeUsersList();
    
    /**
      * if the user cannot modify anything
      * if the user cannot modify common groups and this group is common
      * if the group is not his own
      *
      **/
    if ( !$this->getUser()->hasCredential('pr-group-perso') && !$this->getUser()->hasCredential('pr-group-common')
      || is_null($this->group->sf_guard_user_id) && !$this->getUser()->hasCredential('pr-group-common')
      || $this->group->sf_guard_user_id !== $this->getUser()->getId() && !is_null($this->group->sf_guard_user_id) )
    $this->setTemplate('show');
  }
  public function executeUpdate(sfWebRequest $request)
  {
    $this->group = $this->getRoute()->getObject();
    $this->form = $this->configuration->getForm($this->group);
    if ( !$this->getUser()->hasCredential(array('admin-users', 'admin-power'), false) )
      $this->form->removeUsersList();
    
    /**
      * if the user cannot modify anything
      * if the user cannot modify common groups and this group is common
      * if the group is not his own
      *
      **/
    if ( !$this->getUser()->hasCredential('pr-group-perso') && !$this->getUser()->hasCredential('pr-group-common')
      || is_null($this->group->sf_guard_user_id) && !$this->getUser()->hasCredential('pr-group-common')
      || $this->group->sf_guard_user_id !== $this->getUser()->getId() && !is_null($this->group->sf_guard_user_id) )
    $this->redirect('group/index');
    
    $this->processForm($request, $this->form);
    $this->setTemplate('edit');
    
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
      'groups_list'           => array($request->getParameter('id')),
      'organism_id'           => NULL,
      'organism_category_id'  => NULL,
      'professional_type_id'  => NULL,
    );
    $this->getUser()->setAttribute('contact.filters', $criterias, 'admin_module');
    $this->getUser()->setAttribute('organism.filters', $criterias, 'admin_module');
    
    $this->redirect('contact/index');
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

