<?php use_helper('CrossAppLink') ?>

<td style="white-space: nowrap;">
  <ul class="sf_admin_td_actions fg-buttonset fg-buttonset-single">  
    <li class="sf_admin_action_showup">
      <?php echo link_to(
      	__('Show up', array(), 'messages'),
      	'transactionsList/show?id='.$transaction->getId(),
      	'class=fg-button-mini fg-button ui-state-default fg-button-icon-left '
      ) ?>      
    </li>
    
    <!-- create kiosk admin task -->
    <?php if( $transaction->closed && $transaction->User->username == 'kiosk' ): ?>
      <li class="sf_admin_action_admintask">
	    <a 
	      href="<?php echo cross_app_url_for('kiosk', 'admin/new?transaction=' . $transaction->getId()) ?>"
	      class="fg-button-mini fg-button ui-state-default fg-button-icon-left"
	    >
	   	  <span class="ui-icon ui-icon-print"></span>
	    </a>
      </li>
    <?php endif ?>
  </ul>
</td>
