<?php

/**
 * Hold form.
 *
 * @package    e-venement
 * @subpackage form
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class HoldForm extends BaseHoldForm
{
  /**
   * @see TraceableForm
   */
  public function configure()
  {
    parent::configure();
    unset($this->widgetSchema['seats_list']);
    
    $this->widgetSchema['color']->setAttribute('type', 'color');
    $this->setDefault('color', '#ffffff');
    
    if ( $this->object->isNew() )
      return;
    
    if ( is_null($this->object->color) )
      $this->object->color = '#ffffff';
    
    $this->widgetSchema['next']->setOption('query', Doctrine::getTable('Hold')->createQuery('h')
      ->andWhere('h.id != ?', $this->object->id)
      ->andWhereNotIn('h.id', $this->object->Feeders->getPrimaryKeys())
    );
  }
}
