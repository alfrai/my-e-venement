<?php

/**
 * sfWidgetFormDateText represents a date widget with input[type=text].
 *
 * @package    symfony
 * @subpackage widget
 * @author     Baptiste SIMON <baptiste.simon@libre-informatique.fr>
 */
class liWidgetFormDateText extends sfWidgetFormI18nDate
{
  /**
   * Configures the current widget.
   *
   * Available options:
   *
   *  * format:       The date format string (%month%/%day%/%year% by default)
   *  * years:        An array of years for the year select tag (optional)
   *  * months:       An array of months for the month select tag (optional)
   *  * days:         An array of days for the day select tag (optional)
   *  * can_be_empty: Whether the widget accept an empty value (true by default)
   *  * empty_values: An array of values to use for the empty value (empty string for year, month, and date by default)
   *
   * @param array $options     An array of options
   * @param array $attributes  An array of default HTML attributes
   *
   * @see sfWidgetForm
   */
  protected function configure($options = array(), $attributes = array())
  {
    parent::configure($options,$attributes);
    $this->addOption('culture',sfContext::getInstance()->getUser()->getCulture());
  }
  
  /**
   * @param  string $name        The element name
   * @param  string $value       The date displayed in this widget
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
    $default = array('year' => null, 'month' => null, 'day' => null);
    if (is_array($value))
    {
      $value = array_merge($default, $value);
    }
    else
    {
      $value = (string) $value == (string) (integer) $value ? (integer) $value : strtotime($value);
      if (false === $value)
      {
        $value = $default;
      }
      else
      {
        $value = array('year' => date('Y', $value), 'month' => date('n', $value), 'day' => date('j', $value));
      }
    }

    $date = array();
    $emptyValues = $this->getOption('empty_values');

    // days
    $widget = new sfWidgetFormInput(array(), array_merge($this->attributes, $attributes, array('size' => '2', 'maxlength' => 2)));
    $date['%day%'] = $widget->render($name.'[day]',intval($value['day']) < 10 && intval($value['day']) > 0 ? '0'.intval($value['day']) : $value['day']);

    // months
    $widget = new sfWidgetFormInput(array(), array_merge($this->attributes, $attributes, array('size' => '2', 'maxlength' => 2)));
    $date['%month%'] = $widget->render($name.'[month]',intval($value['month']) < 10 && intval($value['month']) > 0 ? '0'.intval($value['month']) : $value['month']);

    // years
    $widget = new sfWidgetFormInput(array(), array_merge($this->attributes, $attributes, array('class' => 'sfWFDTyear', 'size' => '4', 'maxlength' => 4)));
    $date['%year%'] = $widget->render($name.'[year]', $value['year']);

    return strtr($this->getOption('format'), $date);
  }
}
