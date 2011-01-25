<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWidgetFormJQueryDateText represents a date widget rendered by JQuery UI and inputs[type=text].
 *
 * This widget needs JQuery and JQuery UI to work.
 *
 * @package    symfony
 * @subpackage widget
 * @author     Baptiste SIMON <baptiste.simon@libre-informatique.fr>
 */
class liWidgetFormJQueryDateText extends liWidgetFormDateText
{
  /**
   * Configures the current widget.
   *
   * Available options:
   *
   *  * image:   The image path to represent the widget (false by default)
   *  * config:  A JavaScript array that configures the JQuery date widget
   *  * culture: The user culture
   *  * change:  Authorize the user to select quickly a month and a year in Datepicker's selects
   *  * keycode: The key code used to jump from an input to another when typing the date (65/space by default, if null, all keycodes are accepted)
   *
   * @param array $options     An array of options
   * @param array $attributes  An array of default HTML attributes
   *
   * @see sfWidgetForm
   */
  protected function configure($options = array(), $attributes = array())
  {
    $this->addOption('image', false);
    $this->addOption('config', '{}');
    $this->addOption('culture', '');
    $this->addOption('change', '');
    $this->addOption('keycode', '32');

    parent::configure($options, $attributes);

    if ('en' == $this->getOption('culture'))
    {
      $this->setOption('culture', 'en');
    }
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
    $prefix = $this->generateId($name);

    $image = '';
    if (false !== $this->getOption('image'))
    {
      $image = sprintf(', buttonImage: "%s", buttonImageOnly: true', $this->getOption('image'));
    }

    return parent::render($name, $value, $attributes, $errors).
           $this->renderTag('input', array('type' => 'hidden', 'size' => 10, 'id' => $id = $this->generateId($name).'_jquery_control', 'disabled' => 'disabled')).
           sprintf(<<<EOF
<script type="text/javascript">
  function wfd_%s_read_linked()
  {
    jQuery("#%s").val(jQuery("#%s").val() + "/" + jQuery("#%s").val() + "/" + jQuery("#%s").val());

    return {};
  }

  function wfd_%s_update_linked(date)
  {
    jQuery("#%s").val(date.substring(0, 4));
    jQuery("#%s").val(date.substring(5, 7));
    jQuery("#%s").val(date.substring(8));
  }

  function wfd_%s_check_linked_days()
  {
    var daysInMonth = 32 - new Date(jQuery("#%s").val(), jQuery("#%s").val() - 1, 32).getDate();
    jQuery("#%s option").attr("disabled", "");
    jQuery("#%s option:gt(" + (%s) +")").attr("disabled", "disabled");

    if (jQuery("#%s").val() > daysInMonth)
    {
      jQuery("#%s").val(daysInMonth);
    }
  }

  jQuery(document).ready(function() {
    jQuery("#%s").datepicker(jQuery.extend({}, {
      changeYear: %s,
      changeMonth: %s,
      minDate:    new Date(%s, 1 - 1, 1),
      maxDate:    new Date(%s, 12 - 1, 31),
      beforeShow: wfd_%s_read_linked,
      onSelect:   wfd_%s_update_linked,
      showOn:     "button"
      %s
    }, jQuery.datepicker.regional["%s"], %s, {dateFormat: "yy-mm-dd"}));
  });

  jQuery("#%s, #%s, #%s").change(wfd_%s_check_linked_days);

  jQuery(document).ready(function() {
    jQuery("#%s").parent().children('input[type=text]').each(function(){
      if ( jQuery(this).attr('maxlength') )
      jQuery(this).keyup(function(event){
        if ( (!%s || event.keyCode == %s) && jQuery(this).val().length == jQuery(this).attr('maxlength') )
          jQuery(this).next('[type=text]').eq(0).focus();
      });
    });
  });
</script>
EOF
      ,
      $prefix, $id,
      $this->generateId($name.'[year]'), $this->generateId($name.'[month]'), $this->generateId($name.'[day]'),
      $prefix,
      $this->generateId($name.'[year]'), $this->generateId($name.'[month]'), $this->generateId($name.'[day]'),
      $prefix,
      $this->generateId($name.'[year]'), $this->generateId($name.'[month]'),
      $this->generateId($name.'[day]'), $this->generateId($name.'[day]'),
      ($this->getOption('can_be_empty') ? 'daysInMonth' : 'daysInMonth - 1'),
      $this->generateId($name.'[day]'), $this->generateId($name.'[day]'),
      $id,
      $this->getOption('change') ? 'true' : 'false', $this->getOption('change') ? 'true' : 'false',
      min($this->getOption('years')), max($this->getOption('years')),
      $prefix, $prefix, $image, $this->getOption('culture'), $this->getOption('config'),
      $this->generateId($name.'[day]'), $this->generateId($name.'[month]'), $this->generateId($name.'[year]'),
      $prefix,
      $id,$this->getOption('keycode'),$this->getOption('keycode')
    );
  }
}
