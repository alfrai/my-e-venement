<div class="gauge" id="manifestation-<?php echo $sf_request->getParameter('manifestation_id') ?>">
  <?php foreach ( array('sold' => 'sells','ordered' => 'orders', 'free' => 'free') as $field => $class ): ?><span
    class="seat <?php echo $class ?>"
    style="width: <?php echo $length[$field] ?>%"
    title="<?php echo $nb[$field] ?> - <?php echo $desc[$field] ?>"
  ><?php if ( $nb[$field]/$nb['value'] > 0.1 ): ?><span class="nb"><?php echo $nb[$field] ?></span><?php endif ?></span><?php endforeach ?><span
    class="seat demands"
    style="margin-left: <?php echo ($size = $length['sold'] + $length['ordered']) + $length['demanded'] <= 100 ? $size : 100 - $length['demanded'] ?>%; width: <?php echo $length['demanded'] ?>%"
    title="<?php echo $nb['demanded'] ?>  - <?php echo $desc['demanded'] ?>"
  ></span>
  <span class="txt <?php echo $nb['free'] <= 0 ? 'overbooking' : '' ?>" title="<?php echo $nb['free'] < 0 ? __('%%nb%% overbooked places',array('%%nb%%' => -$nb['free'])) : '' ?>"><?php echo __('%%nb%% pl.',array('%%nb%%' => $nb['value'])) ?></span>
</div>
