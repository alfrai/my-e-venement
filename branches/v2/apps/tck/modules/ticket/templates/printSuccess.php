<?php foreach ( $tickets as $ticket ): ?>
  <div class="page">
  <?php include_partial('ticket_html',array('ticket' => $ticket)) ?>
  </div>
<?php endforeach ?>
<div id="options">
  <!--<p id="close"></p>-->
</div>
