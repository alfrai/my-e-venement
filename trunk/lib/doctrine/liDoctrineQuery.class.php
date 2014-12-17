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
class liDoctrineQuery extends Doctrine_Query
{
  public function getRawSql()
  {
    $query = $this->getSqlQuery();
    foreach ($this->getFlattenedParams() as $param) {
      $query = join(var_export(is_scalar($param) ? $param : (string) $param, true), explode('?', $query, 2));
    }
    return str_replace('\\\\', '\\', $query);
  }
  public function addParams($part, array $params)
  {
    $this->_params = array_merge($this->_params, array($part => $params));
    return $this;
  }

  /**
    * This aims to enable the use of a DB server specialized in reading, and the main one more specialized in writting
    * cf. http://snippets.symfony-project.org/snippet/373
    */
  public function preQuery()
  {
    // If this is a select query then set connection to the slave
    try {
      switch ( $this->getType() ) {
      case Doctrine_Query::SELECT:
        $this->_conn = Doctrine_Manager::getInstance()->getConnection('read');
        break;
      default:
        $this->_conn = Doctrine_Manager::getInstance()->getConnection('write');
        break;
      }
    } catch ( Doctrine_Manager_Exception $e ) {
      if ( sfConfig::get('sf_debug', false) )
        error_log($e->getMessage().'. Check your config/database.yml.');
      $this->_conn = Doctrine_Manager::getInstance()->getCurrentConnection();
    }
    
    return parent::preQuery();
  }
}
