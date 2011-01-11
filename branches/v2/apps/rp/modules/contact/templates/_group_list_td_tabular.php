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
<td class="sf_admin_text sf_admin_list_td_name">
  <?php echo link_to($contact->getName(),'contact/show?id='.$contact->id) ?>
</td>
<td class="sf_admin_text sf_admin_list_td_firstname">
  <?php echo link_to($contact->getFirstname() ? $contact->getFirstname() : ' ','contact/show?id='.$contact->id) ?>
</td>
<td class="sf_admin_text sf_admin_list_td_professional">
  <?php if ( $professional ): ?>
  <?php echo $professional->getNameType() ?>
  <?php endif ?>
</td>
<td class="sf_admin_text sf_admin_list_td_organism">
  <?php if ( $professional ): ?>
  <?php echo link_to($professional->Organism,'organism/show?id='.$professional->Organism->id) ?>
  <?php endif ?>
</td>
