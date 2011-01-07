<script type="text/javascript">var professionals = []; var professional_new;</script>

<div class="sf_admin_edit ui-widget ui-widget-content ui-corner-all professional new">
  <div class="ui-widget-header ui-corner-all fg-toolbar"><h2><?php echo __('New professional') ?></h2></div>
  <div id="professional-new" class="sf_admin_form"></div>
  <script type="text/javascript">professional_new = '<?php echo url_for('professional/new') ?>';</script>
</div>

<?php foreach ( $contact->Professionals as $i => $professional ): ?>
<div class="sf_admin_edit ui-widget ui-widget-content ui-corner-all professional">
  <div class="ui-widget-header ui-corner-all fg-toolbar"><h2><?php echo __('Organism').': '.link_to($professional->Organism,'organism/edit?id='.$professional->organism_id) ?></h2></div>
  <div id="professional-<?php echo $i ?>" class="sf_admin_form"></div>
  <script type="text/javascript">professionals.push('<?php echo url_for('professional/edit?id='.$professional['id']) ?>');</script>
</div>
<?php endforeach ?>

