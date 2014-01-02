<?php use_helper('Number') ?>
          <h4></h4>
          <div class="gauge-data"></div>
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
                <td class="tep nb monney" title="<?php echo __('PET') ?>"></td>
                <td class="vat nb monney" title="<?php echo __('VAT') ?>"></td>
                <td class="pit nb monney" title="<?php echo __('Total') ?>"></td>
              </tr>
            </tbody>
            <tfoot>
              <tr class="total">
                <td class="qty nb">
                  <a class="fg-button-mini fg-button ui-state-default fg-button-icon-left"><span class="ui-icon ui-icon-minus"></span></a>
                  <span class="qty"></span>
                  <a class="fg-button-mini fg-button ui-state-default fg-button-icon-left"><span class="ui-icon ui-icon-plus"></span></a>
                </td>
                <td class="price_name">&nbsp;</td>
                <td class="data"></td>
                <td class="tep nb monney" title="<?php echo __('PET') ?>"></td>
                <td class="vat nb monney" title="<?php echo __('VAT') ?>"></td>
                <td class="pit nb monney" title="<?php echo __('Total') ?>"></td>
              </tr>
            </tfoot>
          </table>
          <script class="infos" type="text/json"><![CDATA[
          ]]></script>
