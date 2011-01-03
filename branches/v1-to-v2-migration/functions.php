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
*
***********************************************************************************/
?>
<?php
  function migrate($from_table,$conversion,$to_table,$strict = true)
  {
    global $config, $bd, $bd2;
    
    if ( $strict )
      $bd->beginTransaction();
    
    $query = ' SELECT * FROM "'.pg_escape_string($from_table).'"';
    $request = new bdRequest($bd2,$query);
    
    $cpt = array();
    while ( $rec = $request->getRecordNext() )
    {
      $arr = array();
      foreach ( $conversion as $new => $old )
      {
        if ( $old != NULL )
          $arr[$new] = $rec[$old];
        else
        switch ( $new ) {
          case 'created_at':
          case 'updated_at':
            $arr[$new] = date('Y-m-d H:i:s');
            break;
          case 'slug':
            $count = 1;
            for ( $i = 0 ; $count > 0 ; $i++ )
            {
              $sluggable = ($arr['firstname'] ? $arr['firstname'].' ' : '').$arr['name'];
              if ( $i > 0 ) $sluggable .= ' '.$i;
              $sluggable = slugify($sluggable);
              $arr[$new] = $sluggable;
              $r = new bdRequest($bd,"SELECT count(*) AS nb FROM $to_table WHERE slug = '".pg_escape_string($sluggable)."'");
              $count = $r->getRecord('nb');
              $r->free();
            }
            break;
          default:
            $arr[$new] = '\DEFAULT';
            break;
        }
      }
      if ( @$bd->addRecord($to_table,$arr) !== false )
        $cpt['ok']++;
      else
      {
        if ( $strict )
        {
          echo $bd->lastError();
          echo $bd->getLastRequest();
          return false;
        }
        $cpt['ko']++;
      }
    }
    
    $request->free();
    
    if ( isset($arr['id']) && !is_null($arr['id']) )
    {
      $request = new bdRequest($bd,'SELECT max(id) AS last FROM '.$to_table);
      $bd->setLastSerial($to_table,'id',$request->getRecord('last'));
    }
    
    if ( $strict )
      $bd->endTransaction();
    
    return $cpt;
  }
  
  
/**
 * Modifies a string to remove all non ASCII characters and spaces.
 */
function slugify($text)
{
    // replace non letter or digits by -
    $text = preg_replace('~[^\\pL\d]+~u', '-', $text);

    // trim
    $text = trim($text, '-');

    // transliterate
    if (function_exists('iconv'))
    {
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    }

    // lowercase
    $text = strtolower($text);

    // remove unwanted characters
    $text = preg_replace('~[^-\w]+~', '', $text);

    if (empty($text))
        return 'n-a';

    return $text;
}
?>
