<?php include_partial('assets') ?>
<?php include_partial('global/flashes') ?>
<div class="ui-widget ui-widget-content ui-corner-all tdp-import">
  <div class="tdp-widget-header ui-widget-header ui-corner-all">
    <h1><?php echo __('Import') ?></h1>
  </div>
  <div class="tdp-explanation ui-widget-content ui-corner-all">
    <p><?php echo __('You are about to import a bulk of contacts. Be careful to respect this type of file:') ?></p>
    <pre>
ID,ETABLISSEMENT,PRENOM,NOM,ADRESSE1,ADRESSE2,CP,VILLE,GROUPE,TELEPHONE,EMAIL
1,DCAP,Marie-Claire,DURAND,MAIRIE D'ECHIROLLES,,38130,ECHIROLLES,ASSOCULT,0707070707,contact@ville-echirolles.fr
2,"CULTURES DU CŒUR",Jeanne,CHANCELLE,11 RUE DOCTEUR LAENNEC,,38610,GIERES,ASSOCULT,04 76 24 08 48,culturesducoeur@free.fr
3,"",Christian,MAJEUR,9 ALLEE DES LAURIERS,,69300,CALUIRE,AUTEUR,,,,,,
    </pre>
    <p><?php echo __('Do not forget that this file must be UTF-8 encoded, its fields must be separated by a comma, and the first line dedicated to field titles... Actually a standard CSV format.'); ?></p>
    <p><?php echo __('Try to import a maximum of %%nb%% lines at once. Then repeat the import the number of times needed.',array('%%nb%%' => 250)) ?></p>
  </div>
  <form class="tdp-import" method="post" enctype="multipart/form-data" action="<?php echo url_for('contact/import') ?>">
    <p>
      <input type="file" name="rp-import" pattern="\.(csv)$" />
      <input type="submit" name="submit" value="<?php echo __('Send') ?>" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" />
    </p>
  </form>
</div>
