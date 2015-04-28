<?php $codes = array(); foreach ( $product->Declinations as $declination ) $codes[$declination->code] = $declination->code; ksort($codes); ?>
<span class="code"><?php echo implode('</span><br/><span class="code">', $codes) ?></span>
