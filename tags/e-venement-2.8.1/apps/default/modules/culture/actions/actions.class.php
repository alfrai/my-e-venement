<?php

/**
 * culture actions.
 *
 * @package    e-venement
 * @subpackage culture
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class cultureActions extends sfActions
{
  protected $basename = 'lang';
  
 /**
  * Executes index action
  *
  * @param sfRequest $request A request object
  */
  public function executeIndex(sfWebRequest $request)
  {
    $this->form = $this->getForm();
  }
  
  public function executeChange(sfWebRequest $request)
  {
    $this->form = $this->getForm();
    $this->form->bind($params = $request->getParameter($this->basename));
    if ( $this->form->isValid() )
    {
      $this->getUser()->setCulture($params['culture']);
      $this->getUser()->setFlash('success', 'Your language as been successfully updated.');
      $this->getResponse()->setCookie('lang', $this->getUser()->getCulture(), '3 month');
    }
    else
      $this->getUser()->setFlash('error', 'Please, try again...');
    
    $this->redirect('culture/index');
  }
  
  protected function getForm()
  {
    $cultures = sfConfig::get('project_internals_cultures', array('fr' => 'Français'));
    
    $form = new sfForm;
    $ws = $form->getWidgetSchema();
    $vs = $form->getValidatorSchema();
    
    $ws['culture'] = new sfWidgetFormChoice(array(
      'choices' => $cultures,
    ));
    $ws['culture']->setDefault($this->getUser()->getCulture());
    $vs['culture'] = new sfValidatorChoice(array(
      'choices' => array_keys($cultures),
    ));
    
    $ws->setNameFormat($this->basename.'[%s]');
    return $form;
  }
}
