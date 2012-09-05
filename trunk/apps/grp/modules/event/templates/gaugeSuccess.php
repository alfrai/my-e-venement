<div class="gauge" id="manifestation-<?php echo $sf_request->getParameter('manifestation_id') ?>">
  <?php foreach ( array('sells','orders','free') as $type ): ?><span
    class="seat <?php echo $type ?>"
    style="width: <?php echo $length[$type] ?>%"
    title="<?php echo $nb[$type] ?> - <?php echo $desc[$type] ?>"
  ></span><?php endforeach ?><span
    class="seat demands"
    style="margin-left: <?php echo ($size = $length['sells'] + $length['orders']) + $length['demands'] <= 100 ? $size : 100 - $length['demands'] ?>%; width: <?php echo $length['demands'] ?>%"
    title="<?php echo $nb['demands'] ?>  - <?php echo $desc['demands'] ?>"
  ></span>
</div>
