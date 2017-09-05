<?php

/**
 * Admin actions.
 *
 * @package    evenement
 * @subpackage kiosk
 * @author     Romain SANCHEZ <romain.sanchez AT libre-informatique.fr>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class adminActions extends autoAdminActions
{
  /**
  * New task from Transaction list
  *
  * @param sfRequest $request A request object
  */
  public function executeNew(sfWebRequest $request)
  {
  	parent::executeNew($request);

  	$this->form->setDefault('transaction_id', $request->getParameter('transaction'));
  }

 /**
  * Json task list
  *
  * @param sfRequest $request A request object
  */
  public function executeGetJsonTasks(sfWebRequest $request)
  {
  	$tasks = Doctrine_Query::create()
      ->from('AdminTask a')
      ->where('a.done = false')
      ->fetchArray();
    ;

	$this->getResponse()->setHttpHeader('Content-type','application/json');

    die(json_encode($this->formatTaskList($tasks)));

    return sfView::NONE;
  }

  /**
  * Flag a task as done
  *
  * @param sfRequest $request A request object
  */
  public function executeDone(sfWebRequest $request)
  {
  	$task = $this->getTask($request->getParameter('task'));
  	$task->done = true;
  	$task->save();

  	die('ok');

  	return sfView::NONE;
  }

  /**
  * Build task array
  *
  * @param array Admintask objects
  */
  protected function formatTaskList($tasks) {
  	$taskList = array();

  	foreach($tasks as $task)
    {	
    	//Check task type
    	if($task['type'] !== 'pin')
    	{
    		$record = $this->getPaymentRecord($task['transaction_id']);

	   	 	if( $record ) 
	   	 	{
	   	 		$task['receipt'] = base64_decode($record->$task['type']);
	   	 	}
    	}

    	$taskList[] = $task;
    }

    return $taskList;
  }

  /**
  * Fetch EptRecord for a Transaction
  *
  * @param String Transaction id
  */
  protected function getPaymentRecord($transactionId)
  {
    return Doctrine_Query::create()
      ->from('EptRecord e')
      ->where('e.transaction_id = ?', $transactionId)	
      ->fetchOne();
    ; 
  }

  /**
  * Fetch AdminTask
  *
  * @param String AdminTask id
  */
  protected function getTask($taskId)
  {
    return Doctrine_Query::create()
      ->from('AdminTask a')
      ->where('a.id = ?', $taskId)	
      ->fetchOne();
    ; 
  }
}
