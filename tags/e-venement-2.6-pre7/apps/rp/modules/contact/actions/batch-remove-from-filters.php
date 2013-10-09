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
*    Copyright (c) 2011 Ayoub HIDRI <ayoub.hidri AT gmail.com>
*    Copyright (c) 2006-2013 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php

  $this->getContext()->getConfiguration()->loadHelpers('I18N');
  
  $ids = $request->getParameter('ids');
  if ( is_array($ids) )
  {
    foreach ( $ids as $key => $id )
    if ( $id."" !== intval($id)."" )
      unset($ids[$key]);
    
    $filters = $this->getFilters();
    
    if ( !isset($filters['not_contacts_list']) )
      $filters['not_contacts_list'] = $ids;
    else
      $filters['not_contacts_list'] = array_merge($filters['not_contacts_list'],$ids);
    
    $this->setFilters($filters);
  }
