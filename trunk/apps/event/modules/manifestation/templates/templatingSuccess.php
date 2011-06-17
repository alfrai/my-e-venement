<?php include_partial('assets') ?>

<?php include_partial('manifestation/flashes') ?>

<?php echo $form->renderFormTag(url_for('manifestation/templating')) ?>
<?php echo $form->renderHiddenFields(); ?>
<table>
<?php echo $form; ?>
<tr><td></td><td><input type="submit" name="submit" value="<?php echo __('Apply') ?>" /></td></tr>
</table>
</form>
