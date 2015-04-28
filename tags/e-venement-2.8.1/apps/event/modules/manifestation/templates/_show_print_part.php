<p class="tab-print">
  <?php if ( $tab == 'spectators' ): ?>
  <a class="fg-button fg-button-icon-left ui-state-default extract" href="<?php echo url_for('manifestation/csv?id='.$manifestation_id) ?>" title="<?php echo __('... the list of spectators with a ticket') ?>" target="_blank">
    <span class="ui-icon ui-icon-cart"></span>
    <?php echo __('Extract') ?>
  </a>
  <?php endif ?>
  <a class="fg-button fg-button-icon-left ui-state-default print" href="#">
    <span class="ui-icon ui-icon-print"></span>
    <?php echo __('Print',array(),'menu') ?>
  </a>
  <a class="fg-button fg-button-icon-left ui-state-default refresh" href="<?php echo url_for('manifestation/'.$action.'?id='.$manifestation_id) ?>?refresh">
    <span class="ui-icon ui-icon-arrowrefresh-1-s"></span>
    <?php echo __('Refresh') ?>
  </a>
  <script type="text/javascript">
    // NOT EXECUTED WHEN CALLED THROUGH AJAX
    $(document).ready(function(){
      <?php include_partial('show_print_part_js',array('tab' => $tab)) ?>
    });
  </script>
</p>
