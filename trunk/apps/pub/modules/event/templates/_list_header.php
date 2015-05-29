<?php include_partial('global/ariane',array('active' => 1)) ?>
<?php include_partial('global/flashes') ?>

<?php if ( $slug = $sf_request->getParameter('meta-event', false) ): ?>
<h2><?php echo Doctrine::getTable('MetaEvent')->findOneBySlug($slug) ?></h2>
<?php endif ?>
