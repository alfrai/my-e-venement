<?php

/**
 * member_card module configuration.
 *
 * @package    ##PROJECT_NAME##
 * @subpackage member_card
 * @author     ##AUTHOR_NAME##
 * @version    SVN: $Id: helper.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
class MemberCardHelper extends sfModelGeneratorHelper
{
  public function getUrlForAction($action)
  {
    return 'list' == $action ? 'member_card' : 'member_card_'.$action;
  }
}
