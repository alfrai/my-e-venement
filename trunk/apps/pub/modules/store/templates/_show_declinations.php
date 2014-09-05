<?php //echo $form->renderFormTag(url_for('cart/show'), array('class' => 'adding-tickets')) ?>
<table><tbody>
  <tr>
    <td class="informations">
      <p class="image">
        <a href="<?php echo url_for('store/mod?product_id='.$product->id) ?>" id="ajax-init-data"></a>
        <img
          src="<?php echo url_for('picture/display?id='.$product->picture_id) ?>"
          alt="<?php echo $product ?>"
          title="<?php echo $product ?>"
          class="pub-product"
        />
      </p>
      <div class="text">
        <?php echo $product->getRawValue()->description ?>
      </div>
    </td>
    <td class="declinations">
      <?php foreach ( $declinations as $declination ): ?>
        <?php include_partial('show_declination', array('declination' => $declination)) ?>
      <?php endforeach ?>
    </td>
  </tr>
</tbody></table>
<?php use_javascript('pub-totals?'.date('Ymd')) ?>
<!--</form>-->

