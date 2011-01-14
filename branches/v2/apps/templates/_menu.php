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
          <li><a href="" target="_blank"><?php echo __('New screen') ?></a></li>
          <li><a href="<?php echo sfContext::getInstance()->getConfiguration()->generateExternalUrl(array('app'=>'default','name'=>'default')) ?>" target="_blank"><?php echo __('Index') ?></a></li>
          <li class="spaced"><a href="#" onclick="javascript: window.sidebar.addPanel(document.title,window.location,'');"><?php echo __('Bookmark') ?></a></li>
          <li><a href="#" onclick="javascript: print()"><?php echo __('Print') ?></a></li>
          <li class="spaced"><a href="<?php echo sfContext::getInstance()->getConfiguration()->generateExternalUrl(array('app'=>'default','name'=>'sf_guard_signin')) ?>"><?php echo __('Change user') ?></a></li>
          <li><a href="<?php echo sfContext::getInstance()->getConfiguration()->generateExternalUrl(array('app'=>'default','name'=>'sf_guard_signout')) ?>"><?php echo __('Logout') ?></a></li>
          <li><a href="<?php echo sfContext::getInstance()->getConfiguration()->generateExternalUrl(array('app'=>'default','name'=>'sf_guard_signout')) ?>" onclick="javascript: window.close()"><?php echo __('Close') ?></a></li>
        </ul>
      <span class="title"><?php echo __('Screen') ?></span></li>
      <li>
        <ul class="second">
          <li><a href="<?php echo sfContext::getInstance()->getConfiguration()->generateExternalUrl(array('app'=>'rp','name'=>'contact')) ?>"><?php echo __('Contacts') ?></a></li>
          <li><a href="<?php echo sfContext::getInstance()->getConfiguration()->generateExternalUrl(array('app'=>'rp','name'=>'organism')) ?>"><?php echo __('Organisms') ?></a></li>
          <li class="spaced"><a href="<?php echo sfContext::getInstance()->getConfiguration()->generateExternalUrl(array('app'=>'rp','name'=>'group')) ?>"><?php echo __('Groups') ?></a></li>
          <li><a href="<?php echo sfContext::getInstance()->getConfiguration()->generateExternalUrl(array('app'=>'rp','name'=>'email')) ?>"><?php echo __('Emailing') ?></a></li>
        </ul>
      <span class="title"><?php echo __('Pub. Rel.') ?></span></li>
      <li>
        <ul class="second">
          <li><a href=""><?php echo __('Events') ?></a></li>
          <li><a href=""><?php echo __('Agenda') ?></a></li>
          <li><a href=""><?php echo __('Locations') ?></a></li>
        </ul>
        <span class="title"><?php echo __('Events') ?></span></li>
      </li>
      <li>
        <ul class="second">
          <li><a href=""><?php echo __('Tickets') ?></a></li>
          <li><a href=""><?php echo __('Cancelations') ?></a></li>
          <li class="spaced"><a href=""><?php echo __('Deposit') ?></a></li>
          <li><a href=""><?php echo __('Sells') ?></a></li>
          <li><a href=""><?php echo __('In progress') ?></a></li>
          <li class="spaced"><a href=""><?php echo __('Asks') ?></a></li>
          <li><a href=""><?php echo __('Orders') ?></a></li>
          <li><a href=""><?php echo __('Invoices') ?></a></li>
          <li><a href=""><?php echo __('Debts') ?></a></li>
          <li><a href=""><?php echo __('Duplicatas') ?></a></li>
          <li class="spaced"><a href=""><?php echo __('Sales Ledger') ?></a></li>
          <li><a href=""><?php echo __('Cash Book') ?></a></li>
        </ul>
        <span class="title"><?php echo __('Ticketting') ?></span></li>
      </li>
      <li>
        <ul class="second">
          <li><a href=""><?php __('Pupils and groups') ?></a></li>
        </ul>
        <span class="title"><?php echo __('Groups') ?></span>
      </li>
      <li>
        <ul class="second">
          <li><a><?php echo __('General') ?></a>
            <ul class="third">
              <li><a href=""><?php echo __('Users') ?></a></li>
              <li class="spaced"><a href=""><?php echo __('Maintenance') ?></a></li>
              <li><a href=""><?php echo __('Archiving') ?></a></li>
              <li class="spaced"><a href=""><?php echo __('System Logs') ?></a></li>
            </ul>
          </li>
          <li><a><?php echo __('P.R.') ?></a>
            <ul class="third">
              <li><a href="<?php echo url_for('option_csv/index') ?>"><?php echo __('Extractions') ?></a></li>
              <li><a href="<?php echo url_for('option_labels/index') ?>"><?php echo __('Labels') ?></a></li>
              <li class="spaced"><a href="<?php echo url_for('title_type/index') ?>"><?php echo __('Generic title') ?></a></li>
              <li><a href="<?php echo url_for('phone_type/index') ?>"><?php echo __('Types of phones') ?></a></li>
              <li class="spaced"><a href="<?php echo url_for('professional_type/index') ?>"><?php echo __('Types of functions') ?></a></li>
              <li><a href="<?php echo url_for('organism_category/index') ?>"><?php echo __('Organism categories') ?></a></li>
            </ul>
          </li>
          <li><a><?php echo __('Events / Ticketting') ?></a>
            <ul class="third">
              <li><a href=""><?php echo __('Event categories') ?></a></li>
              <li><a href=""><?php echo __('Meta-events') ?></a></li>
              <li><a href=""><?php echo __('Colors') ?></a></li>
              <li class="spaced"><a href=""><?php echo __('Rates') ?></a></li>
              <li><a href=""><?php echo __('Payment methods') ?></a></li>
              <li class="spaced"><a href=""><?php echo __('Workspaces') ?></a></li>
            </ul>
          </li>
          <li><a><?php echo __('Groups')?></a>
            <ul class="third">
              <li><a href=""><?php echo __('General settings') ?></a></li>
              <li><a href=""><?php echo __('Quotas leaders') ?></a></li>
            </ul>
          </li>
          <li><a href=""><?php echo __('Online ticketting') ?></a></li>
        </ul>
        <span class="title"><?php echo __('Settings') ?></span>
      </li>
      <li>
        <ul class="second">
          <li><a href="http://www.libre-informatique.fr/sw/01-Billetterie/e-venement/Manuels" target="_blank"><?php echo __('Documentation') ?></a></li>
          <li><a href="<?php echo sfContext::getInstance()->getConfiguration()->generateExternalUrl(array('app'=>'default','name'=>'about')) ?>" class="fancybox"><?php echo __('About') ?></a></li>
        </ul>
        <span class="title"><?php echo __('Help') ?></span>
      </li>
