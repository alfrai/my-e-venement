  <a class="meta-data-url" href="<?php echo url_for('manifestation/statsMetaData?id='.$form->getObject()->id) ?>"></a>
  <a class="filling-data-url" href="<?php echo url_for('manifestation/statsFillingData?id='.$form->getObject()->id) ?>"></a>
  <?php use_javascript('manifestation-stats?'.date('Ymd')) ?>
