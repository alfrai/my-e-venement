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
*    Copyright (c) 2006-2014 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2014 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php
class liSpool extends Swift_DoctrineSpool
{
  private $delay = 0;

  /**
    * Sets the delay between email sends (in seconds) during the flush.
    *
    * @param integer $delay The delay
    */
  public function setFlushDelay($delay)
  {
    $this->delay = (int)$delay;
  }
  /**
    * Gets the delay between email sends (in seconds) during the flush.
    *
    * @return integer The delay
    */
  public function getFlushDelay()
  {
    return $this->delay;
  }

  /**
   * Sends messages using the given transport instance.
   *
   * @param Swift_Transport $transport         A transport instance
   * @param string[]        &$failedRecipients An array of failures by-reference
   *
   * @return int The number of sent emails
   */
  public function flushQueue(Swift_Transport $transport, &$failedRecipients = null)
  {
    $table = Doctrine_Core::getTable($this->model);
    $objects = $table->{$this->method}()->limit($this->getMessageLimit())->execute();

    if (!$transport->isStarted())
    {
      $transport->start();
    }

    $count = 0;
    $time = time();
    foreach ($objects as $object)
    {
      $message = unserialize($object->{$this->column});

      $object->delete();

      try
      {
        $count += $transport->send($message, $failedRecipients);
      }
      catch (Exception $e)
      {
        // TODO: What to do with errors?
      }

      if ($this->getTimeLimit() && (time() - $time) >= $this->getTimeLimit())
      {
        break;
      }

      // keep calm, have a break...
      sleep($this->delay);
    }

    return $count;
  }
}
