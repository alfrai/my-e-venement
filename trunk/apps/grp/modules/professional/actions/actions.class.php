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

require_once dirname(__FILE__).'/../lib/professionalGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/professionalGeneratorHelper.class.php';

/**
 * professional actions.
 *
 * @package    e-venement
 * @subpackage professional
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class professionalActions extends autoProfessionalActions
{
  public function executeShow(sfWebRequest $request)
  {
    $table = Doctrine::getTable('Professional');
    $this->professional = $table->doSelectOnlyGrp($table->createQuery('p'))
      ->andWhere('p.id = ?', $request->getParameter('id'))
      ->fetchOne();
    $this->forward404Unless($this->professional);
    $this->form = $this->configuration->getForm($this->professional);
  }
  
  public function executeEdit(sfWebRequest $request)
  { $this->redirect('professional_full/edit?id='.$request->getParameter('id')); }
  public function executeUpdate(sfWebRequest $request)
  { throw new liEvenementException('This action is not implemented.'); }
  public function executeNew(sfWebRequest $request)
  { throw new liEvenementException('This action is not implemented.'); }
  public function executeCreate(sfWebRequest $request)
  { throw new liEvenementException('This action is not implemented.'); }
  public function executeDelete(sfWebRequest $request)
  { throw new liEvenementException('This action is not implemented.'); }
  public function executeBatchDelete(sfWebRequest $request)
  { throw new liEvenementException('This action is not implemented.'); }
}
