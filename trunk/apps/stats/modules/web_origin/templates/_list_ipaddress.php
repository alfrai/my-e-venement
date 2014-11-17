<?php $alias = Doctrine::getTable('WebOriginIP')->createQuery('ip')->andWhere('ip.ipaddress = ?', $web_origin->ipaddress)->fetchOne() ?>
<?php if ( $alias ): ?>
<span class="alias"><?php echo $alias ?></span>
→
<?php endif ?>
<span class="IP"><?php echo $web_origin->ipaddress ?></span>
