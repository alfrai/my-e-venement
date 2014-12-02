<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Send emails stored in a queue.
 *
 * @package    symfony
 * @subpackage task
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id: sfProjectSendEmailsTask.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class SendEmailsTask extends sfProjectSendEmailsTask
{
  protected function configure()
  {
    parent::configure();
    $this->addOptions(array(
      new sfCommandOption('delay', null, sfCommandOption::PARAMETER_OPTIONAL, 'The delay to wait between 2 emails', 15),
    ));
    
    $this->namespace = 'e-venement';
    
    $this->detailedDescription .= '

Or the delay before sending the next email (in seconds):

  [php symfony project:send-emails --delay=15|INFO]';
  }

  protected function execute($arguments = array(), $options = array())
  {
    $spool = $this->getMailer()->getSpool()->setFlushDelay($options['delay']);
    parent::execute($arguments, $options);
  }
}
