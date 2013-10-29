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
  function migrate($from_table, $conversion, $to_table, $strict = true, $where = '', $from = '*')
  {
    global $config, $bd, $bd2;
    
    if ( $strict )
      $bd->beginTransaction();
    
    $query = ' SELECT '.$from.' FROM '.(substr($from_table,0,1) === '(' ? $from_table : pg_escape_string($from_table)).' ';
    if ( $where )
      $query .= 'WHERE '.$where;
    $request = new bdRequest($bd2,$query);
    
    $cpt = array('ok' => 0, 'ko' => 0);
    while ( $rec = $request->getRecordNext() )
    {
      $callbacks = array();
      $arr = array();
      foreach ( $conversion as $new => $old )
      {
        if ( substr($new,0,1) === '_' )
          $callbacks[substr($new,1)] = $old;
        else if ( is_array($old) )
        {
          // take the first field's content which is "ok"
          foreach ( $old as $subold )
          if ( $rec[$subold] )
          {
            $arr[$new] = $rec[$subold];
            break;
          }
          
          // if nothing was ok, take the name of the first "field" that does not exist in $rec
          if ( !$arr[$new] )
          foreach ( $old as $subold )
          if ( !isset($subold) )
            $arr[$new] = $subold;
        }
        else if ( !is_null($old) )
        {
          if ( substr($old,0,1) === '_' )
          {
            $arr[$new] = call_user_func(substr($old,1),$rec);
          }
          else
            $arr[$new] = $rec[$old];
        }
        else // if ( is_null($old) )
        switch ( $new ) {
          case 'created_at':
          case 'updated_at':
            $arr[$new] = date('Y-m-d H:i:s');
            break;
          case 'slug':
            $count = 1;
            for ( $i = 0 ; $count > 0 ; $i++ )
            {
              $sluggable = (isset($arr['firstname']) && $arr['firstname'] ? $arr['firstname'].' ' : '').$arr['name'];
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
      {
        $cpt['ok']++;
        foreach ( $callbacks as $callback => $value )
          call_user_func($callback,$arr['id'],$rec);
      }
      else
      {
        if ( $strict )
        {
          echo $bd->getLastRequest();
          echo $bd->lastError();
          var_dump($arr);
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
