<?php
/**********************************************************************************
*
*	    This file is part of e-venement.
*
*    e-venement is free software; you can redistribute it and/or modify
*    it under the terms of the GNU General Public License as published by
*    the Free Software Foundation; either version 2 of the License.
*
*    e-venement is distributed in the hope that it will be useful,
*    but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*    GNU General Public License for more details.
*
*    You should have received a copy of the GNU General Public License
*    along with e-venement; if not, write to the Free Software
*    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*
*    Copyright (c) 2006-2012 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2012 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php include_stylesheets_for_form($form) ?>
<?php include_javascripts_for_form($form) ?>
<?php use_stylesheet('/sfFormExtraPlugin/css/jquery.autocompleter.css') ?>
<?php use_javascript('/sfFormExtraPlugin/js/jquery.autocompleter.js') ?>
<?php use_javascript('grp-event') ?>
<?php use_helper('CrossAppLink') ?>

<div class="sf_admin_form">
    <div class="sf_admin_actions_block ui-widget">
      <?php include_partial('form_actions', array('professional' => $professional, 'form' => $form, 'configuration' => $configuration, 'helper' => $helper)) ?>
    </div>
  
  <p>&nbsp;</p>
  <p>&nbsp;</p>
  
  <div id="copy-paste"><?php echo __('Drag and drop elsewhere to paste selected content') ?></div>
  
  <table class="grp-entry">
    <tbody>
      <?php $i = 1 ?>
      <?php $ces = array(); foreach ( $professional->ContactEntries as $ce ) $ces[(string)$ce->Entry->Event.$ce->id] = $ce; ksort($ces); ?>
      <?php foreach ( $ces as $ce ): ?>
      <?php $entry = $ce->Entry ?>
      <?php $mes = array(); foreach ( $entry->ManifestationEntries as $me ) $mes[$me->Manifestation->happens_at.$me->id] = $me; ksort($mes); ?>
      <?php foreach ( $mes as $me ): ?>
      <tr class="manifestation-<?php echo $me->id ?> <?php echo ++$i%2 == 0 ? 'pair' : 'impair' ?> <?php if ( !is_null($ce->transaction_id) ) echo 'transposed' ?> <?php if ( $ce->confirmed ) echo 'confirmed' ?>">
        <?php $j = 0 ?>
        <td class="manifestation manifestation-<?php echo $me->id ?> <?php echo ++$j%2 == 0 ? 'pair' : 'impair' ?>">
          <?php include_partial('form_manifestation',array('me' => $me, )) ?>
        </td>
        <?php foreach ( $ces as $ce1 ): ?>
        <?php
          $entry_element = Doctrine::getTable('EntryElement')->fetchOneByContactManifestation($ce1->id, $me->id);
          if ( !$entry_element )
          {
            $entry_element = new EntryElement;
            $entry_element->contact_entry_id = $ce->id;
            $entry_element->manifestation_entry_id = $me->id;
            $entry_element->save();
          }
        ?>
        <td class="contact-<?php echo $ce->id ?> <?php echo ++$j%2 == 0 ? 'pair' : 'impair' ?> <?php echo $entry_element->second_choice ? 'second_choice' : '' ?> <?php echo $entry_element->accepted ? 'accepted' : '' ?>">
          <?php if ( $ce1->id === $ce->id ): ?>
          <?php
            if ( !$entry_element )
            {
              $entry_element = new EntryElement;
              $entry_element->contact_entry_id = $ce->id;
              $entry_element->manifestation_entry_id = $me->id;
              $entry_element->save();
            }
          ?>
          <script type="text/javascript"><!--
            $(document).ready(function(){
              $('tr.manifestation-<?php echo $me->id ?> td.contact-<?php echo $ce->id ?>').attr('title',"<?php echo $professional->Contact.' - '.$professional?>\n<?php echo $me->Manifestation ?>");
            });
          --></script>
          <div class="EntryTickets">
            <?php $et = new EntryTickets; $et->EntryElement = $entry_element; ?>
            <?php include_partial('event/entry_tickets',array('form' => new EntryTicketsForm($et), 'entry_element' => $entry_element)) ?>
            <?php foreach ( $entry_element->EntryTickets as $et ): ?>
            <?php include_partial('event/entry_tickets',array('form' => new EntryTicketsForm($et), 'entry_element' => $entry_element)) ?>
            <?php endforeach ?>
          </div>
          <div style="clear: both"></div>
          <?php $f = new EntryElementForm($entry_element) ?>
          <?php echo form_tag_for($f,'@entry_element',array('class' => 'EntryElement')) ?>
          <?php echo $f->renderHiddenFields() ?>
            <?php echo $f['second_choice']->getWidget()->getLabel() ?>
            <p>
              <span title="<?php echo sfConfig::get('app_messages_dashed',__('Needed')) ?>"><?php echo $f['second_choice'] ?></span><span title="<?php echo __('Accepted') ?>"><?php echo $f['accepted'] ?></span><!--<input type="submit" name="submit" value="<?php echo __('Save',null,'sf_admin') ?>" />-->
              <input type="hidden" name="<?php echo $f['manifestation_entry_id']->renderName() ?>" value="<?php echo $me->id ?>" />
              <input type="hidden" name="<?php echo $f['contact_entry_id']->renderName() ?>" value="<?php echo $ce->id ?>" />
              <span class="translinked" title="<?php echo __('Related transactions, as in a cancellation case') ?>"><?php
                if ( $entry_element->EntryTickets->count() > 0 && $ce->Transaction && $ce->Transaction->Translinked->count() > 0 )
                foreach ( $ce->Transaction->Translinked as $tr )
                  echo '<a class="cancelling" href="'.cross_app_url_for('tck','ticket/pay?id='.$tr->id).'">#'.$tr->id.'</a> ';
              ?></span>
            </p>
          </form>
          <?php endif ?>
        </td>
        <?php endforeach ?>
      </tr>
      <?php endforeach ?>
      <?php endforeach ?>
    </tbody>
    <thead>
      <?php $j = 0 ?>
      <tr class="impair">
        <td class="title <?php echo ++$j%2 == 0 ? 'pair' : 'impair' ?>"><p class="min-width"><?php echo __('Manifestations').' / '.__('Contacts') ?></p></td>
        <?php $ces = array(); foreach ( $professional->ContactEntries as $ce ) $ces[(string)$ce->Entry->Event.$ce->id] = $ce; ksort($ces); ?>
        <?php foreach ( $ces as $ce ): ?>
        <td class="contact <?php echo ++$j%2 == 0 ? 'pair' : 'impair' ?>">
          <a href="<?php echo cross_app_url_for('event','event/show?id='.$ce->Entry->event_id) ?>"><?php echo $ce->Entry->Event ?></a>
          <a style="float: right" class="fg-button-mini fg-button ui-state-default fg-button-icon-left" href="<?php echo url_for('event/edit?id='.$ce->Entry->event_id) ?>"><span class="ui-icon ui-icon-document"></span><?php echo __('Show','','sf_admin') ?></a>
        </td>
        <?php endforeach ?>
      </tr>
    </thead>
    <tfoot>
      <?php $j = 0 ?>
      <tr class="contact_entry_new <?php echo ++$i%2 == 0 ? 'pair' : 'impair' ?>">
        <td id="contact_entry_new" class="contact <?php echo ++$j%2 == 0 ? 'pair' : 'impair' ?>">
          <?php $f = new ContactEntryByContactForm; $f->setDefault('professional_id', $professional->id); ?>
          <?php echo form_tag_for($f,'@contact_entry_by_contact') ?>
            <?php include_partial('form_contact_new',array('f' => $f)) ?>
          </form>
        </td>
        <?php foreach ( $professional->ContactEntries as $ce ): ?>
        <td class="<?php echo ++$j%2 == 0 ? 'pair' : 'impair' ?> count contact-<?php echo $ce->id ?>">
        </td>
        <?php endforeach ?>
      </tr>
    </tfoot>
  </table>
  
  <script type="text/javascript">
    <?php include_partial('event/form_js', array('entry' => $entry)) ?>
  </script>

</div>
