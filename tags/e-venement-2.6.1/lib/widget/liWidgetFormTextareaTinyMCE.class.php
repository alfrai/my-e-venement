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
 * liWidgetFormTextareaTinyMCE represents a rich text input widget rendered by TinyMCE
 *
 * This implementation is based on sfWidgetFormTextareaTinyMCE
 *
 * @package    symfony
 * @subpackage widget
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 */
class liWidgetFormTextareaTinyMCE extends sfWidgetFormTextareaTinyMCE
{
  public function __construct($options = array(), $parameters = array())
  {
    if ( !isset($options['config']) )
      $options['config'] = array();
    if ( !is_array($options['config']) )
      $options['config'] = array($options['config']);
    
    if ( sfContext::hasInstance() )
      $options['config']['language'] = sfContext::getInstance()->getUser()->getCulture();
    if ( !isset($options['config']['paste_as_text']) )
    {
      $options['config']['plugins'] = 'paste';
      $options['config']['paste_as_text'] = true;
    }
    
    $config = array();
    foreach ( $options['config'] as $key => $value )
    {
      if ( is_bool($value) )
        $value = $value ? 'true' : 'false';
      else
        $value = '"'.$value.'"';
      $config[] = $key.': '.$value;
    }
    $options['config'] = implode(",\n",$config);
    
    parent::__construct($options, $parameters);
  }
}
