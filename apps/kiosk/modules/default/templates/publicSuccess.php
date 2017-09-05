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
*    Copyright (c) 2017 Romain SANCHEZ <romain.sanchez AT libre-informatique.fr>
*    Copyright (c) 2017 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php use_helper('I18N', 'CrossAppLink') ?>
<?php use_stylesheet('material.min.css') ?>
<?php use_stylesheet('kiosk/waves.css') ?>
<?php use_stylesheet('kiosk/dialog-polyfill.css') ?>
<?php use_stylesheet('kiosk/mdl-select.css') ?>
<?php use_stylesheet('kiosk/kiosk.css') . time() ?>
<?php use_stylesheet('kiosk/toastr.min.css') ?>
<?php use_stylesheet('/private/kiosk.css') ?>

<?php use_javascript('socket.io.js') ?>
<?php use_javascript('EveConnector/web/js/eve-connector.js') ?>
<?php use_javascript('EveConnector/web/js/concert-protocol.js') ?>
<?php use_javascript('kiosk/jquery.idle.min.js') ?>
<?php use_javascript('tck-devices.js') ?>
<?php use_javascript('kiosk/keypad.js') ?>
<?php use_javascript('kiosk/dialog-polyfill.js') ?>
<?php use_javascript('/sfAdminThemejRollerPlugin/js/jquery-ui.custom.min.js') ?>
<?php use_javascript('kiosk/toastr.min.js') ?>
<?php use_javascript('kiosk/waves.js') ?>
<?php use_javascript('handlebars/handlebars-v4.0.5.js') ?>
<?php use_javascript('material/material.min.js') ?>
<?php use_javascript('kiosk/mdl-select.js') ?>
<?php use_javascript('kiosk/kiosk.js?' . time()) ?>

<div id="app" class="app-layout mdl-layout mdl-js-layout mdl-layout--fixed-header">
	<header id="app-header" class="mdl-layout__header mdl-shadow--4dp">
		<div class="mdl-layout__header-row">
			<span id="logo" class="mdl-layout-title"><img src="<?php echo sfConfig::get('app_ui_logo', sfConfig::get('project_about_logo')) ?>" alt="logo"/></span>
			<div class="mdl-layout-spacer"></div>
			<!-- I18N LINKS -->
			<i class="material-icons culture">language</i>
			<?php foreach(sfConfig::get('project_internals_cultures', array('fr' => 'Français')) as $key => $culture): ?>
	            <a href="<?php echo cross_app_url_for('kiosk', 'default/culture') ?>/lang/<?php echo $key ?>" class="culture" data-culture="<?php echo $key ?>">
				  <?php echo $culture ?>
	            </a>
	        <?php endforeach ?>
	        <div class="mdl-layout-spacer"></div>
			<!-- RESET -->
	        <button id="reset-btn" class="mdl-button mdl-js-button mdl-button--icon">
            	<i class="material-icons">replay</i>
          	</button>
          	<div id="admin-trigger"></div>
	        <!-- INFO -->
	        <div id="info-panel" class="mdl-card__supporting-text mdl-shadow--6dp">
				<p>
					<a href="http://www.e-venement.org/">e-venement</a>
					<span>la billetterie informatique, libre et open source - © 2006-2016</span>
					<a href="http://www.libre-informatique.fr/">Libre Informatique</a>
					<br>
					<span>Publié sous licence</span>
					<a href="http://www.gnu.org/licenses/gpl.html">GNU/GPL</a>
					<span>- Renforcé par</span>
					<a href="http://www.symfony-project.org/">Symfony</a>
					,
					<a href="http://www.php.net/">PHP</a>
					,
					<a href="http://www.postgresql.org/">PostgreSQL</a>
				</p>
			</div>
            <button id="info-btn" class="mdl-button mdl-js-button mdl-button--icon">
            	<i class="material-icons">info_outline</i>
          	</button>
		</div>
	</header>

	<main id="main" class="mdl-layout__content">
		<div id="content">
			<!-- breadcrumbs -->
			<div id="breadcrumbs-wrapper" class="">
				<ul id="breadcrumbs" class="">
					<li id="home-breadcrumb" class="breadcrumb mdl-shadow--2dp" data-target="product-menu">
						<a href="#"><?php echo kioskConfiguration::getText('breadcrumb_home', 'Home') ?></a>
					</li>
					<li id="products-breadcrumb" class="breadcrumb mdl-shadow--2dp" data-target="products">
						<a href="#"><?php echo kioskConfiguration::getText('breadcrumb_products', 'Products') ?></a>
					</li>
					<li id="details-breadcrumb" class="breadcrumb mdl-shadow--2dp" data-target="product-details">
						<a href="#"></a>
					</li>
				</ul>
			</div>
			<!-- loader -->
			<div class="mdl-spinner mdl-js-spinner is-active" id="spinner"></div>
			<!-- back fab -->
			<button id="back-fab" class="mdl-button mdl-js-button mdl-button--fab waves-effect">
	  			<i class="material-icons light">keyboard_backspace</i>
			</button>
			<!-- access fab -->
			<button id="access-fab" class="mdl-button mdl-js-button mdl-button--fab waves-effect">
	  			<i class="material-icons light">accessible</i>
			</button>
			<!-- Snackbar -->
			<div id="snackbar" class="mdl-js-snackbar mdl-snackbar">
	  			<div class="mdl-snackbar__text"></div>
	  			<button class="mdl-snackbar__action" type="button"></button>
			</div>
			<!-- menu panel -->
			<div id="product-menu" class="panel">
				<ul id="product-menu-items" class="flex-list"></ul>
			</div>
			<!-- product list -->
			<div id="products" class="panel">
				<ul id="product-list" class="flex-list"></ul>
			</div>
			<!-- product details panel -->
			<div id="details" class="panel">
				<div id="product-details-card" class="mdl-card mdl-shadow--2dp"></div>
				<ul id="declinations" class="flex-list"></ul>
				<ul id="prices" class="flex-list"></ul>
			</div>
		</div>
		<!-- cart panel -->
		<div id="cart" class="">
			<!-- lines -->
			<ul id="cart-lines"></ul>
			<!-- total -->
			<div id="cart-total">
				<span id="cart-total-label"><?php echo kioskConfiguration::getText('cart_total', 'Total') ?></span>
				<span id="cart-total-value"></span>
			</div>
			<!-- confirm button -->
			<div id="cart-confirm" class="">
				<button id="confirm-btn" class="mdl-button mdl-js-button mdl-button--raised waves-effect">
					<span id="confirm-btn-wrapper">
						<i class="material-icons light">check</i>
						<?php echo kioskConfiguration::getText('cart_validate', 'Checkout') ?>
					</span>
				</button>
			</div>
		</div>
	</main>
