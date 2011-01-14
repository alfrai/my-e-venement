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
      <li>
        <ul class="second">
          <li><a href="" target="_blank"><?php echo __('New screen',array(),'menu') ?></a></li>
          <li><a href="<?php echo sfContext::getInstance()->getConfiguration()->generateExternalUrl(array('app'=>'default','name'=>'default')) ?>" target="_blank"><?php echo __('Index',array(),'menu') ?></a></li>
          <li class="spaced"><a href="#" onclick="javascript: window.sidebar.addPanel(document.title,window.location,'');"><?php echo __('Bookmark',array(),'menu') ?></a></li>
          <li><a href="#" onclick="javascript: print()"><?php echo __('Print',array(),'menu') ?></a></li>
          <li class="spaced"><a href="<?php echo sfContext::getInstance()->getConfiguration()->generateExternalUrl(array('app'=>'default','name'=>'sf_guard_signin')) ?>"><?php echo __('Change user',array(),'menu') ?></a></li>
          <li><a href="<?php echo sfContext::getInstance()->getConfiguration()->generateExternalUrl(array('app'=>'default','name'=>'sf_guard_signout')) ?>"><?php echo __('Logout',array(),'menu') ?></a></li>
          <li><a href="<?php echo sfContext::getInstance()->getConfiguration()->generateExternalUrl(array('app'=>'default','name'=>'sf_guard_signout')) ?>" onclick="javascript: window.close()"><?php echo __('Close',array(),'menu') ?></a></li>
        </ul>
      <span class="title"><?php echo __('Screen',array(),'menu') ?></span></li>
      <li>
        <ul class="second">
          <li><a href="<?php echo sfContext::getInstance()->getConfiguration()->generateExternalUrl(array('app'=>'rp','name'=>'contact')) ?>"><?php echo __('Contacts',array(),'menu') ?></a></li>
          <li><a href="<?php echo sfContext::getInstance()->getConfiguration()->generateExternalUrl(array('app'=>'rp','name'=>'organism')) ?>"><?php echo __('Organisms',array(),'menu') ?></a></li>
          <li class="spaced"><a href="<?php echo sfContext::getInstance()->getConfiguration()->generateExternalUrl(array('app'=>'rp','name'=>'group')) ?>"><?php echo __('Groups',array(),'menu') ?></a></li>
          <li><a href="<?php echo sfContext::getInstance()->getConfiguration()->generateExternalUrl(array('app'=>'rp','name'=>'email')) ?>"><?php echo __('Emailing',array(),'menu') ?></a></li>
        </ul>
      <span class="title"><?php echo __('Pub. Rel.',array(),'menu') ?></span></li>
      <li>
        <ul class="second">
          <li><a href=""><?php echo __('Events',array(),'menu') ?></a></li>
          <li><a href=""><?php echo __('Agenda',array(),'menu') ?></a></li>
          <li><a href=""><?php echo __('Locations',array(),'menu') ?></a></li>
        </ul>
        <span class="title"><?php echo __('Events',array(),'menu') ?></span></li>
      </li>
      <li>
        <ul class="second">
          <li><a href=""><?php echo __('Tickets',array(),'menu') ?></a></li>
          <li><a href=""><?php echo __('Cancelations',array(),'menu') ?></a></li>
          <li class="spaced"><a href=""><?php echo __('Deposit',array(),'menu') ?></a></li>
          <li><a href=""><?php echo __('Sells',array(),'menu') ?></a></li>
          <li><a href=""><?php echo __('In progress',array(),'menu') ?></a></li>
          <li class="spaced"><a href=""><?php echo __('Asks',array(),'menu') ?></a></li>
          <li><a href=""><?php echo __('Orders',array(),'menu') ?></a></li>
          <li><a href=""><?php echo __('Invoices',array(),'menu') ?></a></li>
          <li><a href=""><?php echo __('Debts',array(),'menu') ?></a></li>
          <li><a href=""><?php echo __('Duplicatas',array(),'menu') ?></a></li>
          <li class="spaced"><a href=""><?php echo __('Sales Ledger',array(),'menu') ?></a></li>
          <li><a href=""><?php echo __('Cash Book',array(),'menu') ?></a></li>
        </ul>
        <span class="title"><?php echo __('Ticketting',array(),'menu') ?></span></li>
      </li>
      <li>
        <ul class="second">
          <li><a href=""><?php __('Pupils and groups',array(),'menu') ?></a></li>
        </ul>
        <span class="title"><?php echo __('Groups',array(),'menu') ?></span>
      </li>
      <li>
        <ul class="second">
          <li><a><?php echo __('General',array(),'menu') ?></a>
            <ul class="third">
              <li><a href=""><?php echo __('Users',array(),'menu') ?></a></li>
              <li class="spaced"><a href=""><?php echo __('Maintenance',array(),'menu') ?></a></li>
              <li><a href=""><?php echo __('Archiving',array(),'menu') ?></a></li>
              <li class="spaced"><a href=""><?php echo __('System Logs',array(),'menu') ?></a></li>
            </ul>
          </li>
          <li><a><?php echo __('P.R.',array(),'menu') ?></a>
            <ul class="third">
              <li><a href="<?php echo url_for('option_csv/index') ?>"><?php echo __('Extractions',array(),'menu') ?></a></li>
              <li><a href="<?php echo url_for('option_labels/index') ?>"><?php echo __('Labels',array(),'menu') ?></a></li>
              <li class="spaced"><a href="<?php echo url_for('title_type/index') ?>"><?php echo __('Generic title',array(),'menu') ?></a></li>
              <li><a href="<?php echo url_for('phone_type/index') ?>"><?php echo __('Types of phones',array(),'menu') ?></a></li>
              <li class="spaced"><a href="<?php echo url_for('professional_type/index') ?>"><?php echo __('Types of functions',array(),'menu') ?></a></li>
              <li><a href="<?php echo url_for('organism_category/index') ?>"><?php echo __('Organism categories',array(),'menu') ?></a></li>
            </ul>
          </li>
          <li><a><?php echo __('Events / Ticketting',array(),'menu') ?></a>
            <ul class="third">
              <li><a href=""><?php echo __('Event categories',array(),'menu') ?></a></li>
              <li><a href=""><?php echo __('Meta-events',array(),'menu') ?></a></li>
              <li><a href=""><?php echo __('Colors',array(),'menu') ?></a></li>
              <li class="spaced"><a href=""><?php echo __('Rates',array(),'menu') ?></a></li>
              <li><a href=""><?php echo __('Payment methods',array(),'menu') ?></a></li>
              <li class="spaced"><a href=""><?php echo __('Workspaces',array(),'menu') ?></a></li>
            </ul>
          </li>
          <li><a><?php echo __('Groups')?></a>
            <ul class="third">
              <li><a href=""><?php echo __('General settings',array(),'menu') ?></a></li>
              <li><a href=""><?php echo __('Quotas leaders',array(),'menu') ?></a></li>
            </ul>
          </li>
          <li><a href=""><?php echo __('Online ticketting',array(),'menu') ?></a></li>
        </ul>
        <span class="title"><?php echo __('Settings',array(),'menu') ?></span>
      </li>
      <li>
        <ul class="second">
          <li><a href="http://www.libre-informatique.fr/sw/01-Billetterie/e-venement/Manuels" target="_blank"><?php echo __('Documentation',array(),'menu') ?></a></li>
          <li><a href="<?php echo sfContext::getInstance()->getConfiguration()->generateExternalUrl(array('app'=>'default','name'=>'about')) ?>" class="fancybox"><?php echo __('About',array(),'menu') ?></a></li>
        </ul>
        <span class="title"><?php echo __('Help',array(),'menu') ?></span>
      </li>
