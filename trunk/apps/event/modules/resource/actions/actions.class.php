<?php

require_once dirname(__FILE__).'/../lib/resourceGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/resourceGeneratorHelper.class.php';

/**
 * location actions.
 *
 * @package    e-venement
 * @subpackage resource
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class resourceActions extends autoResourceActions
{
  public function executeCalendar(sfWebRequest $request)
  {
    $this->executeEdit($request);
  }

  public function executeNewManif(sfWebRequest $request)
  {
    // preconditions
    $this->executeEdit($request);
    if ( !$request->getParameter('event_name',false) )
      throw new liEvenementException('Bad request.');
    
    $event = new Event;
    $event->name = $request->getParameter('event_name');
    $me = array_keys($this->getUser()->getMetaEventsCredentials());
    $event->meta_event_id = $me[0];
    $event->save();
    
    $this->redirect('manifestation/new?event='.$event->slug.'&booking_list[]='.$this->location->id);
  }
}
