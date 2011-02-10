<?php

/**
 * Checkpoint form.
 *
 * @package    e-venement
 * @subpackage form
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class CheckpointForm extends BaseCheckpointForm
{
  public function configure()
  {
    $this->widgetSchema['event_id']->setOption('add_empty',true);
  }
}
