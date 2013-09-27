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

  class liCardDavVCard extends vCardLI
  {
    protected $id = NULL, $con = NULL, $etag = NULL;
    public $last_error = NULL;
    
    public function __construct(liCardDavConnection $con, $id = NULL, $data = NULL, array $options = null)
    {
      $this->con = $con;
      $this->id = $id;
      
      if ( $this->id && !$data )
      {
        $this->update();
        return $this;
      }
      
      // initialize this w/ given data
      $this->setData($data, $options);

      // completing the uid after creation if one is given inside data
      if ( $this['uid'] )
        $this->id = $this['uid'];
    }
    
    /**
     * @return liCardDavVCard this
     */
    public function delete()
    {
      return $this->con->rawDelVCard($this->id);
    }
    
    /**
     * function update() tries to update the local liCardDavVCard with distant data depending on the UID
     * 
     * @return liCardDavVCard this
     */
    public function update()
    {
      if ( !$this->id )
        throw new liCardDavException('UID not set: a liCardDavVCard object can only be updated if set.');
      
      try {
        $response = $this->con->rawGetVCard($this->id);
        $this->setData((string)$response);
        $this->etag = $response->getETag();
      }
      catch ( liCardDavResponse404Exception $e )
      { $this->reset(); }
      
      return $this;
    }
    
    protected function setData($data, array $options = NULL)
    {
      return parent::setData(NULL, $data, $options);
    }
    
    /**
     * function save() saves this liCardDavVCard into the CardDAV repository
     * @return string vCard's id in the CardDAV repository
     */
    public function save()
    {
      // updates existing vCard
      if ( $this->id )
      {
        $response = $this->con->rawUpdateVCard((string)$this, $this->id, $this->etag);
      }
      
      // inserts new vCard
      else
      {
        $this['uid'] = $this->id = $this->con->generateUID();
        $response = $this->con->rawInsertVCard((string)$this, $this->id);
      }
      
      return $response;
    }
    
    /**
     * @return string vCard's id in the CardDAV repository
     */
    public function getId()
    {
      return $this->id;
    }
    /**
     * function turnNew() resets the id if object does not exist in the DAV repository
     * @return liCardDavVCard this
     */
    public function turnNew()
    {
      $this->id = NULL;
      return $this;
    }
    
    /**
     * @return string vCard's ETag in the CardDAV repository
     */
    public function getETag()
    {
      return $this->etag;
    }
    /**
     * function setETag()
     * sets the vCard's ETag in the CardDAV repository
     *
     * @param string $etag
     * @return liCardDavVCard object this
     */
    public function setETag($etag)
    {
      $this->etag = $etag;
      return $this;
    }
  }
