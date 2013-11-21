<?php

/**
 * byGroup actions.
 *
 * @package    e-venement
 * @subpackage byGroup
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class byGroupActions extends sfActions
{
 /**
  * Executes index action
  *
  * @param sfRequest $request A request object
  */
  public function executeIndex(sfWebRequest $request)
  {
    /*
    if ( $request->hasParameter('criterias') )
    {
      $this->criterias = $request->getParameter('criterias');
      $this->getUser()->setAttribute('stats.criterias',$this->criterias,'admin_module');
      $this->redirect($this->getContext()->getModuleName().'/index');
    }
    
    $this->form = new StatsCriteriasForm();
    if ( is_array($this->getUser()->getAttribute('stats.criterias',array(),'admin_module')) )
      $this->form->bind($this->getUser()->getAttribute('stats.criterias',array(),'admin_module'));
    */
  }
  
  public function executeCsv(sfWebRequest $request)
  {
    $groups = $this->getGroups('array');
    $q = Doctrine::getTable('Manifestation')->createQuery('m')
      ->andWhere('m.happens_at <= now()')
      ->limit('3')
      ->orderBy('m.happens_at DESC, e.name, l.name');
    $this->manifs = $manifs = $q->execute();
    
    $this->lines = array();
    foreach ( $groups as $group )
    {
      $this->lines[$group['id']]['name'] = $group['name'];
      foreach ( $manifs as $manif )
      if ( !isset($this->lines[$group['id']]['manif-'.$manif->id]) )
        $this->lines[$group['id']]['manif-'.$manif->id] = 0;
      
      $this->lines[$group['id']]['manif-'.$group['manifestation_id']] = $group['nb_entries'];
    }
    
    $params = OptionCsvForm::getDBOptions();
    $this->options = array(
      'ms' => in_array('microsoft',$params['option']),
      'fields' => array('name'),
      'tunnel' => false,
      'noheader' => false,
    );
    foreach ( $manifs as $manif )
      $this->options['fields'][] = 'manif-'.$manif->id;
    
    $this->outstream = 'php://output';
    $this->delimiter = $this->options['ms'] ? ';' : ',';
    $this->enclosure = '"';
    $this->charset   = sfConfig::get('software_internals_charset');
    
    sfConfig::set('sf_escaping_strategy', false);
    $confcsv = sfConfig::get('software_internals_csv'); if ( isset($confcsv['set_charset']) && $confcsv['set_charset'] ) sfConfig::set('sf_charset', $this->options['ms'] ? $this->charset['ms'] : $this->charset['db']);
    
    if ( $request->hasParameter('debug') )
    {
      $this->setLayout(true);
      $this->getResponse()->sendHttpHeaders();
    }
    else
      sfConfig::set('sf_web_debug', false);
  }
  
  public function executeData(sfWebRequest $request)
  {
    $this->manifs = Doctrine::getTable('Manifestation')->createQuery('m')
      ->andWhere('m.happens_at <= now()')
      ->limit('3')
      ->orderBy('m.happens_at DESC, e.name, l.name')
      ->execute();
    $this->groups = $this->getGroups();
    
    if ( !$request->hasParameter('debug') )
    {
      $this->setLayout('raw');
      sfConfig::set('sf_debug',false);
      $this->getResponse()->setContentType('application/json');
    }
  }
  
  protected function getGroups()
  {
    $pdo = Doctrine_Manager::getInstance()->getCurrentConnection()->getDbh();
    $q = ' SELECT g.id, g.name,
                  m.id AS manifestation_id, m.happens_at,
                  e.id AS event_id, e.name AS event_name,
                  l.id AS location_id, l.name AS location_name, l.city AS location_city,
                  count(DISTINCT ctrl.ticket_id) AS nb_entries
           FROM group_table g
           LEFT JOIN group_contact gc ON gc.group_id = g.id
           LEFT JOIN contact c ON c.id = gc.contact_id
           LEFT JOIN transaction t ON t.contact_id = c.id AND t.professional_id IS NULL
           LEFT JOIN ticket tck ON tck.transaction_id = t.id
           LEFT JOIN (SELECT mm.* FROM manifestation mm LEFT JOIN event ee ON ee.id = mm.event_id WHERE mm.happens_at > now() AND ee.meta_event_id IN ('.implode(',',array_keys($this->getUser()->getMetaEventsCredentials())).') LIMIT 1) AS m ON m.id = tck.manifestation_id
           LEFT JOIN event e ON e.id = m.event_id
           LEFT JOIN location l ON l.id = m.location_id
           LEFT JOIN control ctrl ON ctrl.ticket_id = tck.id
           WHERE m.id IS NOT NULL
             AND (t.workspace_id IS NULL OR t.workspace_id IN ('.implode(',',array_keys($this->getUser()->getWorkspacesCredentials())).'))
           GROUP BY g.id, g.name, m.id, m.happens_at, e.id, e.name, l.id, l.name, l.city
           ORDER BY g.name, m.happens_at, e.name, l.name';
    $stmt1 = $pdo->prepare($q);
    $stmt1->execute();
    
    $q = ' SELECT g.id, g.name,
                  m.id AS manifestation_id, m.happens_at,
                  e.id AS event_id, e.name AS event_name,
                  l.id AS location_id, l.name AS location_name, l.city AS location_city,
                  count(DISTINCT ctrl.ticket_id) AS nb_entries
           FROM group_table g
           LEFT JOIN group_contact gc ON gc.group_id = g.id
           LEFT JOIN contact c ON c.id = gc.contact_id
           LEFT JOIN transaction t ON t.contact_id = c.id AND t.professional_id IS NULL
           LEFT JOIN ticket tck ON tck.transaction_id = t.id
           LEFT JOIN (SELECT mm.* FROM manifestation mm LEFT JOIN event ee ON ee.id = mm.event_id WHERE mm.happens_at > now() AND ee.meta_event_id IN ('.implode(',',array_keys($this->getUser()->getMetaEventsCredentials())).') LIMIT 1) AS m ON m.id = tck.manifestation_id
           LEFT JOIN event e ON e.id = m.event_id
           LEFT JOIN location l ON l.id = m.location_id
           LEFT JOIN control ctrl ON ctrl.ticket_id = tck.id
           WHERE m.id IS NOT NULL
             AND (t.workspace_id IS NULL OR t.workspace_id IN ('.implode(',',array_keys($this->getUser()->getWorkspacesCredentials())).'))
           GROUP BY g.id, g.name, m.id, m.happens_at, e.id, e.name, l.id, l.name, l.city
           ORDER BY g.name, m.happens_at, e.name, l.name';
    $stmt2 = $pdo->prepare($q);
    $stmt2->execute();
    
    return array_merge($stmt1->fetchAll(),$stmt2->fetchAll());
  }
}
