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
*    Copyright (c) 2006-2013 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2013 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php

require_once dirname(__FILE__).'/../lib/professionalGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/professionalGeneratorHelper.class.php';

/**
 * professional actions.
 *
 * @package    e-venement
 * @subpackage professional
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class professionalActions extends autoProfessionalActions
{
  public function executeShow(sfWebRequest $request)
  {
    $table = Doctrine::getTable('Professional');
    $this->professional = $table->doSelectOnlyGrp($table->createQuery('p'))
      ->andWhere('p.id = ?', $request->getParameter('id'))
      ->fetchOne();
    $this->forward404Unless($this->professional);
    $this->form = $this->configuration->getForm($this->professional);
  }
  
  public function executeNew(sfWebRequest $request)
  { $this->redirect('professional_full/new'); }
  public function executeEdit(sfWebRequest $request)
  { $this->redirect('professional_full/edit?id='.$request->getParameter('id')); }
  public function executeUpdate(sfWebRequest $request)
  { throw new liEvenementException('This action is not implemented.'); }
  public function executeCreate(sfWebRequest $request)
  { throw new liEvenementException('This action is not implemented.'); }
  public function executeDelete(sfWebRequest $request)
  { throw new liEvenementException('This action is not implemented.'); }
  public function executeBatchDelete(sfWebRequest $request)
  { throw new liEvenementException('This action is not implemented.'); }
  
  public function getPager()
  {
    // a trick to avoid errors in sfDoctrinePager::getNbResults()
    $pager = parent::getPager();
    $q = $pager->getQuery();
    $a = $q->getRootAlias();
    $q->select("$a.*, c.*, o.*, g.id, g.name, g.display_everywhere, g.sf_guard_user_id, pic.id, pic.name, pic.content")
      ->addSelect('o.city AS organism_city')
      ->addSelect('count(DISTINCT eem.event_id) AS nb_events, count(DISTINCT eem.id) AS nb_manifestations')
      ->groupBy("$a.id, c.id, c.name, c.firstname, o.id, o.name, o.city, t.name, g.id, g.name, u.id, pic.id, pic.name, pic.content, g.display_everywhere, g.sf_guard_user_id")
    ;
    return $pager;
  }
  
  public function executeExtract(sfWebRequest $request)
  {
    $pager = $this->getPager();
    $q = $pager->getQuery()
      ->removeDqlQueryPart('offset')
      ->removeDqlQueryPart('limit');
    $a = $q->getRootAlias();
    $q->select("$a.id AS professional_id, o.name AS organism_name, o.city AS organism_city, $a.name AS function, c.name||' '||c.firstname AS name, $a.contact_email")
      ->addSelect('o.administrative_number')
      ->addSelect('count(DISTINCT eem.event_id) as nb_events, count(DISTINCT eem.id) as nb_manifestations');
    $this->lines = $q->fetchArray();
    for ( $i = 0 ; $i < count($this->lines) ; $i++ )
    {
      unset($this->lines[$i]['Contact']);
      unset($this->lines[$i]['Organism']);
      
      $this->lines[$i]['nb_tickets'] = 'N/A';
      if ( count($this->lines) < 5000 )
      {
        $q = Doctrine_Query::create()->from('EntryTickets et')
          ->select('et.*, ee.id, me.id')
          ->leftJoin('et.EntryElement ee')
          ->leftJoin('ee.ContactEntry ce')
          ->leftJoin('ee.ManifestationEntry me')
          ->leftJoin('me.Manifestation m')
          ->leftJoin('m.Event e')
          ->andWhereIn('e.meta_event_id',array_keys($this->getUser()->getMetaEventsCredentials()))
          ->andWhere('ce.professional_id = ?',$this->lines[$i]['professional_id'])
          ->andWhere('ee.accepted = ?',true) 
        ;
        
        $nb = 0;
        $mids = array();
        foreach ( $q->execute() as $tickets )
        {
          $mids[$tickets->EntryElement->ManifestationEntry->id] = 1;
          $nb += $tickets->quantity;
        }
      
        $this->lines[$i]['nb_tickets'] = round($nb/array_sum($mids));
      }
    }
    
    $params = OptionCsvForm::getDBOptions();
    $this->options = array(
      'ms' => in_array('microsoft',$params['option']),
      'tunnel' => false,
      'noheader' => false,
      'fields'   => array(
        'organism_name',
        'organism_city',
        'name',
        'function',
        'contact_email',
        'administrative_number',
        'nb_events',
        'nb_manifestations',
        'nb_tickets',
      ),
    );
    
    $this->outstream = 'php://output';
    $this->delimiter = $this->options['ms'] ? ';' : ',';
    $this->enclosure = '"';
    $this->charset   = sfConfig::get('software_internals_charset');
    
    sfConfig::set('sf_escaping_strategy', false);
    sfConfig::set('sf_charset', $this->options['ms'] ? $this->charset['ms'] : $this->charset['db']);
    
    if ( $request->hasParameter('debug') )
    {
      $this->getResponse()->sendHttpHeaders();
      $this->setLayout('layout');
    }
    else
      sfConfig::set('sf_web_debug', false);
  }

  public function executeSearch(sfWebRequest $request)
  {
    parent::executeIndex($request);
    
    $q = array('Contact' => NULL, 'Organism' => NULL);
    $cpt = 0;
    foreach ( $q as $tname => $query )
    {
      $cpt++;
      $table = Doctrine::getTable($tname);
      $search = $this->sanitizeSearch($s = $request->getParameter('s'));
      $transliterate = sfConfig::get('software_internals_transliterate',array());
      $q[$tname] = $table->search($search.'*',Doctrine_Query::create()->from($tname.' tt'.$cpt));
      $q[$tname]->select("tt$cpt.id");
    }
    
    $a = $this->pager->getQuery()->getRootAlias();
    $this->pager->getQuery()->andWhere("$a.contact_id IN (".$q['Contact'].") OR $a.organism_id IN (".$q['Organism'].")",array($s,$s));
    $this->pager->setPage($request->getParameter('page') ? $request->getParameter('page') : 1);
    $this->pager->init();
    
    $this->setTemplate('index');
  }
  public static function sanitizeSearch($search)
  {
    $nb = strlen($search);
    $charset = sfConfig::get('software_internals_charset');
    return str_replace(array('-','+',','),' ',strtolower(iconv($charset['db'],$charset['ascii'],substr($search,$nb-1,$nb) == '*' ? substr($search,0,$nb-1) : $search)));
  }
}
