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
 * liWidgetFormDoctrineJQueryAutocompleter represents an autocompleter input widget rendered by JQuery
 * which is resetted to void if the input string is ""
 *
 * This implementation is based on sfWidgetFormDoctrineJQueryAutocompleter
 *
 * @package    symfony
 * @subpackage widget
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfWidgetFormPropelJQueryAutocompleter.class.php 12130 2008-10-10 14:51:07Z fabien $
 */
class liWidgetFormDoctrineJQueryAutocompleter extends sfWidgetFormDoctrineJQueryAutocompleter
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
    return parent::render($name, $value, $attributes, $errors).
           sprintf(<<<EOF
<a href="%s" style="display: none;" id="%s"></a>
<script type="text/javascript"><!--
  jQuery(document).ready(function() {
    jQuery('#%s').change(function(){
      if ( jQuery(this).val() === '' )
      {
        jQuery('#%s').val('').change();
      }
    });
  });
--></script>
EOF
      ,
      $this->getOption('url'),
      $this->generateId('url_'.$name),
      $this->generateId('autocomplete_'.$name),
      $this->generateId($name)
    );
  }
  
  public function getVisibleValue($value)
  {
    return $this->toString($value);
  }
  
  public function getJavaScripts()
  {
    return parent::getJavaScripts() + array(
      '/sfAdminThemejRollerPlugin/js/jquery.min.js',
      '/sfFormExtraPlugin/js/jquery.autocompleter.js',
    );
  }
  public function getStylesheets()
  {
    return parent::getStylesheets() + array(
      '/sfFormExtraPlugin/css/jquery.autocompleter.css' => 'all',
      '/sfAdminThemejRollerPlugin/css/jquery/redmond/jquery-ui.custom.css' => 'all',
      '/sfAdminThemejRollerPlugin/css/jroller.css'      => 'all',
      '/sfAdminThemejRollerPlugin/css/fg.buttons.css'   => 'all',
    );
    // CAREFUL: the order is important sometimes
  }
}
