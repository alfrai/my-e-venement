<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(dirname(__FILE__).'/../lib/BasesfGuardAuthActions.class.php');

/**
 *
 * @package    symfony
 * @subpackage plugin
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id: actions.class.php 23319 2009-10-25 12:22:23Z Kris.Wallsmith $
 */
class sfGuardAuthActions extends BasesfGuardAuthActions
{
  public function executeSignin($request)
  {
    $this->ipv6 = array(
      'ready' => filter_var($request->getRemoteAddress(), FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) || sfConfig::get('project_network_ipv6_ready',true),
      'on' => filter_var($request->getRemoteAddress(), FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)
    );
    return parent::executeSignin($request);
  }
  public function executeError404(sfWebRequest $request)
  {
  }
}
