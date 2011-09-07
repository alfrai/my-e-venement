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
*    Copyright (c) 2006-2011 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2011 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 * (c) Baptiste SIMON <baptiste.simon AT e-glop.net>
 * (c) Libre Informatique [http://www.libre-informatique.fr/]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWidgetFormDateTime represents a datetime widget.
 *
 * @package    symfony
 * @subpackage widget
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id: sfWidgetFormDateTime.class.php 30762 2010-08-25 12:33:33Z fabien $
 */
class liWidgetFormDateTime extends sfWidgetFormDateTime
{
  /**
   * Configures the current widget.
   *
   * The attributes are passed to both the date and the time widget.
   *
   * If you want to pass HTML attributes to one of the two widget, pass an
   * attributes option to the date or time option (see below).
   *
   * Available options:
   *
   *  * date:      Options for the date widget (see sfWidgetFormDate)
   *  * time:      Options for the time widget (see sfWidgetFormTime)
   *  * with_time: Whether to include time (true by default)
   *  * format:    The format string for the date and the time widget (default to %date% %time%)
   *
   * @param array $options     An array of options
   * @param array $attributes  An array of default HTML attributes
   *
   * @see sfWidgetForm
   */
  protected function configure($options = array(), $attributes = array())
  {
    if ( !$options['date'] instanceof sfWidgetFormDate && !is_array($options['date']) )
      unset($options['date']);
    if ( !$options['time'] instanceof sfWidgetFormtime && !is_array($options['time']) )
      unset($options['time']);
    
    parent::configure();
  }

  /**
   * Returns the date widget.
   *
   * @param  array $attributes  An array of attributes
   *
   * @return sfWidgetForm A Widget representing the date
   */
  protected function getDateWidget($attributes = array())
  {
    if ( $this->getOption('date') instanceof sfWidgetFormJQueryDate
      || $this->getOption('date') instanceof sfWidgetFormDate )
      return $this->getOption('date');
    return new sfWidgetFormDate($this->getOptionsFor('date'), $this->getAttributesFor('date', $attributes));
  }
  
  /**
   * Returns the time widget.
   *
   * @param  array $attributes  An array of attributes
   *
   * @return sfWidgetForm A Widget representing the time
   */
  protected function getTimeWidget($attributes = array())
  {
    if ( $this->getOption('time') instanceof sfWidgetFormTime )
      return $this->getOption('time');
    return new sfWidgetFormTime($this->getOptionsFor('time'), $this->getAttributesFor('time', $attributes));
  }
}
