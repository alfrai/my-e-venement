<?php use_stylesheet('pub-seated-plan-legend?'.date('Ymd')) ?>
<ul>
  <li>
    <span class="seat free"></span>
    <?php echo __('Available seat') ?>
  </li>
  <li>
    <span class="seat occupied"></span>
    <?php echo __('Unavailable seat') ?>
  </li>
  <li>
    <span class="seat ordered"></span>
    <?php echo __('Selected seat') ?>
  </li>
  <li>
    <span class="seat hover"></span>
    <?php echo __('Seat under your cursor') ?>
  </li>
  <li>
    <span class="seat orphan"></span>
    <?php echo __('Orphan seat') ?>
  </li>
</ul>
