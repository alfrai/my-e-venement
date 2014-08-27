<?php use_helper('Number') ?>
          <h4></h4>
          <div class="data"></div>
          <table class="declinations">
            <tbody>
              <tr class="declination" data-price-id="">
                <td class="qty nb">
                  <a class="fg-button-mini fg-button ui-state-default fg-button-icon-left"><span class="ui-icon ui-icon-minus"></span></a>
                  <input class="qty" type="text" name="qty" value="" pattern="\d*" maxlength="3" />
                  <a class="fg-button-mini fg-button ui-state-default fg-button-icon-left"><span class="ui-icon ui-icon-plus"></span></a>
                </td>
                <td class="ticket-data">
                  <span class="ids"></span>
                </td>
                <td class="price_name"></td>
                <td class="tep nb money" title="<?php echo __('PET') ?>"></td>
                <td class="vat nb money" title="<?php echo __('VAT') ?>"></td>
                <td class="pit nb money" title="<?php echo __('Total') ?>"></td>
                <td class="extra-taxes nb money" title="<?php echo __('Extra taxes') ?>"></td>
                <td class="item-details" title="<?php echo __('Registered tickets') ?>">
                  <a href="<?php echo url_for('transaction/registered?id='.$transaction->id) ?>" target="_blank"><span class="ui-icon ui-icon-person"></span></a>
                </td>
              </tr>
            </tbody>
            <tfoot>
              <tr class="total">
                <td class="qty nb">
                  <a class="fg-button-mini fg-button ui-state-default fg-button-icon-left"><span class="ui-icon ui-icon-minus"></span></a>
                  <span class="qty"></span>
                  <a class="fg-button-mini fg-button ui-state-default fg-button-icon-left"><span class="ui-icon ui-icon-plus"></span></a>
                </td>
                <td class="ticket-data"></td>
                <td class="price_name">&nbsp;</td>
                <td class="tep nb money" title="<?php echo __('PET') ?>"></td>
                <td class="vat nb money" title="<?php echo __('VAT') ?>"></td>
                <td class="pit nb money" title="<?php echo __('Total') ?>"></td>
                <td class="extra-taxes nb money" title="<?php echo __('Extra taxes') ?>"></td>
                <td class="item-details"></td>
              </tr>
            </tfoot>
          </table>
