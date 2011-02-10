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

class addWSMECredentials extends sfGuardBasicSecurityFilter
{
  function execute($filterChain)
  {
    $context = $this->getContext();
    $request = $context->getRequest();
    $user = $context->getUser();
    
    $object = $context->getController()
      ->getAction($context->getModuleName(),$context->getActionName())
      ->getRoute()->getObject();
    
    if ( !($object instanceof liEventSecurityAccessor) )
      return parent::execute($filterChain);
    
    $meta_event_ids = $user->getMetaEventsCredentials();
    $workspace_ids = $user->getWorkspacesCredentials();
    
    if ( $user->hasCredential(myUser::CREDENTIAL_METAEVENT_PREFIX.$object->getMEid()) )
      $user->addCredential(myUser::CREDENTIAL_METAEVENT_PREFIX.'can-access');
    elseif ( $user->hasCredential(myUser::CREDENTIAL_METAEVENT_PREFIX.'can-access') )
      $user->removeCredential(myUser::CREDENTIAL_METAEVENT_PREFIX.'can-access');
    
    // Continue down normal filterChain
    parent::execute($filterChain);

    // On the way back, before rendering, remove owner credential again
    // The code after the call to $filterChain->execute() executes after the
    // action execution and before the rendering.
    if ($user->hasCredential(myUser::CREDENTIAL_METAEVENT_PREFIX.'can-access')) {
      $user->removeCredential(myUser::CREDENTIAL_METAEVENT_PREFIX.'can-access');
    }
  }

}

