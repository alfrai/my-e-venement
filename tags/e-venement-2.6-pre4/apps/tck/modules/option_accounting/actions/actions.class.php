<?php

/**
 * option_labels actions.
 *
 * @package    e-venement
 * @subpackage option_labels
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class option_AccountingActions extends sfActions
{
  public function executeIndex(sfWebRequest $request)
  {
    $this->form = new OptionAccountingForm();
    $this->options = $this->form->getDBOptions();
    $this->form->setDefaults($this->options);
  }
  
  public function executeUpdate(sfWebRequest $request)
  {
    $this->getContext()->getConfiguration()->loadHelpers('I18N');
    $this->form = new OptionAccountingForm();
    $this->form->bind($request->getPostParameters());
    
    if ( !$this->form->isValid() )
    {
      $this->getUser()->setFlash('error',__('Your form cannot be validated.'));
      return $this->setTemplate('index');
    }
    
    $user_id = NULL;
    
    $cpt = $this->form->save($user_id);
    $this->getUser()->setFlash('notice',__('Your configuration has been updated with %i% option(s).',$arr = array('%i%' => $cpt)));
    $this->redirect('option_accounting/index');
  }
}
