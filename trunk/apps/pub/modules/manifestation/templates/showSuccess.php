<?php include_partial('global/ariane', array('active' => 1)) ?>
<?php include_partial('global/flashes') ?>
<?php include_partial('show_title', array('manifestation' => $manifestation)) ?>

<?php if ( sfConfig::get('app_options_synthetic_plans', false) ): ?>
  
  <?php use_stylesheet('pub-manifestation-synthetic?'.date('Ymd')) ?>
  <?php use_javascript('pub-manifestation-synthetic?'.date('Ymd')) ?>
  <?php use_javascript('pub-seated-plan?'.date('Ymd')) ?>
  
  <div id="tickets">
    <?php include_partial('show_named_tickets', array('manifestation' => $manifestation)) ?>
  </div>
  <div id="container">
    <?php
      $texts = sfConfig::get('app_texts_synthetic', array());
      foreach ( array('plans', 'categories') as $field )
      if ( !isset($texts[$field]) )
        $texts[$field] = '';
    ?>
    <h4 data-tab="#plans"><?php echo __('Choice by the seating') ?></h4>
    <h4 class="hidden" data-tab="#categories"><?php echo __('Automatic choice by category') ?></h4>
    <div class="tab" id="plans">
      <div class="li-content">
        <?php include_partial('show_plans', array('manifestation' => $manifestation)) ?>
        <div class="description"><?php echo $texts['plans'] ?></div>
      </div>
    </div>
    <div class="tab hidden" id="categories">
      <div class="li-content">
        <?php include_partial('show_categories', array('manifestation' => $manifestation)) ?>
        <div class="description"><?php echo $texts['categories'] ?></div>
      </div>
    </div>
  </div>
  <div class="clear"></div>
  
<?php else: ?>
  
  <?php include_partial('show_gauges', array('gauges' => $gauges, 'manifestation' => $manifestation, 'form' => $form, 'mcp' => $mcp, )) ?>
  <?php include_partial('show_footer', array('manifestation' => $manifestation)) ?>
  <?php include_partial('show_ease') ?>
  <?php include_partial('global/show_links', array('objects' => $manifestation)) ?>
  
<?php endif ?>
