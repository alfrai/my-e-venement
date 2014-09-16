<div>
  <?php echo mb_substr($manifestation->description,0,85) ?>
  <?php echo mb_strlen($manifestation->description) > 80 ? ' ...' : '' ?>
</div>
