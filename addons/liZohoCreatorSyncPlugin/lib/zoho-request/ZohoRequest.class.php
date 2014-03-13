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
*    Copyright (c) 2006-2014 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2014 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php
  class ZohoRequest extends \Httpful\Request
  {
    protected $auth, $base_uri, $appname, $scope = 'creatorapi', $type = 'json', $task = NULL;
    
    public function __construct($base_uri, $auth, $appname, sfTask $task = NULL)
    {
      foreach ( array('base_uri', 'auth', 'appname') as $prop )
      {
        if ( !$prop )
          throw new liZohoCreatorException('Configuration missing...');
        $this->$prop = $$prop;
      }
      $this->task = $task;
    }
    
    public function getBaseUri()
    {
      return $this->base_uri.$this->type.'/'.$this->appname;
    }
    
    public function go(Array $request = array())
    {
      if (!( is_array($request) && $module = $request['module'] ))
        throw new liZohoCreatorException('URI missing');
      
      $method = NULL;
      $params = array();
      
      $name = NULL;
      if ( isset($request['name']) )
      {
        $name = $request['name'];
        unset($request['name']);
      }
      $action = NULL;
      if ( isset($request['action']) )
      {
        $action = $request['action'];
        unset($request['action']);
      }
      
      switch ( $module ) {
      default:
        $method = 'get';
        break;
      }
      unset($request['module']);
      
      $request['authtoken'] = $this->auth;
      $request['scope']     = $this->scope;
      $request['raw']       = 'true';
      
      switch ( $method ) {
      case 'get':
        $req = parent::get($url = $this->getBaseUri().'/'.$module.'/'.($name ? $name.'/' : '').($action ? $action.'/' : '').http_build_query($request));
        break;
      case 'post':
        $req = parent::post($url = $this->getBaseUri().'/'.$module.'/'.($name ? $name.'/' : '').($action ? $action.'/' : ''))
          ->body(http_build_query($request));
        break;
      }
      
      // debug
      if ( sfConfig::get('sf_debug') && $this->task )
        $this->task->logSection('URI', $req->uri);
      
      // return
      return $req;
    }
  }
