<?php include_partial('global/ariane', array('active' => 1)) ?>
<?php include_partial('global/flashes') ?>
<?php include_partial('show_title', array('manifestation' => $manifestation)) ?>

<?php if ( sfConfig::get('app_options_synthetic_plans', false) ): ?>
  <?php use_stylesheet('pub-manifestation-synthetic') ?>
  <?php use_javascript('pub-manifestation-synthetic') ?>
  <?php include_partial('show_categories', array('manifestation' => $manifestation)) ?>
  <?php include_partial('show_plans', array('manifestation' => $manifestation)) ?>
  <?php include_partial('show_named_tickets', array('manifestation' => $manifestation)) ?>
<?php else: ?>
  <?php include_partial('show_gauges', array('gauges' => $gauges, 'manifestation' => $manifestation, 'form' => $form, 'mcp' => $mcp, )) ?>
  <?php include_partial('show_footer', array('manifestation' => $manifestation)) ?>
  <?php include_partial('show_ease') ?>
  <?php include_partial('global/show_links', array('objects' => $manifestation)) ?>
<?php endif ?>
