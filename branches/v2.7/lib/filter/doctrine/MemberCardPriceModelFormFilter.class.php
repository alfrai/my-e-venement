<?php

/**
 * MemberCardPriceModel filter form.
 *
 * @package    symfony
 * @subpackage filter
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormFilterTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class MemberCardPriceModelFormFilter extends BaseMemberCardPriceModelFormFilter
{
  /**
   * @see TraceableFormFilter
   */
  public function configure()
  {
    parent::configure();
    
    if ( !sfContext::hasInstance() )
      return;
    $this->user = sfContext::getInstance()->getUser();
    
    $this->widgetSchema   ['event_id']->setOption('query', Doctrine::getTable('Event')->createQuery('e')
      ->andWhereIn('e.meta_event_id', array_keys($this->user->getMetaEventsCredentials()))
    );
    $this->validatorSchema['event_id']->setOption('query', $this->widgetSchema['event_id']->getOption('query'));
  }
}
