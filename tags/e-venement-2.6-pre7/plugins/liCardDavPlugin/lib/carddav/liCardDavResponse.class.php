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

  class liCardDavResponse
  {
    protected $data, $headers, $statusCode, $uid = NULL;
    
    public function __construct(array $response)
    {
      if ( !$response )
        throw new liCardDavResponseException('Empty response...');
      
      $this->data = $response['body'];
      $this->headers = $response['headers'];
      $this->statusCode = $response['statusCode'];
      if ( isset($response['uid']) )
        $this->uid = $response['uid'];
    }
    
    public function getETag()
    {
      return isset($this->headers['etag']) ? $this->headers['etag'] : '';
    }
    
    public function __toString()
    {
      return $this->getData();
    }
    
    public function getData()
    { return $this->data; }
    public function getUid()
    { return $this->uid; }
    public function getHeaders()
    { return $this->headers; }
    public function getHeader($key)
    { return isset($this->headers[$key]) ? $this->headers[$key] : NULL; }
    public function getStatus()
    { return $this->statusCode; }
  }
