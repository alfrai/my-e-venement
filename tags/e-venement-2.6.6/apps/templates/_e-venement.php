<?php use_stylesheet('rss') ?>
<div class="feed">
<?php $feed = sfFeedPeer::createFromWeb(sfConfig::get('app_feed_url','http://www.e-venement.org/feed/')); ?>
<?php foreach ( $feed->getItems() as $item ): ?>
<article>
<h1><?php echo link_to($item->getTitle(), $item->getLink(), array('target' => '_blank')) ?></h1>
<time class="entry-date" datetime="<?php echo $item->getPubDate() ?>"><?php echo format_date($item->getPubDate(), 'EEE d MMM yyyy') ?></time>
<?php echo $item->getContent() ?>
</article>
<?php endforeach ?>
</div>
<script type="text/javascript">$(document).ready(function(){
  $('.feed a').attr('target','_blank').click(function(){
    $('#transition .close').click();
  });
});</script>
