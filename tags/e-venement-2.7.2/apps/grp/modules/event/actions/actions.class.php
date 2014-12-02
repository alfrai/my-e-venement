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
*    Copyright (c) 2006-2012 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2012 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php

require_once dirname(__FILE__).'/../lib/eventGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/eventGeneratorHelper.class.php';

/**
 * event actions.
 *
 * @package    e-venement
 * @subpackage event
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class eventActions extends autoEventActions
{
  public function executeFromDateToDate(sfWebRequest $request)
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers(array('I18N'));
    
    $this->form = new sfForm;
    $ws = $this->form->getWidgetSchema();
    $vs = $this->form->getValidatorSchema();
    $ws->setNameFormat('extract[%s]');
    $ws['dates'] = new sfWidgetFormDateRange(array(
      'from_date' => new liWidgetFormJQueryDateText(array('culture' => sfContext::getInstance()->getUser()->getCulture())),
      'to_date'   => new liWidgetFormJQueryDateText(array('culture' => sfContext::getInstance()->getUser()->getCulture())),
      'template'  => '<span class="from">'.__('From %from_date%').'</span> <span class="to">'.__('to %to_date%').'</span>',
    ));
    $vs['dates'] = new sfValidatorDateRange(array(
      'from_date'     => new sfValidatorDate(array('date_output' => 'Y-m-d','with_time' => false,)),
      'to_date'       => new sfValidatorDate(array('date_output' => 'Y-m-d', 'with_time'   => false,)),
    ));
    
    if ( !$request->hasParameter('extract') )
      return 'Success';
    $this->form->bind($request->getParameter('extract'));
    if ( !$this->form->isValid() )
      return 'Success';
    
    $request->setParameter('dates', $this->form->getValue('dates'));
    $this->forward('event', 'accepted');
  }
  public function executeExport(sfWebRequest $request)
  {
    require(dirname(__FILE__).'/export.php');
  }
  public function executeRefused(sfWebRequest $request)
  {
    $request->setParameter('type','refused');
    $this->executeCsv($request);
  }
  public function executeAccepted(sfWebRequest $request)
  {
    $request->setParameter('type','accepted');
    return $this->executeCsv($request);
  }
  public function executeCsv(sfWebRequest $request)
  {
    require(dirname(__FILE__).'/csv.php');
  }
  public function executeGauge(sfWebRequest $request)
  {
    require(dirname(__FILE__).'/gauge.php');
  }
  
  public function executeIndex(sfWebRequest $request)
  {
    parent::executeIndex($request);
    if ( !$this->sort[0] )
    {
      $this->sort = array('name','');
      $q = $this->pager->getQuery();
      $a = $q->getRootAlias();
      $q->andWhereIn("$a.meta_event_id",array_keys($this->getUser()->getMetaEventsCredentials()))
        ->orderby('name');
    }
  }
  
  public function executeEdit(sfWebRequest $request)
  {
    parent::executeEdit($request);
    
    $q = new Doctrine_Query();
    $this->entry = $q->from('Entry e')
      ->leftJoin('e.ContactEntries ce')
      ->leftJoin('ce.Transaction t')
      ->leftJoin('t.Translinked t2')
      ->leftJoin('ce.Professional p')
      ->leftJoin('p.Contact c')
      ->leftJoin('p.Organism o')
      ->leftJoin('e.ManifestationEntries me')
      ->leftJoin('me.Manifestation m')
      ->andWhere('e.event_id = ?',$request->getParameter('id'))
      ->orderBy("ce.comment1 IS NULL OR TRIM(ce.comment1) = '', ce.comment1, c.name, c.firstname, m.happens_at ASC")
      ->fetchOne();
    
    if ( !$this->entry )
    {
      $this->entry = new Entry;
      $this->entry->event_id = $request->getParameter('id');
      $this->entry->save();
    }
  }
}
