<?php

/**
 * option_csv actions.
 *
 * @package    e-venement
 * @subpackage option_csv
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class option_csvActions extends sfActions
{
 /**
  * Executes index action
  *
  * @param sfRequest $request A request object
  */
  public function executeIndex(sfWebRequest $request)
  {
    $this->form = new OptionCsvForm();
    $this->options = $this->form->getDBOptions();
    
    $opts = array();
    foreach ( $this->options as $name => $values )
    foreach ( $values as $value )
      $opts[$value] = true;
    $this->options = $opts;
    
    $this->form->setDefaults($this->options);
  }
  
  public function executeUpdate(sfWebRequest $request)
  {
    $this->getContext()->getConfiguration()->loadHelpers('I18N');
    $this->form = new OptionCsvForm();
    $this->form->bind($request->getPostParameters());
    $this->setTemplate('index');
    
    if ( !$this->form->isValid() )
    {
      $this->getUser()->setFlash('error',__('Your form cannot be validated.'));
      return;
    }
    
    $user_id = $this->getUser() instanceof sfGuardSecurityUser
      ? $this->getUser()->getId()
      : NULL;
    
    $params = array_keys($request->getPostParameters());
    unset($params['_csrf_token']);
    
    $adds = array();
    
    $cpt = 0;
    $widgets = $this->form->widgets;
    unset($widgets['out']);
    foreach ( $widgets as $fieldset => $names )
    foreach ( $names as $value => $label )
    if ( in_array($value,$params) )
      $adds[$fieldset != 'extra' || $value == 'information' ? 'field' : 'option'][] = $value;
    
    $cpt = $this->form->save($user_id,$adds);
    
    $this->getUser()->setFlash('notice',__('Your configuration has been updated with %i% option(s).',array('%i%' => $cpt)));
    $this->redirect('option_csv/index');
  }
}
