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
<?php use_helper('CrossAppLink') ?>

<div class="sf_admin_form">
    <div class="sf_admin_actions_block ui-widget">
      <?php include_partial('event/form_actions', array('event' => $event, 'form' => $form, 'configuration' => $configuration, 'helper' => $helper)) ?>
    </div>
  
  <p>&nbsp;</p>
  <p>&nbsp;</p>
  
  <div id="copy-paste"><?php echo __('Drag and drop elsewhere to paste selected content') ?></div>
  
  <table class="grp-entry">
    <tbody>
      <?php $i = 1 ?>
      <?php foreach ( $sf_data->getRaw('entry')->ContactEntries as $ce ): ?>
      <tr class="contact-<?php echo $ce->id ?> <?php echo ++$i%2 == 0 ? 'pair' : 'impair' ?> <?php if ( !is_null($ce->transaction_id) ) echo 'transposed' ?> <?php if ( $ce->confirmed ) echo 'confirmed' ?>">
        <?php $j = 0 ?>
        <td class="contact <?php echo ++$j%2 == 0 ? 'pair' : 'impair' ?>"><?php $f = new ContactEntryForm($ce) ?>
          <?php echo form_tag_for($f, '@contact_entry') ?>
          <?php include_partial('form_contact',array('f' => $f, 'ce' => $ce)) ?>
          </form>
        </td>
        <?php foreach ( $entry->ManifestationEntries as $me ): ?>
        <?php
          $entry_element = Doctrine::getTable('EntryElement')->fetchOneByContactManifestation($ce->id, $me->id);
          if ( !$entry_element )
          {
            $entry_element = new EntryElement;
            $entry_element->contact_entry_id = $ce->id;
            $entry_element->manifestation_entry_id = $me->id;
            $entry_element->save();
          }
        ?>
        <td class="manifestation-<?php echo $me->id ?> <?php echo ++$j%2 == 0 ? 'pair' : 'impair' ?> <?php echo $entry_element->second_choice ? 'second_choice' : '' ?> <?php echo $entry_element->accepted ? 'accepted' : '' ?>">
          <script type="text/javascript"><!--
            $(document).ready(function(){
              $('tr.contact-<?php echo $ce->id ?> td.manifestation-<?php echo $me->id ?>').attr('title',"<?php echo $ce->Professional->Contact.' - '.$ce->Professional?>\n<?php echo $me->Manifestation ?>");
            });
          --></script>
          <div class="EntryTickets">
            <?php $et = new EntryTickets; $et->EntryElement = $entry_element; ?>
            <?php include_partial('entry_tickets',array('form' => new EntryTicketsForm($et), 'entry_element' => $entry_element)) ?>
            <?php foreach ( $entry_element->EntryTickets as $et ): ?>
            <?php include_partial('entry_tickets',array('form' => new EntryTicketsForm($et), 'entry_element' => $entry_element)) ?>
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
              <?php if ( $entry_element->EntryTickets->count() > 0 && $ce->Transaction && $ce->Transaction->Translinked->count() > 0 ): ?>
              <span class="translinked" title="<?php echo __('Related transactions, as in a cancellation case') ?>">
                <?php foreach ( $ce->Transaction->Translinked as $tr ): ?>
                  <a class="cancelling" href="<?php echo cross_app_url_for('tck','ticket/pay?id='.$tr->id) ?>">#<?php echo $tr->id ?></a>
                <?php endforeach ?>
              </span>
              <?php endif ?>
            </p>
          </form>
        </td>
        <?php endforeach ?>
        <td class="<?php echo ++$j%2 == 0 ? 'pair' : 'impair' ?> ticketting"<?php if ( $ce->transaction_id ): ?> title="<?php echo __('Transaction #%%t%%',array('%%t%%' => $ce->transaction_id)); ?>"<?php endif ?>>
          <p class="count"><span class="total">0</span></p>
          <p class="transpose" title="<?php echo __('Transpose to ticketting') ?>"><a href="<?php echo url_for('contact_entry/transpose?id='.$ce->id) ?>">&gt;&gt;</a></p>
          <?php if ( $ce->transaction_id ): ?><p class="untranspose">
            <span class="translinked" title="<?php echo __('Related transactions, as in a cancellation case') ?>"><?php
              if ( $ce->Transaction->Translinked->count() > 0 )
                foreach ( $ce->Transaction->Translinked as $tr )
                  echo '<a class="cancelling" href="'.cross_app_url_for('tck','ticket/pay?id='.$tr->id).'">#'.$tr->id.'</a> ';
            ?></span>
            <a href="<?php echo url_for('contact_entry/untranspose?id='.$ce->id) ?>">&lt;&lt;</a>
          </p><?php endif ?>
        </td>
      </tr>
      <?php endforeach ?>
    </tbody>
    <thead>
      <?php $j = 0 ?>
      <tr class="impair">
        <td class="title <?php echo ++$j%2 == 0 ? 'pair' : 'impair' ?>"><p class="min-width"><?php echo __('Contacts') ?> / <?php echo __('Event') ?></p></td>
        <?php $manifs = array() ?>
        <?php foreach ( $entry->ManifestationEntries as $me ): ?>
        <?php $manifs[] = $me->Manifestation->id ?>
        <td class="manifestation manifestation-<?php echo $me->id ?> <?php echo ++$j%2 == 0 ? 'pair' : 'impair' ?>" title="<?php echo $me->Manifestation->Location ?>">
          <?php include_partial('form_manifestation',array('me' => $me, )) ?>
        </td>
        <?php endforeach ?>
        <td id="manifestation_entry_new" class="<?php echo ++$j%2 == 0 ? 'pair' : 'impair' ?>">
          <?php $f = new ManifestationEntryForm ?>
          <?php echo form_tag_for($f,'@manifestation_entry') ?>
            <?php echo $f->renderHiddenFields(); ?>
            <p><?php $f['manifestation_id']->getWidget()->setOption('query',$f['manifestation_id']->getWidget()->getOption('query')->andWhere('m.event_id = ?',$event->id)->andWhereNotIn('m.id',$manifs)); echo $f['manifestation_id']; ?></p>
            <p>
              <input type="submit" name="submit" value="<?php echo __('Save',array(),'sf_admin') ?>" />
            </p>
          </form>
        </td>
      </tr>
    </thead>
    <tfoot>
      <?php $j = 0 ?>
      <tr class="contact_entry_new <?php echo ++$i%2 == 0 ? 'pair' : 'impair' ?>">
        <td id="contact_entry_new" class="contact <?php echo ++$j%2 == 0 ? 'pair' : 'impair' ?>">
          <?php $f = new ContactEntryForm ?>
          <?php echo form_tag_for($f,'@contact_entry') ?>
            <?php include_partial('form_contact_new',array('f' => $f)) ?>
          </form>
        </td>
        <?php foreach ( $entry->ManifestationEntries as $me ): ?>
        <td class="<?php echo ++$j%2 == 0 ? 'pair' : 'impair' ?> count manifestation-<?php echo $me->id ?>">
        </td>
        <?php endforeach ?>
        <td class="<?php echo ++$j%2 == 0 ? 'pair' : 'impair' ?> total"></td>
      </tr>
    </tfoot>
  </table>
  
  <script type="text/javascript">
    <?php include_partial('form_js', array('entry' => $entry)) ?>
  </script>

</div>
