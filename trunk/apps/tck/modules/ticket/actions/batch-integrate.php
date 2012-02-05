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
  $mid = $request->getParameter('manifestation_id');
  $q = Doctrine::getTable('Manifestation')->createQuery('m')
    ->where('id = ?',$mid);
  $this->manifestation = $q->fetchOne();
  
  $this->form = new TicketsIntegrationForm($this->manifestation);
  
  $files = $request->getFiles('integrate');
  if ( count($files) > 0 )
  {
    $this->form->bind($integrate = $request->getParameter('integrate'),$request->getFiles('integrate'));
    if ( $this->form->isValid() )
    {
      $files = $request->getFiles('integrate');
      $fp = fopen($files['file']['tmp_name'],'r');
      $transaction = new Transaction();
      
      switch ( $integrate['filetype'] ) {
      case 'fb':
        require(dirname(__FILE__).'/batch-integrate-fb.php');
        break;
      default:
        require(dirname(__FILE__).'/batch-integrate-default.php');
        break;
      }
      
      fclose($fp);
      sfContext::getInstance()->getConfiguration()->loadHelpers(array('Url','I18N'));
      $this->getUser()->setFlash('notice',__('File importated.'));
      $this->redirect(url_for('ticket/batchIntegrate?manifestation_id='.$this->manifestation->id));
    }
    else
    {
      print_r($integrate);
      $this->getUser()->setFlash('error','Error in the form validation');
    }
  }
