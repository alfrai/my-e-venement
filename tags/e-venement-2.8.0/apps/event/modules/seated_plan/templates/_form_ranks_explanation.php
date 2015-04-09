<div class="ranks_explanation">
  <p>
    <?php echo nl2br(__("This form will allow you to define the rank of seats in bulk on your map. Using it will save you a lot of time, because you will only have to set the rank of particular seats. The venues are usually symetrical, you will probably make at least to passes. Take the left side, then the right side of instance.
Careful: this tool has its limits. It works good only with batches of seats named for instance from A-1 to H-74. Test it carefully before to conclude on its utility level.")) ?>
  </p>
  <a href="<?php echo url_for('seated_plan/batchSeatSetRank') ?>" style="display: none;"></a>
</div>
<?php include_partial('form_ranks_field', array(
  'name'  => 'id',
  'value' => $form->getObject()->id,
  'type'  => 'hidden',
)) ?>