</div>

<!-- LOCATION DIALOG -->
<dialog id="location" class="mdl-dialog">
  <form id="location-form" method="dialog" autocomplete="off">
    <p class="mdl-dialog__title"><?php echo kioskConfiguration::getText('location_title', 'Please enter your postcode or country') ?></p>
    <div class="mdl-dialog__content">
    	<div class="mdl-cell mdl-cell--12-col">
			<div class="mdl-selectfield mdl-js-selectfield mdl-selectfield--floating-label" id="country-field">
			  <select id="countries" name="myselect" class="mdl-selectfield__select">
			  </select>
			  <label class="mdl-selectfield__label" for="countries"><?php echo kioskConfiguration::getText('country', 'Country') ?></label>
			</div>
	    </div>
	    <div class="mdl-cell mdl-cell--12-col">
		    <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label" id="postcode-field">
		    	<input type="text" id="postcode" name="postcode" class="mdl-textfield__input" placeholder="">
		    	<label class="mdl-textfield__label" for="postcode"><?php echo kioskConfiguration::getText('postcode', 'Post code') ?></label>
		    	<span class="mdl-textfield__error"><?php echo kioskConfiguration::getText('postcode_validation', 'Post code must be all numbers') ?></span>
		    </div>
		</div>
	</div>
	<div class="mdl-dialog__actions">
    	<button id="location-submit" class="mdl-button mdl-button--raised mdl-button--colored" type="submit">
    		<?php echo kioskConfiguration::getText('location_close', 'Continue to payment') ?>
    		<i class="material-icons">arrow_forward</i>
    	</button>
    </div>
  </form>
  <div id="postcode-pad" class="keypad mdl-grid"></div>
</dialog>

<!-- STATUS DIALOG -->
<dialog id="status" class="mdl-dialog">
  <form id="status-form" method="dialog">
	  <h4 id="status-title" class="mdl-dialog__title"></h4>
	  <div id="status-content" class="mdl-dialog__content">
  	  	<p id="status-details"></p>
  	  	<img id="status-ept" src="/images/kiosk/ept.png" alt="Payment terminal">
	  </div>
	  <div id="status-actions" class="mdl-dialog__actions">
  		<button type="submit" class="mdl-button" value="true"><?php echo kioskConfiguration::getText('retry', 'Retry') ?></button>
  	    <button type="submit" class="mdl-button close" value="false"><?php echo kioskConfiguration::getText('cancel', 'Cancel') ?></button>
	  </div>
  </form>

</dialog>

<!-- ADMIN DIALOG -->
<dialog id="admin" class="mdl-dialog">
	<div class="mdl-dialog__content">
		<p Administration></p>
		<button id="execute-tasks" class="mdl-button"><?php echo kioskConfiguration::getText('execute_tasks', 'Execute task list') ?></button>
	</div>
