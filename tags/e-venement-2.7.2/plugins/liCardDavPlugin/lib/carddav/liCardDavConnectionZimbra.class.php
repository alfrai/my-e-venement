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
*    Copyright (c) 2006-2013 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2013 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php

  class liCardDavConnectionZimbra extends liCardDavConnection
  {
    protected $vcards = array(), $last_update = NULL;
    const VCF_QUERY_STRING = 'fmt=vcf';
    
    public function getVCard($id, $nocache = false)
    {
      return !$nocache && isset($this->vcards[$id])
        ? $this->vcards[$id]
        : parent::getVCard($id);
    }
    
    public function getIdsList()
    {
      if ( !$this->vcards )
        $this->fetchVCards();
      return array_keys($this->vcards);
    }
    
    public function fetchVCards($nocache = true)
    {
      if ( $nocache || count($this->vcards) == 0
        || is_null($this->last_update) || $this->last_update < $this->getLastUpdate() )
      {
        $url = str_replace(array('://', '/dav/'), array('://'.$this->auth['userName'].':'.$this->auth['password'].'@', '/home/'), $this->url);
        $vcf_list = file_get_contents($url.'?'.self::VCF_QUERY_STRING);
        
        $this->vcards = array();
        foreach ( explode($str = 'END:VCARD',$vcf_list) as $vcard )
        {
          if ( !trim($vcard) )
            continue;
          $vcard .= $str."\n";
          $vcard = liCardDavVCard::create($this, NULL, $vcard);
          $this->vcards[$vcard['uid']] = $vcard;
        }
        
        $this->last_update = $this->getLastUpdate();
      }
      
      return $this->vcards;
    }
  }
