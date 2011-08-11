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
/**
 * sfWidgetFormDoctrineJQueryAutocompleterGuide represents an autocompleter input widget rendered by JQuery
 * optimized for propositions of completion
 *
 * This implementation is based on sfWidgetFormDoctrineJQueryAutocompleter.
 *
 * @package    symfony
 * @subpackage widget
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfWidgetFormPropelJQueryAutocompleter.class.php 12130 2008-10-10 14:51:07Z fabien $
 */
class liWidgetFormDoctrineJQueryAutocompleterGuide extends sfWidgetFormDoctrineJQueryAutocompleter
{
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
    $visibleValue = $this->getOption('value_callback') ? call_user_func($this->getOption('value_callback'), $value) 
: $value;

    return $this->renderTag('input', array('type' => 'hidden', 'name' => $name, 'value' => $value)).
           sfWidgetFormInput::render('autocomplete_'.$name, $visibleValue, $attributes, $errors).
           sprintf(<<<EOF
<script type="text/javascript">
  jQuery(document).ready(function() {
    input = '#%s';
    autocomplete = '#%s';
    
    jQuery(autocomplete)
    .autocomplete('%s', jQuery.extend({}, {
      dataType: 'json',
      parse:    function(data) {
        var parsed = [];
        for (key in data) {
          parsed[parsed.length] = { data: [ data[key], data[key] ], value: data[key], result: data[key] };
        }
        return parsed;
      }
    }, %s))
    .result(function(event, data) { jQuery(input).val(data[1]); });
    
    jQuery(input).closest('form').submit(function(){
      if ( jQuery(input).val() != jQuery(autocomplete).val() )
      {
        jQuery(input).val(jQuery(autocomplete).val());
      }
    });
  });
</script>
EOF
      ,
      $this->generateId($name),
      $this->generateId('autocomplete_'.$name),
      $this->getOption('url'),
      $this->getOption('config')
    );
  }

  /**
   * Returns the text representation of a foreign key.
   *
   * @param string $value The primary key
   */
  protected function toString($value)
  {
    return $value;
    
    
    $object = null;
    if ($value != null)
    {
      $class = Doctrine::getTable($this->getOption('model'));
      $method = $this->getOption('method_for_query');

      $object = call_user_func(array($class, $method), $value);
    }

    $method = $this->getOption('method');

    if (!method_exists($this->getOption('model'), $method))
    {
      throw new RuntimeException(sprintf('Class "%s" must implement a "%s" method to be rendered in a "%s" widget', $this->getOption('model'), $method, __CLASS__));
    }
    
    return is_object($object) ? $object->$method() : $value;
  }
}