</dialog>

<!-- ADMIN PIN DIALOG -->
<dialog id="pin" class="mdl-dialog">
	<div id="pin-pad" class="keypad mdl-grid"></div>
	<div class="mdl-dialog__content">
		<div class="mdl-cell mdl-cell--12-col">
		    <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label" id="pin-field">
		    	<p id="pin-error"><?php echo kioskConfiguration::getText('pin_failure', 'Wrong pin') ?></p>
		    	<input type="password" id="pin-input" name="pin-input" class="mdl-textfield__input" placeholder="">
		    	<label class="mdl-textfield__label" for="pin-input"><?php echo kioskConfiguration::getText('pin', 'Pin') ?></label>
		    </div>
		</div>
	</div>
	<div id="status-actions" class="mdl-dialog__actions">
  		<button type="submit" id="pin-validate" class="mdl-button" value="true">Ok</button>
  	    <button type="submit" class="mdl-button close" value="false"><?php echo kioskConfiguration::getText('cancel', 'Cancel') ?></button>
  	</div>	
</dialog>

<!-- JS DATA -->
<div class="js-data" id="kiosk-urls"
  data-get-new-transaction="<?php echo cross_app_url_for('tck', 'transaction/newJson') ?>"
  data-get-csrf="<?php echo cross_app_url_for('tck', 'transaction/getCSRFToken') ?>"
  data-get-countries="<?php echo cross_app_url_for('kiosk', 'default/getCountries') ?>"
  data-complete-transaction="<?php echo cross_app_url_for('tck', 'transaction/complete?id=-666') ?>"
  data-get-manifestations="<?php echo cross_app_url_for('tck', 'transaction/getManifestations?simplified=1') ?>"
  data-get-store="<?php echo cross_app_url_for('tck', 'transaction/getStore?simplified=1') ?>"
  data-get-museum="<?php echo cross_app_url_for('tck', 'transaction/getPeriods?simplified=1') ?>"
  data-print-tickets="<?php echo cross_app_url_for('tck', 'ticket/print?id=-666') ?>"
  data-log-print-failure="<?php echo cross_app_url_for('tck', 'transaction/directPrintLog?id=-666') ?>"
  data-save-payment-record="<?php echo cross_app_url_for('kiosk', 'default/savePaymentRecord') ?>"
  data-get-payment-record="<?php echo cross_app_url_for('kiosk', 'default/getPaymentRecord') ?>"
  data-get-task-list="<?php echo cross_app_url_for('kiosk', 'admin/getJsonTasks') ?>"
  data-update-task="<?php echo cross_app_url_for('kiosk', 'admin/done') ?>"
></div>

<div class="js-data" id="kiosk-devices" data-devices="<?php echo htmlspecialchars(json_encode(sfConfig::get('app_io_devices',
	[
		'ept' => [
			'type' => 'serial',
			'params' => [
				'baudrate' => 1200,
				'comName' => '/dev/ttyACM0',
				'databits' => 7,
				'parity' => 'even',
				'pnpId' => 'usb-079b_0028-if00'
			]
		],
		'ticketPrinter' => [
			'type' => 'usb',
			'params' => [
				'pid' => '1',
				'vid' => '1305'
			]
		],
		'invoicePrinter' => [
			'type' => 'usb',
			'params' => [
				'pid' => '14864',
				'vid' => '7306'
			]
		]
	]
))) ?>"></div>

<div class="js-data" id="kiosk-config"
  data-culture="<?php echo sfConfig::get('app_culture', 'FR') ?>"
  data-user-culture="<?php echo sfContext::getInstance()->getUser()->getCulture() ?>"
  data-idle-time="<?php echo sfConfig::get('app_idle_time', false); ?>"
  data-ui-labels="<?php echo htmlspecialchars(json_encode(sfConfig::get('app_ui_labels'))) ?>"
  data-show-location-prompt="<?php echo sfConfig::get('app_location_prompt') ?>"
  data-payment-method="<?php echo sfConfig::get('app_payment_method_id') ?>"
 ></div>

<!-- JS I18N -->
<div class="js-i18n" id="kiosk-strings" data-strings="<?php echo htmlspecialchars(json_encode(kioskConfiguration::getTexts())) ?>"></div>

<!-- HANDLEBARS TEMPLATES -->
	<!-- menu item -->
<script id="menu-item-template" type="text/x-handlebars-template" data-template-type="menuItem">
	<li class="menu-item" data-type="{{ type }}">
		<div id="" class="menu-item-card mdl-card mdl-shadow--4dp waves-effect">
  			<div class="mdl-card__title mdl-card--expand" style="background-color: {{ color }};">
    			{{ name }}
    		</div>
  		</div>
  	</li>
