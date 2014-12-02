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
*    Copyright (c) Fabien Potencier <fabien.potencier@symfony-project.com>
*    Copyright (c) 2006-2011 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2011 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php

/**
 * sfWidgetFormTime represents a time widget.
 *
 * @package    symfony
 * @subpackage widget
 * @author     Baptiste SIMON
 * @version    SVN: $Id: sfWidgetFormTime.class.php 30762 2010-08-25 12:33:33Z fabien $
 */
class liWidgetFormTimeText extends sfWidgetFormTime
{
  /*
   * Renders the widget.
   *
   * @param  string $name        The element name
   * @param  string $value       The time displayed in this widget
   * @param  array  $attributes  An array of HTML attributes to be merged with the default HTML attributes
   * @param  array  $errors      An array of errors for the field
   *
   * @return string An HTML tag string
   *
   * @see sfWidgetForm
   */
  public function render($name, $value = null, $attributes = array(), $errors = array())
  {
    // convert value to an array
    $default = array('hour' => null, 'minute' => null, 'second' => null);
    if (is_array($value))
    {
      $value = array_merge($default, $value);
    }
    else
    {
      $value = ctype_digit($value) ? (integer) $value : strtotime($value);
      if (false === $value)
      {
        $value = $default;
      }
      else
      {
        // int cast required to get rid of leading zeros
        $value = array('hour' => (int) date('H', $value), 'minute' => (int) date('i', $value), 'second' => (int) date('s', $value));
      }
    }

    $time = array();
    $emptyValues = $this->getOption('empty_values');

    // hours
    $widget = new sfWidgetFormInput(array('id_format' => $this->getOption('id_format')), array_merge($this->attributes, $attributes, array('maxlength' => 2, 'size' => 2)));
    $time['%hour%'] = $widget->render($name.'[hour]', $value['hour']);

    // minutes
    $widget = new sfWidgetFormInput(array('id_format' => $this->getOption('id_format')), array_merge($this->attributes, $attributes, array('maxlength' => 2, 'size' => 2)));
    $time['%minute%'] = $widget->render($name.'[minute]', $value['minute']);

    if ($this->getOption('with_seconds'))
    {
      // seconds
      $widget = new sfWidgetFormInput(array('id_format' => $this->getOption('id_format')), array_merge($this->attributes, $attributes, array('maxlength' => 2, 'size' => 2)));
      $time['%second%'] = $widget->render($name.'[second]', $value['second']);
    }

    return strtr($this->getOption('with_seconds') ? $this->getOption('format') : $this->getOption('format_without_seconds'), $time);
  }
}
