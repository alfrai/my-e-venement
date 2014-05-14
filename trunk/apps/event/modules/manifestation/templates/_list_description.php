<div style="background-color: <?php echo $manifestation->color_id ? $manifestation->Color->color : '' ?>">
  <?php echo mb_substr($manifestation->description,0,28) ?>
  <?php echo mb_strlen($manifestation->description) > 28 ? ' ...' : '' ?>
</div>