</script>

	<!-- manif card -->
<script id="manif-card-template" type="text/x-handlebars-template"  data-template-type="productCard" data-product-type="manifestations">
	<li class="product" data-type="{{ type }}" data-id="{{ id }}"> 
		<div class="manif-card mdl-card mdl-shadow--2dp waves-effect" id="{{ id }}">
			<div class="mdl-card__title manif-title" style="{{ background }};">
				<p class="mdl-card__title-text manif-name">{{ name }}</p>
			{{#unless museum}}
	    		<p class="mdl-card__title-text manif-happens_at"><i class="material-icons" role="presentation">access_time</i>{{ start }}</p>
	  		{{/unless}}
				<p class="mdl-card__title-text manif-location"><i class="material-icons" role="presentation">location_on</i>{{ location }}</p>
			</div>
			<div class="mdl-card__supporting-text manif-description">
				{{{ description }}}
			</div>
		</div>
	</li>
</script>

<!-- store card -->
<script id="store-card-template" type="text/x-handlebars-template"  data-template-type="productCard" data-product-type="store">
	<li class="product" data-type="{{ type }}" data-id="{{ id }}"> 
		<div class="manif-card mdl-card mdl-shadow--4dp waves-effect" id="{{ id }}">
			<div class="mdl-card__title manif-title" style="{{ background }};">
				<p class="mdl-card__title-text manif-name">{{ name }}</p>
			</div>
			<div class="mdl-card__supporting-text manif-description">
				{{{ description }}}
			</div>
		</div>
	</li>
</script>

	<!-- product details -->
<script id="product-details-template" type="text/x-handlebars-template" data-template-type="productDetails">
	<div id="product-background" style="{{ background }};">
		<div class="mdl-card__title"></div>
		<div id="details-content" class="mdl-card__supporting-text">
			<div id="product-details">
	    		<div id="details-name">{{ name }}</div>

	    		<div id="details-description">{{{ description }}}</div>
	    		{{#unless store}}
		    		<div id="details-time">
			    		<span>
			    			<i class="material-icons" role="presentation">access_time</i>
			    			<span class="">{{ start }} - {{ end }}</span>
			    		</span>
			    		<span>
			    			<i class="material-icons" role="presentation">location_on</i>
			    			<span>{{ location }}</span>
			    		</span>
			    	</div>
			    {{/unless}}
	    	</div>
		</div>
	</div>
</script>

	<!-- declination card -->
<script id="declination-card-template" type="text/x-handlebars-template" data-template-type="declinationCard">
	<li class="declination">
		<div id="{{ id }}" class="declination-card mdl-card mdl-shadow--2dp waves-effect">
			{{#if store}}
				<div class="mdl-card__title mdl-card--expand" style="background-color: {{ color }};">
					<p>{{ name }}</p>
					<p>{{ value }}</p>
				</div>
			{{else}}
				<div class="mdl-card__title mdl-card--expand" style="background-color: {{ color }};">
					{{ name }}
				</div>
			{{/if}}
		</div>
	</li>
</script>

	<!-- price card -->
<script id="price-card-template" type="text/x-handlebars-template" data-template-type="priceCard">
	<li class="price">
		<div id="{{ id }}" class="price-card-square mdl-card mdl-shadow--2dp waves-effect">
			<div class="mdl-card__title mdl-card--expand price-content" style="background-color: {{ color }};">	
				<p>{{ name }}</p>
				<p>{{ value }}</p>
			</div>
		</div>
	</li>
</script>

	<!-- cart line -->
<script id="cart-line-template" type="text/x-handlebars-template" data-template-type="cartLine">
	<li class="cart-line" id="{{ id }}" style="border-right: 5px solid {{ color }};">
		<div class="line-controls">
			{{#if product.isNecessaryTo}}
			{{else}}
				<button class="add-item line-control mdl-button mdl-js-button mdl-button--fab">
		  			<i class="material-icons light">add</i>
				</button>
				<button class="remove-item line-control mdl-button mdl-js-button mdl-button--fab">
		  			<i class="material-icons light">remove</i>
				</button>
			{{/if}}
	    </div>
	    <div class="line-details">
			<p class="line-main">
				<span class="line-qty">{{ qty }}</span>
				<span class="line-multiplier"> x </span>
				<span class="line-name">{{ name }}</span>
			</p>
			{{#if product.isNecessaryTo}}
				<p class="linked-product"><?php echo kioskConfiguration::getText('linked_product') ?>  {{ product.isNecessaryTo }}</p>
			{{/if}}
			<p class="line-second">
				<span class="line-price">{{ price.description }} ({{ value }})</span>
			</p>
		</div>
		<div class="line-value">
			<span class="line-total">{{ total }}</span>
	    </div>
  	</li>
</script>