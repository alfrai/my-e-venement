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
  //require_once __DIR__.'/../vendor/SabreDAV/vendor/autoload.php';

  /**
   * class liCardDavConnection a proxy layer to a DAVClient object
   */
  abstract class liCardDavConnection
  {
    protected $auth, $url, $options;
    protected $backend; // carddav_backend
    protected $vcard_id_chars = array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 'A', 'B', 'C', 'D', 'E', 'F');
    
    /**
     * Class constructor
     *
     * @param url the url to the CardDAV service
     * @param auth an array containing "username" and "password"
     */
    public function __construct($url, array $auth, array $options = array())
    {
      if ( !isset($auth['userName']) && !isset($auth['username']) )
        throw new liCardDAVException('You must provide a username');
      if ( !isset($auth['password']) )
        throw new liCardDAVException('You must provide a password');
      if ( !isset($url) )
        throw new liCardDAVException('You must provide a url');
      
      if ( !isset($auth['userName']) && isset($auth['username']) )
      {
        $auth['userName'] = $auth['username'];
        unset($auth['username']);
      }
      
      $this->backend = new DAVClient(array_merge(array('baseUri' => $url), $auth));
      $this->backend->setVerifyPeer(isset($options['check_ssl']) ? $options['check_ssl'] : false);
      
      $this->url = $url;
      $this->auth = $auth;
      $this->options = $options;
      
      if ( sfContext::hasInstance() )
        sfContext::getInstance()->getEventDispatcher()->notify(new sfEvent($this, 'carddav.connect', array('url' => $this->url, 'auth' => $this->auth, 'options' => $this->options)));
    }
    
    /**
     * Check for the connection state
     */
    public function isValid()
    {
      $response = $this->backend->request('GET', $this->url);
      if ( $response['statusCode'] == 200 )
        return true;
      
      throw new liCardDavException('Your connection is not valid: '.print_r($response));
    }
    
    /**
     * function buildVcfUrl
     *
     * @param string $id a unique resouce id
     * @return the complete URL for resource
     **/
    protected function buildVcfUrl($id)
    {
      if ( !$id )
        throw new liCardDavException('You must specify a parameter to build the VCF URL.');
      return $this->url.'/'.$id.'.vcf';
    }
    
    /**
     * @param $id string id to get
     * @return liCardDavResponse
     */
    public function rawGetVCard($id)
    {
      $response = $this->backend->request('GET', $this->buildVcfUrl($id));
      return new liCardDavResponse($response);
    }
    
    /**
     * @param $id string id to delete
     * @return liCardDavResponse
     */
    public function rawDelVCard($id)
    {
      $response = $this->backend->request('DELETE', $this->buildVcfUrl($id));
      return new liCardDavResponse($response);
    }
    
    /**
     * @param $data string data
     * @param $id string id to update
     * @return liCardDavResponse
     */
    public function rawUpdateVCard($data, $id, $etag = NULL)
    {
      $etag = NULL;
      $headers = array('If-Match'  => $etag ? $etag : '*');
      if ( $etag )
        $headers['ETag'] = $headers['X-Zimbra-ETag'] = $etag;
      $response = $this->backend->request('PUT', $this->buildVcfUrl($id), $data, $headers);
      $response['uid'] = $id;
      return new liCardDavResponse($response);
    }
    
    /**
     * @param string $data vCard to insert
     * @param string $id (optional) id of the future resource
     * @return liCardDavResponse
     */
    public function rawInsertVCard($data, $id = NULL)
    {
      if ( !$id )
        $id = $this->generateUID();
      
      $response = $this->backend->request('PUT', $this->buildVcfUrl($id), $data, array('If-None-Match' => '*'));
      $response['uid'] = $id;
      return new liCardDavResponse($response);
    }
    
    /**
     * @param string representing the vCard id to download
     * @return liCardDavVCard object based on the downloaded data
     */
    public function getVCard($id)
    {
      return liCardDavVCard::create($this, $id);
    }
    
    /**
     * Generates an arbitrary UID, not validated
     *
     * @return string UID
     */
    protected function generateUIDToValidate()
    {
      $vcard_id = null;
      for ($number = 0; $number <= 25; $number ++)
      {
        if ($number == 8 || $number == 17)
        {
          $vcard_id .= '-';
        }
        else
        {
          $vcard_id .= $this->vcard_id_chars[mt_rand(0, (count($this->vcard_id_chars) - 1))];
        }
      }
      
      return $vcard_id;
    }
    
    /**
     * Generates a validated (unique) UID
     *
     * @return string UID really unique
     */
    public function generateUID()
    {
      $vcard_id = $this->generateUIDToValidate();
      
      try
      {
        try { $this->rawGetVCard($vcard_id); }
        catch ( liCardDavResponse404Exception $e )
        { $vcard_id = $this->generateUIDToValidate(); }
        
        return $vcard_id;
      }
      catch (Exception $e)
      {
        throw new liCardDavException('Generating a new vCard UID was impossible. See details: '.$e->getMessage());
      }
    }
    
    /**
     * function getLastUpdate() returns the datetime (ISO8601) of the last update
     *
     * @return string Date ISO8601 OR FALSE if failed
     **/
    public function getLastUpdate()
    {
      $r = array_values(
        $this->backend->propFind('',array('{DAV:}getlastmodified',))
      );
      return date('c',strtotime($r[0]));
    }
    
    /**
     * function getLastUpdate() returns the datetime (ISO8601) of the last update
     *
     * @return string Date ISO8601 OR FALSE if failed
     **/
    public function resetLastUpdate()
    {
      $r = array_values(
        $this->backend->propPatch('',array('{DAV:}getlastmodified' => date('c'),))
      );

      if ( sfContext::hasInstance() )
        sfContext::getInstance()->getEventDispatcher()->notify(new sfEvent($this, 'carddav.reset_last_update', array('datetime' => date('c',strtotime($r[0])))));

      return date('c',strtotime($r[0]));
    }
    
    public function test()
    {
/*
      $r = $this->backend->request('REPORT', '', <<<EOF
<?xml version="1.0" encoding="utf-8" ?>
<C:addressbook-query xmlns:D="DAV:"
                     xmlns:C="urn:ietf:params:xml:ns:carddav">
     <D:prop>
       <D:getetag/>
       <C:address-data>
         <C:prop name="REV"/>
         <C:prop name="UID"/>
       </C:address-data>
     </D:prop>
     <C:filter>
       <C:prop-filter name="FN">
         <C:text-match collation="i;unicode-casemap"
                       match-type="starts-with"
         >pl</C:text-match>
       </C:prop-filter>
     </C:filter>
   </C:addressbook-query>
EOF
      );
*/
      $r = $this->backend->request('PROPFIND');
      print_r($r);
    }
    
    /**
     * @return array of ids
     */
    public abstract function getIdsList();
    public abstract function fetchVCards();
  }
