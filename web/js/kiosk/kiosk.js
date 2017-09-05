/**********************************************************************************
*
*       This file is part of e-venement.
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
$(document).ready(function() {
    //Initialize app
    LI.kiosk.init();
    LI.kiosk.admin.executePinTask();
});

if ( LI === undefined )
  var LI = {};

LI.kiosk = {
    debug: window.location.hash == '#debug',
    connector: new EveConnector('https://localhost:8164'),
    devices: {},
    templates: {},
    dialogs: {},
    transaction: {},
    products: {},
    urls: {},
    currentPanel: {},
    config: {},
    countries: {},
    ticketsIntegrated: false,
    init: function() {
        LI.kiosk.utils.showLoader();
        LI.kiosk.config = $('#kiosk-config').data();
        LI.kiosk.urls = $('#kiosk-urls').data();
        LI.kiosk.strings = $('#kiosk-strings').data('strings');
        LI.kiosk.devices = $('#kiosk-devices').data('devices');
        LI.kiosk.initPlugins();
      //  LI.kiosk.checkDevices();
        LI.kiosk.addListeners();

        //Initialize backend
        if(LI.kiosk.config.admin) {
            LI.kiosk.admin.init();
        }

        //hide current culture from menu
        $('.culture[data-culture="' + LI.kiosk.config.userCulture + '"]').hide();

        // retrieve data then display menu
        LI.kiosk.utils.whenAlways(
            LI.kiosk.getCSRF(),
            LI.kiosk.getTransaction(),
            LI.kiosk.getManifestations(),
            LI.kiosk.getMuseum(),
            LI.kiosk.getStore()
         )
         .done(function() {
            LI.kiosk.menu();
            LI.kiosk.afterInit();
         })
        ;
    },
    afterInit: function() {
        LI.kiosk.menu();

        //handle idle user
        // if(LI.kiosk.config.idleTime) {
        //     $(this).idle({
        //         onIdle: function() {
        //             $('.culture[data-culture="fr"]')
        //                 .trigger('click')
        //                 // get native element as triggering click
        //                 // doesn't work on jquery objects that  were
        //                 // not previously bound with .click or .on
        //                 .get(0)
        //                 .click()
        //             ;
        //         },
        //         idle: LI.kiosk.config.idleTime
        //     });
        // }

        //Retrieve country list for location prompt
        if(LI.kiosk.config.showLocationPrompt) {
            LI.kiosk.getCountries();
        }
    },
    reset: function() {
        $(document).off();
        $('body').css('pointer-events', 'none');
        LI.kiosk.utils.hideLoader();
    },
    initPlugins: function() {
        Waves.attach('.waves-effect');
        Waves.init();

        LI.kiosk.cacheTemplates();
        
        toastr.options = {
            positionClass: 'toast-bottom-full-width',
            closeButton: true,
            timeOut: 0
        };

        $('.mdl-dialog').each(function(key, elem) {
            var dialog = $(elem).get(0);

            dialogPolyfill.registerDialog(dialog);

            LI.kiosk.dialogs[$(elem).prop('id')] = dialog;
        });
    },
    addListeners: function() {
        //UI transitions
        $(document)
            .on('menu:mount', function() {
                LI.kiosk.mountProductMenu();
            })
            .on('menu:unmount', function(e) {
                LI.kiosk.menuToList(e.productType);
                $('.culture').hide();
            })
            .on('product-list:mount', function(e) {
                LI.kiosk.mountProductList(e.productType, e.mode);
            })
            .on('product-list:unmount', function(e) {
                if(e.mode == 'back') {
                    LI.kiosk.listToMenu();
                }else{
                    LI.kiosk.listToProduct(e.product);
                }
            })
            .on('product-details:mount', function(e) {
                LI.kiosk.mountProductDetails(e.product);
            })
            .on('product-details:unmount', function(e) {
                LI.kiosk.productToList(e.productType);
            })
            .on('declinations:mount', function(e) {
                LI.kiosk.mountDeclinations(e.product);
            })
            .on('declinations:unmount', function(e) {
                LI.kiosk.declinationsToPrices(e.product, e.declination);
            })
            .on('prices:mount', function(e) {
                LI.kiosk.mountPrices(e.product, e.declination, e.mode);
            })
            .on('prices:unmount', function(e) {
                if(e.mode == 'direct') {
                    LI.kiosk.pricesToProducts(e.product.type, 'back');
                } else {
                    LI.kiosk.pricesToDeclinations(e.product);
                }
            })
        ;

        //accessibility mode
        $('#access-fab').click(function() {
            $('#access-fab, #app, #back-fab, .panel, #product-details-card, #cart').toggleClass('a11y');
        });

        $('#reset-btn, #logo').click(function() {
            LI.kiosk.utils.showLoader();
            location.reload();
        });

        //info button
        $('#info-btn').click(function() {
            $('#info-panel').toggle(500);

            setTimeout(function() {
                $('#info-panel').hide(500);
            }, 10000);
        });

        //breadcrumbs clicks
        $('.breadcrumb').not(':last-child').click(function() {
            var id = $(this).prop('id');
            var target = $(this).data('target');

            $('.breadcrumb')
                .not($(this))
                .not('#home-breadcrumb')
                .hide()
            ;

            LI.kiosk.utils.switchPanels('right', function() {
                $('#' + target).effect('slide', {
                    direction: 'left',
                    mode: 'show',
                    duration: 500
                });
            });
        });

        //product clicks
        $('#product-list').on('click', '.product', function(event) {
            var productCard = $(event.currentTarget);
            var type = productCard.data('type');
            var id = productCard.data('id');

            $(document).trigger({
                type: 'product-list:unmount',
                mode: 'forth',
                product: LI.kiosk.products[type][id]
            });
        });

        //cart validation clicks
        $('#confirm-btn').click(function() {
            if(LI.kiosk.config.showLocationPrompt) {
                LI.kiosk.utils.showLocationPrompt();
            } else {
                LI.kiosk.checkout();
            }
        });
    },
    checkDevices: function() {
        var ept = false;

        var query = {
            type: LI.kiosk.devices.ept.type,
            params: [{pnpId: LI.kiosk.devices.ept.params.pnpId}]
        };

        if(!LI.kiosk.connector.isConnected()) {
            LI.kiosk.utils.showHardwarePrompt('connector');
        }

        LI.kiosk.connector.areDevicesAvailable(query).then(
            function(response) {
                if (!response.params.length) {
                    LI.kiosk.utils.showHardwarePrompt('ept');
                }
            },
            function(error) { 
                LI.kiosk.utils.showHardwarePrompt('ept');
                console.error("areDevicesAvailable() error:", error); 
            }
        ).then(function() {
            query = {
                type: LI.kiosk.devices.ticketPrinter.type, 
                params: [
                    {
                        vid: LI.kiosk.devices.ticketPrinter.params.vid,
                        pid: LI.kiosk.devices.ticketPrinter.params.pid
                    },
                    {
                        vid: LI.kiosk.devices.invoicePrinter.params.vid,
                        pid: LI.kiosk.devices.invoicePrinter.params.pid
                    }
                ] 
            };

            LI.kiosk.connector.areDevicesAvailable(query).then(
                function(response) {
                    if (!response.params.length) {
                        LI.kiosk.utils.showHardwarePrompt('ticketPrinter');
                    }
                },
                function(error) { 
                    LI.kiosk.utils.showHardwarePrompt('ticketPrinter');
                    console.error("areDevicesAvailable() error:", error); 
                }
            );
        });
    },
    menu: function() {
        //check if product type menu is needed
        var lists = {};

        $.each(LI.kiosk.products, function(key, productList) {
            var listLength = Object.keys(productList).length;

            if( listLength > 0)
                lists[key] = listLength;
        });

        if(Object.keys(lists).length > 1) {
            $(document).trigger('menu:mount');
        }else {
            $(document).trigger({
                type: 'product-list:mount',
                productType: Object.keys(lists)[0]
            });
        }

        LI.kiosk.utils.hideLoader();
    },
    getTransaction: function() {
        return $.get(LI.kiosk.urls.getNewTransaction, function(data) {
            LI.kiosk.transaction.id = data;
        });
    },
    getCSRF: function() {
        return $.get(LI.kiosk.urls.getCsrf, function(token) {
            LI.kiosk.CSRF = token;
        });
    },
    getCountries: function() {
        return $.get(LI.kiosk.urls.getCountries + '?culture=' + LI.kiosk.config.userCulture, function(data) {
            LI.kiosk.countries = JSON.parse(data);
        });
    },
    getManifestations: function() {
        return $.ajax({
            url: LI.kiosk.urls.getManifestations,
            type: 'GET',
            success: LI.kiosk.cacheManifestations,
            error: LI.kiosk.utils.error
        });
    },
    getStore: function() {
        return $.ajax({
            url: LI.kiosk.urls.getStore,
            type: 'GET',
            success: LI.kiosk.cacheStore,
            error: LI.kiosk.utils.error
        });
    },
    getMuseum: function() {
        return $.ajax({
            url: LI.kiosk.urls.getMuseum,
            type: 'GET',
            success: LI.kiosk.cacheMuseum,
            error: LI.kiosk.utils.error
        });
    },
    mountProductMenu: function() {
        LI.kiosk.utils.resetBackFab();

        if( !$('#product-menu-items').children().length > 0 ) {
            var template = Handlebars.compile(LI.kiosk.templates.menuItem);

            $.each(LI.kiosk.products, function(type, length){   
                var item = {
                    name: LI.kiosk.strings['menu_' + type],
                    type: type
                };

                $('#product-menu-items').append(template(item));
            });

            $('.menu-item').click(function() {
                $(document).trigger({
                    type: 'menu:unmount',
                    productType: $(this).data('type')
                });
            });
        }

        $('#product-menu').effect('slide', {
            direction: 'left',
            mode: 'show',
            duration: '300'
        });
    },
    menuToList: function(productType) {
        LI.kiosk.utils.switchPanels('left', function() {
            $(document).trigger({
                type: 'product-list:mount',
                productType: productType,
                mode: 'forth'
            });
        });
    },
    mountProductList: function(type, mode) {
        var direction = mode == 'back' ? 'left': 'right';

        LI.kiosk.utils.resetBackFab();
        
        $('#back-fab')
            .click(function() {
                $(document).trigger({
                    type: 'product-list:unmount',
                    mode: 'back'
                });
            })
            .show()
        ;

        $('#products-breadcrumb a')
            .html($('[data-source="' + type + '"]').data('target'))
            .parent()
            .css('display', 'inline-block')
        ;

        LI.kiosk.insertProducts(type); 

        $('#products').effect('slide', {
            direction: direction, 
            mode: 'show',
            duration: '300'
        });
    },
    listToProduct: function(product) {
        LI.kiosk.utils.switchPanels('left', function() {
            $(document).trigger({
                type: 'product-details:mount',
                product: product
            });
        });
    },
    listToMenu: function() {
        $('#products-breadcrumb').hide();

        LI.kiosk.utils.switchPanels('right', function() {
            $(document).trigger('menu:mount');
        });
    },
    mountProductDetails: function(product) {
        if(product.type == 'store') {
            LI.kiosk.insertStoreProductDetails(product);
        } else {
            LI.kiosk.insertProductDetails(product);
        }

        $('#details').effect('slide', {
            direction: 'right',
            mode: 'show',
            duration: '300',
            complete: function() {
                $('#details-breadcrumb a')
                    .html(product.name)
                    .parent()
                    .css('display', 'inline-block')
                ;
            }
        });
    },
    productToList: function(productType) {
        $('#details-breadcrumb').hide();

        LI.kiosk.utils.switchPanels('right', function() {
            $(document).trigger({
                type: 'product-list:mount',
                mode: 'back',
                productType: productType
            });
        });
    },
    mountDeclinations: function(product) {
        var declinationList = $('#declinations');
        var declinationTemplate = Handlebars.compile(LI.kiosk.templates.declinationCard);
        
        declinationList.empty();

        $('#prices')
            .empty()
            .hide()
        ;

        $.each(product.declinations, function(id, declination) {
            if(product.type == 'store') {
                declination.store = true;
                declination.value = declination.available_prices[Object.keys(declination.available_prices)[0]].value;
            }

            declinationList.append(declinationTemplate(declination));
        });

        $('#back-fab')
            .click(function() {
                $(document).trigger({
                    type: 'product-details:unmount',
                    productType: product.type
                })
            })
            .show()
        ;

        if(product.type !== 'store') {
            $('.declination').off('click').click(function(event) {
                var declination = product.declinations[$(event.currentTarget.children).attr('id')];

                $(document).trigger({
                    type: 'declinations:unmount',
                    product: product,
                    declination: declination
                });

                declinationList.hide();
            });
        }else {
            $('.declination').off('click').click(function(event) {
                var declination = product.declinations[$(event.currentTarget.children).attr('id')];
                var price = declination.available_prices[Object.keys(declination.available_prices)[0]];
            
                LI.kiosk.cart.addItem(product, price, declination);
            });
        }

        $('#declinations').css('display', 'flex');
    },
    declinationsToPrices: function(product, declination) {
        LI.kiosk.utils.resetBackFab();

        $('#declinations').effect('slide', {
            direction: 'left',
            mode: 'hide',
            duration: '150',
            complete: function() {
                $(document).trigger({
                    type: 'prices:mount',
                    product: product,
                    declination: declination
                });
            }
        });
    },
    mountPrices: function(product, declination, mode) {
        LI.kiosk.insertPrices(product, declination);

        $('#back-fab')
            .click(function() {
                $(document).trigger({
                    type: 'prices:unmount',
                    mode: mode,
                    product: product,
                    declination: declination
                })
            })
            .show();
        ;
        
        $('#prices').css('display', 'flex');
    },
    pricesToProducts: function(productType, mode) {
        $('#details-breadcrumb').hide();

        $('#prices').effect('slide', {
            direction: 'right',
            mode: 'hide',
            duration: '300',
            complete: function() {
                $('#details').hide();
                
                $(document).trigger({
                    type: 'product-list:mount',
                    productType: productType,
                    mode: mode
                });
            }
        });
    },
    pricesToDeclinations: function(product) {
        LI.kiosk.utils.resetBackFab();

        $('#prices').effect('slide', {
            direction: 'right',
            mode: 'hide',
            duration: '300',
            complete: function() {
                $(document).trigger({
                    type: 'declinations:mount',
                    product: product
                });
            }
        });
    },
    insertProducts: function(type) {
        var cardTemplate = LI.kiosk.templates['productCard'][type];

        if(cardTemplate == null)
            cardTemplate = LI.kiosk.templates['productCard']['manifestations'];

        $('#product-list').empty();
        
        var template = Handlebars.compile(cardTemplate);

        $.each(LI.kiosk.products[type], function(key, product){
            $('#product-list').append(template(product));
        });
    },
    insertProductDetails: function(product) {
        var detailsTemplate = Handlebars.compile(LI.kiosk.templates.productDetails);

        $('#declinations').empty();
        // insert manif info
        $('#product-details-card').html(detailsTemplate(product));

        if( Object.keys(product.declinations).length > 1 ) {
            $(document).trigger({
                type: 'declinations:mount',
                product: product
            });
        }else {
            $(document).trigger({
                type: 'prices:mount',
                product: product,
                mode: 'direct',
                declination: Object.values(product.declinations)[0]
            });
        }
    },
    insertStoreProductDetails:  function(product) {
        var detailsTemplate = Handlebars.compile(LI.kiosk.templates.productDetails);

        $('#product-details-card').html(detailsTemplate(product));

        $(document).trigger({
            type: 'declinations:mount',
            product: product
        });
    },
    insertPrices: function(product, declination) {
        var priceTemplate = $('#price-card-template').html();
        var prices = declination.available_prices;

        $('#prices, #declinations')
            .empty()
            .hide()
        ;

        var template = Handlebars.compile(priceTemplate);

        for(key in prices){
            $('#prices').append(template(prices[key]));
        }

        LI.kiosk.addPriceListener(product, declination);
    },
    addPriceListener: function(product, declination) {
        $('#prices').off('click').on('click', '.price', function(event) {
            LI.kiosk.cart.addItem(product, product.prices[$(event.currentTarget.children).attr('id')], declination);    
        });
    },
    /********************* CACHE **************************/
    rearrangeProperties: function(product) {
        var productDate = new Date(product.happens_at.replace(' ', 'T'));
        var endDate = new Date(product.ends_at);

        product.declinations = {};
        product.prices = {};
        product.start = productDate.toLocaleString().replace(/:\d\d( \w+){0,1}$/,'');
        product.end = endDate.getHours() + ':' + endDate.getMinutes();
        product.name = product.name == null ? product.category : product.name;

        if(product.image_id != undefined)
            product.background = 'background-image: url("' + product.image_url + '"); background-size: cover;';
        else
            product.background = 'background-color: ' + product.color;

        $.each(product.gauges, function(i, gauge){


            $.each(gauge.available_prices, function(key, price){
                if(LI.kiosk.config.uiLabels.price !== undefined) {
                    price.name = price[LI.kiosk.config.uiLabels.price]
                }

                product.prices[price.id] = price;
            });

            product.declinations[gauge.id] = gauge;
        });
    },
    cacheManifestations: function(data) {
        LI.kiosk.products.manifestations = {};
        var type = 'manifestations';

        if(data.success) {
            $.each(data.success.success_fields[type].data.content, function(key, manif) {
                
                if (LI.kiosk.debug)
                    console.log('Loading an item (#' + manif.id + ') from the ' + type);

                manif.type = type;
                LI.kiosk.rearrangeProperties(manif);
                LI.kiosk.products.manifestations[manif.id] = manif;
            });
        }
    },
    cacheMuseum: function(data) {
        var type = 'museum';
        LI.kiosk.products.museum = {};

        if(data.success) {
            $.each(data.success.success_fields[type].data.content, function(key, manif) {
                
                if (LI.kiosk.debug)
                    console.log('Loading an item (#' + manif.id + ') from the ' + type);

                manif.type = type;
                manif.museum = true;
                LI.kiosk.rearrangeProperties(manif);
                LI.kiosk.products.museum[manif.id] = manif;
            });
        }
    },
    cacheStore: function(data) {
        var type = 'store';
        LI.kiosk.products.store = {};

        if(data.success) {
            $.each(data.success.success_fields[type].data.content, function(key, product) {
                
                if (LI.kiosk.debug)
                    console.log('Loading an item (#' + product.id + ') from the ' + type);

                product.prices = {};
                product.type = type;
                product.store = true;

                $.each(product.declinations, function(i, declination) {

                    $.each(declination.available_prices, function(key, price) {
                        if(LI.kiosk.config.uiLabels.price !== undefined) {
                            price.name = price[LI.kiosk.config.uiLabels.price]
                        }

                        product.prices[price.id] = price;
                    });

                    product.declinations[declination.id] = declination;
                });

                LI.kiosk.products.store[product.id] = product;
            });
        }
    },
    cacheTemplates: function() {
        //make handlebars cache the templates for quicker future uses
        $('script[type="text/x-handlebars-template"]').each(function(id, template) {
            var templateType = $(template).data('template-type');
            var productType = $(template).data('product-type');
            var html = $(template).html();

            if( LI.kiosk.templates[templateType] === undefined )
                LI.kiosk.templates[templateType] = {};

            if(productType !== undefined)
                LI.kiosk.templates[templateType][productType] = html;
            else
                LI.kiosk.templates[templateType] = html;
        });
    },
    /******************** CART ****************************/
    cart: {
        total: 0,
        lines: {},
        insertLine: function(line, item, price, declination) {
            var lineTemplate = Handlebars.compile(LI.kiosk.templates.cartLine);

            $('#cart-lines').append(lineTemplate(line));

            $('#' + line.id + ' .remove-item').click(function() {
                LI.kiosk.cart.removeItem(line.id, item);

                $.each(line.linkedLines, function(id, line) {
                    LI.kiosk.cart.removeItem(id, line.product);
                });  
            });

            $('#' + line.id + ' .add-item').click(function() {
                LI.kiosk.cart.addItem(item, price, declination);

                if(line.linkedLines) {
                    $.each(line.linkedLines, function(id, line) {
                        LI.kiosk.cart.addItem(line.product, line.price, line.declination);
                    });
                }
            });
        },
        removeLine: function(htmlLine) {
            $(htmlLine).hide(500).remove();
        },
        lineTotal: function(line) {
            line.total = LI.format_currency(line.price.raw_value * line.qty, false);
        },
        cartTotal: function() {
            LI.kiosk.cart.total = 0;

            $.each(LI.kiosk.cart.lines, function(key, line) {
                LI.kiosk.cart.total += line.price.raw_value * line.qty;
            });

            $('#cart-total-value').text(LI.format_currency(LI.kiosk.cart.total, false));
        },
        addItem: function(item, price, declination, linkedLineId) {
            var newLine;
            var lineId;
            var lineExists = false;

            $.each(LI.kiosk.cart.lines, function(key, line) {
                if(line.product.id == item.id && line.price.id == price.id && line.declination.id == declination.id) {
                            
                    var htmlLine = $('#' + line.id);

                    line.qty++;
                    lineExists = true;
                    LI.kiosk.cart.lineTotal(line);
                    
                    htmlLine.find('.line-total').text(line.total);
                    htmlLine.find('.line-qty').text(line.qty);
                    LI.kiosk.utils.flash('#' + line.id);

                    newLine = line;
                }
            });

            if(!lineExists) {
                newLine = LI.kiosk.cart.newLine(item, price, declination);
                LI.kiosk.utils.flash('#' + newLine.id);
            }

            if(linkedLineId) {
                LI.kiosk.cart.addLinkedLine(newLine, linkedLineId, item);
            }

            LI.kiosk.cart.cartTotal();

            if(!$('#cart').is(':visible')) {
                $('#cart').show(500);
                $('#cart').css('display', 'flex');
            } 

            LI.kiosk.cart.validateItem(newLine);
        },
        addLinkedLine: function(line, owningLineId, item) {
            var owningLine = LI.kiosk.cart.lines[owningLineId];

            if(!owningLine.linkedLines) {
                owningLine.linkedLines = {};
            }

            if(!owningLine.linkedLines[line.id]) {
                owningLine.linkedLines[line.id] = line;
            }

            line.linked = true;
            owningLine.linked = true;
            owningLine.isOwner = true;
        },
        removeItem: function(lineId, item) {
            var line = LI.kiosk.cart.lines[lineId];
            var htmlLine = $('#' + lineId);

            line.qty--;
            LI.kiosk.cart.lineTotal(line);

            if(line.qty == 0){
                delete LI.kiosk.cart.lines[lineId]
                LI.kiosk.cart.removeLine(htmlLine);
            }else{
                LI.kiosk.cart.lines[lineId] = line;
                htmlLine.find('.line-qty').text(line.qty);
                htmlLine.find('.line-total').text(line.total);
            }

            LI.kiosk.cart.updateTransaction({ 
                transaction: {
                    price_new: {
                        _csrf_token: LI.kiosk.CSRF,
                        price_id: line.price.id,
                        declination_id: line.declination.id,
                        type: item.type == 'store' ? 'declination' : 'gauge',
                        bunch: item.type,
                        id: LI.kiosk.transaction.id,
                        state: '',
                        qty: '-1'
                    }
                }
            });

            LI.kiosk.cart.cartTotal();

            if(Object.keys(LI.kiosk.cart.lines) < 1) {
                $('#cart').hide(200);
            }
        },
        newLine: function(item, price, declination) {
            var newLine = {
                id: LI.kiosk.utils.generateUUID(),
                name: item.name,
                product: item,
                value: price.value,
                price: price,
                declination: declination,
                qty: 1,
                total: price.value
            };

            LI.kiosk.cart.lines[newLine.id] = newLine;
            LI.kiosk.cart.insertLine(LI.kiosk.cart.lines[newLine.id], item, price, declination);

            return newLine;
        },
        validateItem: function(line) {
            var available = false;
            
            if(line.product.type == 'store') {
                available = true;
            }

            if(line.product.gauge_url !== undefined) {
                available = LI.kiosk.cart.checkAvailability(line.product.gauge_url, line.id, line.product.id);
            }

            if(available) {
                $.when(LI.kiosk.cart.updateTransaction({
                    transaction: {
                        price_new: {
                            _csrf_token: LI.kiosk.CSRF,
                            price_id: line.price.id,
                            declination_id: line.declination.id,
                            type: line.product.type == 'store' ? 'declination' : 'gauge',
                            bunch: line.product.type,
                            id: LI.kiosk.transaction.id,
                            state: '',
                            qty: '1'
                        }
                    }
                }))
                .then(function() {
                    if(!line.product.isNecessaryTo && !line.linked) {
                        LI.kiosk.cart.checkForLinkedProducts(line);
                    }
                });
            }
        },
        checkAvailability: function(gaugeUrl, lineId, productId) {
            var qty = 0;
            var available = true;

            $.each(LI.kiosk.cart.lines, function(key, line) {
                if(line.product.id == productId) {
                    qty += line.qty;
                }
            });

            $.get(gaugeUrl, function(data) {
                if(data.free < qty){
                    console.log(lineId);
                    available = false;
                    $('#' + lineId + ' .remove-item').click();
                    toastr.info('The last item added to the cart was removed as it wasn\'t available anymore');
                }
            });

            return available;
        },
        checkForLinkedProducts: function(line) {
            $.get(LI.kiosk.urls.getManifestations + '&id=' + LI.kiosk.transaction.id, function(data) {
                LI.kiosk.cart.handleLinkedProducts(data, line, 'manifestations');
            });

            $.get(LI.kiosk.urls.getMuseum + '&id=' + LI.kiosk.transaction.id, function(data) {
                LI.kiosk.cart.handleLinkedProducts(data, line, 'museum');
            });
        },
        handleLinkedProducts: function(data, line, productType) {
            $.each(data.success.success_fields[productType].data.content, function(key, item) {
                if(item.id != line.product.id) {
                    item.isNecessaryTo = line.product.name;

                    LI.kiosk.cart.addLinkedProduct(item, line);
                }
            });
        },
        addLinkedProduct: function(item, line, type) {
            item.type = type;
            item.noLink = true;
            
            LI.kiosk.rearrangeProperties(item);

            var linkedDeclination;
            var linkedPrice;

            $.each(item.declinations, function(key, declination) {
                if (declination.name == line.declination.name) {
                    linkedDeclination = declination;
                }
            });

            $.each(item.prices, function(key, price) {
                if (price.name == line.price.name) {
                    linkedPrice = price;
                }
            });

            if(linkedPrice && linkedDeclination) {
                LI.kiosk.cart.addItem(item, linkedPrice, linkedDeclination, line.id);
            }
        },
        updateTransaction: function(data, successCallback, errorCallback) {
            return $.ajax({
                url: LI.kiosk.urls.completeTransaction.replace('-666', LI.kiosk.transaction.id),
                type: 'get',
                data: data,
                success: successCallback,
                error: errorCallback !== undefined ? errorCallback : LI.kiosk.utils.error
            });
        }
    },
    /************** CHECKOUT *******************************/
    checkout: function() {
        LI.kiosk.utils.showPaymentPrompt();

        var eptOptions = {
            amount: LI.kiosk.cart.total * 100,
            delay: 'A010',
            version: 'E+'
        };

        var message = new ConcertProtocolMessage(eptOptions);
        
        var device = new ConcertProtocolDevice(LI.kiosk.devices.ept, LI.kiosk.connector);

        device
            .doTransaction(message)
            .then(function(res) {
                if(res.stat === '0') {
                    LI.kiosk.finalize();
                } else {
                    console.error(res.stat + ' ' + res.getStatusText());
                    LI.kiosk.utils.showPaymentFailurePrompt();
                }
            })
            .catch(function(err) {
                console.error(err);
            })
        ;
    },
    finalize: function() {
        LI.kiosk.print();

        LI.kiosk.cart.updateTransaction({
            transaction: {
                payment_new: {
                    _csrf_token: LI.kiosk.CSRF,
                    value: LI.kiosk.cart.total,
                    payment_method_id: LI.kiosk.config.paymentMethod
                }
            }
        });
    },
    print: function(duplicate) {
        LI.kiosk.utils.showPaymentSuccessPrompt();

        if(!LI.kiosk.ticketsIntegrated) {
            LI.kiosk.integrateTickets().then(function() {
                LI.kiosk.printTickets(duplicate);
            });

            return;
        }

        LI.kiosk.printTickets(duplicate);
    },
    /******************  TICKETS **************************/
    integrateTickets: function() {
        return LI.kiosk.cart.updateTransaction(
            { 
                transaction: {
                    store_integrate: {
                        _csrf_token: LI.kiosk.CSRF,
                        id: LI.kiosk.transaction.id,
                        force: ''
                    }
                }
            }, 
            function() {
                LI.kiosk.ticketsIntegrated = true;
            }
        );
    },
    printTickets: function(duplicate) {
        $.get(
            LI.kiosk.urls.printTickets.replace('-666', LI.kiosk.transaction.id) +
                '?duplicate="' + duplicate + '"' +
                '&price_name=&manifestation_id=' +
                '&direct={"vid": ' + LI.kiosk.devices.ticketPrinter.params.vid + 
                ', "pid": ' + LI.kiosk.devices.ticketPrinter.params.pid + 
                '}'
            , 
            function(data) {
                //if ( window.location.hash == '#test' ) {
                    data = '\r\nG0AbHQMDAAAbHkEBGwcUFBsqclIbKnJBGypyUTEAGypyRDMAGypyVDEAGypyRjkAGypyRTkAABsqclAxMTYwABsqclkxABsMABsqclAxMTYwABsqclk0NwBiLAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAH\/\/\/\/\/\/\/\/\/\/\/\/mIsAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAf\/\/\/\/\/\/\/\/\/\/\/+GypyWTIAYjUAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAB\/\/\/\/\/\/\/\/\/\/\/\/4AAAAAAAAAAfBiNQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAH\/\/\/\/\/\/\/\/\/\/\/\/gAAAAAAAAHz+GI1AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAf\/\/\/\/\/\/\/\/\/\/\/+AAAAAAAAA\/8cYjUAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAHDgxiNQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAYMBGI1AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAABgwGYjUAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAGDAZiNQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAH\/\/\/\/\/\/\/\/\/\/\/\/gAAAAAAAAYADGI1AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAf\/\/\/\/\/\/\/\/\/\/\/+AAAAAAAAA4AcYjUAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAB\/\/\/\/\/\/\/\/\/\/\/\/4AAAAAAAAD4HhiNQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAH\/\/\/\/\/\/\/\/\/\/\/\/gAAAAAAAADgcGIsAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAf\/\/\/\/\/\/\/\/\/\/\/+YiwAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAB\/\/\/\/\/\/\/\/\/\/\/\/5iNQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAH\/\/\/\/\/\/\/\/\/\/\/\/gAAAAAAAABh8GI1AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAfP4YjUAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD\/hxiNQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAH\/\/\/\/\/\/\/\/\/\/\/\/gAAAAAAAAcODGI1AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAf\/\/\/\/\/\/\/\/\/\/\/+AAAAAAAABgwEYjUAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAB\/\/\/\/\/\/\/\/\/\/\/\/4AAAAAAAAGDAZiNQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAYMBGI1AAAAAB4AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAABw4MYjUAAAAAMwAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAB\/\/\/\/\/\/\/\/\/\/\/\/4AAAAAAAAD\/xxiNQAAAAAhAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAH\/\/\/\/\/\/\/\/\/\/\/\/gAAAAAAAAHz+GI1AAAAACEAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAf\/\/\/\/\/\/\/\/\/\/\/+AAAAAAAAAAHwYiwAAAAAIQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAB\/\/\/\/\/\/\/\/\/\/\/\/5iBAAAAAAhYjUAAAAAP\/gAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAw\/BiNQAAAAA\/+AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAHH+GI1AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA44cYjUAAAAAIAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAGDAxiNQAAAAAgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAH\/\/\/\/\/\/\/\/\/\/\/\/gAAAAAAAAYIBGI1AAAAACAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAf\/\/\/\/\/\/\/\/\/\/\/+AAAAAAAABhgGYjUAAAAAIAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAB\/\/\/\/\/\/\/\/\/\/\/\/4AAAAAAAAGDAxiNQAAAAA\/+AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAH\/\/\/\/\/\/\/\/\/\/\/\/gAAAAAAAAMMDGI1AAAAACAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAf\/\/\/\/\/\/\/\/\/\/\/+AAAAAAAAA8c8YjUAAAAAIAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA\/\/BiNQAAAAAgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA\/wGIEAAAAACBiBQAAAAAAgGI1AAAAAACAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAf\/\/\/\/\/\/\/\/\/\/\/+AAAAAAAAAMPwYjUAAAAAAIAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAB\/\/\/\/\/\/\/\/\/\/\/\/4AAAAAAAABx\/hiNQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAH\/\/\/\/\/\/\/\/\/\/\/\/gAAAAAAAAOOHGI1AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAf\/\/\/\/\/\/\/\/\/\/\/+AAAAAAAABgwMYjUAAAAAHwAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAB\/\/\/\/\/\/\/\/\/\/\/\/4AAAAAAAAGCARiNQAAA8B\/wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAH\/\/\/\/\/\/\/\/\/\/\/\/gAAAAAAAAYYBmI1AAADwf\/gAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAf\/\/\/\/\/\/\/\/\/\/\/+AAAAAAAABgwMYjUAAAPB\/\/AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAADDAxiNQAAA8P\/+AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAPHPGI1AAADw+D4AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAf\/\/\/\/\/\/\/\/\/\/\/+AAAAAAAAAP\/wYjUAAAPHwHwAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAB\/\/\/\/\/\/\/\/\/\/\/\/4AAAAAAAAAP8BiLAAAA8eAPAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAH\/\/\/\/\/\/\/\/\/\/\/\/mIFAAADw4A8YgUAAAPDgDxiBQAAA8PAfGIFAAAD\/+D4YiwAAAP\/4fgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAB\/\/\/\/\/\/\/\/\/\/\/\/5iNQAAA\/\/h8AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAH\/\/\/\/\/\/\/\/\/\/\/\/gAAAAAAAAf\/\/GI1AAAAB+HgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAf\/\/\/\/\/\/\/\/\/\/\/+AAAAAAAAA\/\/8YjQAAAAAAcAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAB\/\/\/\/\/\/\/\/\/\/\/\/4AAAAAAAAAwGI0AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAf\/\/\/\/\/\/\/\/\/\/\/+AAAAAAAAAMBiNAAAAB4AeAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAADAYjQAAAB\/gHgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAwGIsAAAB\/8B4AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAf\/\/\/\/\/\/\/\/\/\/\/+YiwAAAH\/4HgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAB\/\/\/\/\/\/\/\/\/\/\/\/5iLAAAA\/\/weAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAH\/\/\/\/\/\/\/\/\/\/\/\/mIsAAADwPh4AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAf\/\/\/\/\/\/\/\/\/\/\/+YgUAAAPAeHhiBQAAA4A8eGIFAAAHgD54YjUAAAOAH3gAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAADD8AAH\/\/xiNQAAA8AP+AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAcf4AAP\/\/GI0AAAD\/Af4AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAf\/\/\/\/\/\/\/\/\/\/\/+AAADjhwAAMBiNAAAAfwH+AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAH\/\/\/\/\/\/\/\/\/\/\/\/gAABgwMAADAYjQAAAH8A\/gAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAYIBAAAwGI0AAAA\/AD4AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAGGAYAAMBiMQAAABwAOAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAABgwMYjEAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAMMDGIxAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAADxzxiNQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP\/wAAB\/wGI1AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP8AAAf\/4YjUAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAB\/\/\/\/\/\/\/\/\/\/\/\/4AAAAAAAADwDxiNQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAH\/\/\/\/\/\/\/\/\/\/\/\/gAAAAAAAAMADGI1AAAD\/\/\/4AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAYfAABgAEYjUAAAP\/\/\/gAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAHz+AAGAAZiNQAAA\/\/\/+AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA\/4cAAYABGI1AAAA\/\/\/4AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAHDgwAAwAMYjUAAAB4AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAYMBAADwHxiNQAAAHgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAH\/\/\/\/\/\/\/\/\/\/\/\/gAABgwGAAH\/8GI1AAAAOAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAf\/\/\/\/\/\/\/\/\/\/\/+AAAGDAQAAD\/AYjEAAAA4AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAB\/\/\/\/\/\/\/\/\/\/\/\/4AAAcODGIxAAAAOAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAf\/\/\/\/\/\/\/\/\/\/\/+AAAD\/xxiMQAAADgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAH\/\/\/\/\/\/\/\/\/\/\/\/gAAAfP4YjEAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAB8BsqclkxAGIsAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAf\/\/\/\/\/\/\/\/\/\/\/+YjUAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAB\/\/\/\/\/\/\/\/\/\/\/\/4AAAAAAAAH\/\/xiNQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAH\/\/\/\/\/\/\/\/\/\/\/\/gAAAAAAAAP\/\/GI0AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAf\/\/\/\/\/\/\/\/\/\/\/+AAAAAAAAAMBiNAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAADAYjQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAwGI0AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAMBiLAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAH\/\/\/\/\/\/\/\/\/\/\/\/mIxAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAf\/\/\/\/\/\/\/\/\/\/\/+AAAAf8BiMQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAf\/wYjEAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAOOOGI0AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAf\/\/\/\/\/\/\/\/\/\/\/+AAAHAwwAAAFiNAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAH\/\/\/\/\/\/\/\/\/\/\/\/gAABgMMAAABYjQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAB\/\/\/\/\/\/\/\/\/\/\/\/4AAAYDBgAAAWI0AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAGAwYAAAFiNAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAABwMMAAABYjQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAB\/\/\/\/\/\/\/\/\/\/\/\/4AAAOHHAAAAWIxAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAf\/\/\/\/\/\/\/\/\/\/\/+AAAB\/jhiMQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAPggYjUAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA+AxiNQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAH\/\/\/\/\/\/\/\/\/\/\/\/gAAAAAAAAP8DGI1AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAf\/\/\/\/\/\/\/\/\/\/\/+AAAAw\/AAAw4MYjUAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAB\/\/\/\/\/\/\/\/\/\/\/\/4AAAHH+AAGBgxiNQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAH\/\/\/\/\/\/\/\/\/\/\/\/gAAA44cAAYDDGI1AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAf\/\/\/\/\/\/\/\/\/\/\/+AAAGDAwABgMMYjUAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAYIBAAGAYxiNQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAABhgGAAcBzGI1AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAGDAwAA4DsYjUAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAMMDAAB4HxiNQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA8c8AABgPGIxAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA\/\/BiMQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD\/AYjUAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAB\/\/\/\/\/\/\/\/\/\/\/\/4AAAAAAAAAYfBiNQAAAAAAAAAAAAAAAAAAAAAA4AAAAAAAAAAAAAAAAAAAAAAAH\/\/\/\/\/\/\/\/\/\/\/\/gAAAAAAAAHz+GI1AAAAAAAAAAAAAAAAAAAAAAHwAeAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA\/4cYjUAAAAAAAAAAAAAAAAAAAAAAeMB4AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAHDgxiNQAAAAAAAAAAAAAAAAAAAAAB4yDgAAAAAAAAAAAAAAAAAAAAH\/\/\/\/\/\/\/\/\/\/\/\/gAAAAAAAAYMBGI1AAAAAAAAAAAAAAAAAAAAAAHDMPAAAAAAAAAAAAAAAAAAAAAf\/\/\/\/\/\/\/\/\/\/\/+AAAAAAAABgwGYjUAAAAAAAAAAAAAAAAAAAAAAcMw8AAAAAAAAAAAAAAAAAAAAB\/\/\/\/\/\/\/\/\/\/\/\/4AAAf\/\/AAGDARiNQAAAAAAAAAAAAAAAAAAAAAB4zDgAAAAAAAAAAAAAAAAAAAAH\/\/\/\/\/\/\/\/\/\/\/\/gAAA\/\/8AAcODGI1AAAAAAAAAAAAAAAAAAAAAAHzMeAAAAAAAAAAAAAAAAAAAAAf\/\/\/\/\/\/\/\/\/\/\/+AAAAwAAAA\/8cYjUAAAAAAAAAAAAAAAAAAAAAAP834AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAADAAAAB8\/hiNQAAAAAAAAAAAAAAAAAAAAAA\/\/\/AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAMAAAAAB8GIwAAAAAAAAAAAAAAAAAAAAAAB\/\/4AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAwGISAAAAAAAAAAAAAAAAAAAAAAAf\/2I1AAAAAAAAAAAAAAAAAAAAAAAH+AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAMPwYjUAAAAAAAAAAAAAAAAAAAAAAAMwAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAABx\/hiNQAAAAAAAAAAAAAAAAAAAAAAARAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAOOHGI1AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAf\/\/\/\/\/\/\/\/\/\/\/+AAAAAAAABgwMYjUAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAB\/\/\/\/\/\/\/\/\/\/\/\/4AAAAAAAAGCARiNQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAYYBmI1AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAH\/\/wABgwMYjUAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAB\/\/\/\/\/\/\/\/\/\/\/\/4AAAP\/\/AADDAxiNQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAH\/\/\/\/\/\/\/\/\/\/\/\/gAAAMAAAAPHPGI1AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAf\/\/\/\/\/\/\/\/\/\/\/+AAAAwAAAAP\/wYjUAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAB\/\/\/\/\/\/\/\/\/\/\/\/4AAADAAAAAP8BiMAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAH\/\/\/\/\/\/\/\/\/\/\/\/gAAAMBiEgAAAAAAAAAAAAAAAAAAAAAAAAhiNQAAAAAAAAAAAAAAAAAAAAAB4P8AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAADD8GI1AAAAAAAAAAAAAAAAAAAAAAHh\/8AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAcf4YjUAAAAAAAAAAAAAAAAAAAAAAeP\/wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAB\/wAADjhxiNQAAAAAAAAAAAAAAAAAAAAAB4\/fgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAf\/4AAYMDGI1AAAAAAAAAAAAAAAAAAAAAAHjgeAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAADwDwABggEYjUAAAAAAAAAAAAAAAAAAAAAAeOA8AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAMADAAGGAZiNQAAAAAAAAAAAAAAAAAAAAAB44DwAAAAAAAAAAAAAAAAAAAAH\/\/\/\/\/\/\/\/\/\/\/\/gAABgAEAAYMDGI1AAAAAAAAAAAAAAAAAAAAAAHjgPAAAAAAAAAAAAAAAAAAAAAf\/\/\/\/\/\/\/\/\/\/\/+AAAGAAYAAwwMYjUAAAAAAAAAAAAAAAAAAAAAAeOA4AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAYABAADxzxiNQAAAAAAAAAAAAAAAAAAAAAB\/8PgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAwAMAAD\/8GI1AAAAAAAAAAAAAAAAAAAAAAH\/w+AAAAAAAAAAAAAAAAAAAAAf\/\/\/\/\/\/\/\/\/\/\/+AAADwHwAAD\/AYjEAAAAAAAAAAAAAAAAAAAAAAB\/DwAAAAAAAAAAAAAAAAAAAAB\/\/\/\/\/\/\/\/\/\/\/\/4AAAH\/8GIxAAAAAAAAAAAAAAAAAAAAAAAAQ4AAAAAAAAAAAAAAAAAAAAAf\/\/\/\/\/\/\/\/\/\/\/+AAAAP8BiLAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAH\/\/\/\/\/\/\/\/\/\/\/\/mIsAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAf\/\/\/\/\/\/\/\/\/\/\/+GypyWTIAYjUAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAH\/\/xiNQAAAAAAAAAAAAAAAAAAAAAB\/\/\/gAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP\/\/GI0AAAAAAAAAAAAAAAAAAAAAAH\/\/+AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAMBiNAAAAAAAAAAAAAAAAAAAAAAB\/\/\/gAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAADAYjQAAAAAAAAAAAAAAAAAAAAAAP\/\/4AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAADD8AAAwGI0AAAAAAAAAAAAAAAAAAAAAAB4AAAAAAAAAAAAAAAAAAAAAAAf\/\/\/\/\/\/\/\/\/\/\/+AAABx\/gAAMBiMQAAAAAAAAAAAAAAAAAAAAAAOAAAAAAAAAAAAAAAAAAAAAAAH\/\/\/\/\/\/\/\/\/\/\/\/gAAA44cYjEAAAAAAAAAAAAAAAAAAAAAADgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAYMDGIxAAAAAAAAAAAAAAAAAAAAAAA4AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAGCARiMQAAAAAAAAAAAAAAAAAAAAAAGAAAAAAAAAAAAAAAAAAAAAAAH\/\/\/\/\/\/\/\/\/\/\/\/gAABhgGYjEAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAB\/\/\/\/\/\/\/\/\/\/\/\/4AAAYMDGIxAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAf\/\/\/\/\/\/\/\/\/\/\/+AAADDAxiMQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAH\/\/\/\/\/\/\/\/\/\/\/\/gAAA8c8YjUAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAB\/\/\/\/\/\/\/\/\/\/\/\/4AAAD\/8AAH\/\/xiNQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD\/AAAP\/\/GI0AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAMBiNAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAADAYjQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAABh8AAAwGI0AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAB8\/gAAMBiMQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA\/4cYjEAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAcODGIxAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAf\/\/\/\/\/\/\/\/\/\/\/+AAAGDARiNQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAH\/\/\/\/\/\/\/\/\/\/\/\/gAABgwGAAB\/wGI1AAAAAAAAAAAAAAAAAAAAAAAD\/+AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAGDAQAAf\/4YjUAAAAAAAAAAAAAAAAAAAAAAAf\/4AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAcODAADwDxiNQAAAAAAAAAAAAAAAAAAAAAAD\/\/gAAAAAAAAAAAAAAAAAAAAH\/\/\/\/\/\/\/\/\/\/\/\/gAAA\/8cAAMADGI1AAAAAAAAAAAAAAAAAAAAAAAP\/+AAAAAAAAAAAAAAAAAAAAAf\/\/\/\/\/\/\/\/\/\/\/+AAAB8\/gABgAEYjUAAAAAAAAAAAAAAAAAAAAAAA4AAAAAAAAAAAAAAAAAAAAAAB\/\/\/\/\/\/\/\/\/\/\/\/4AAAAB8AAGAAZiNQAAAAAAAAAAAAAAAAAAAAAADgAAAAAAAAAAAAAAAAAAAAAAH\/\/\/\/\/\/\/\/\/\/\/\/gAAAAAAAAYABGI1AAAAAAAAAAAAAAAAAAAAAAAOAAAAAAAAAAAAAAAAAAAAAAAf\/\/\/\/\/\/\/\/\/\/\/+AAAAAAAAAwAMYjUAAAAAAAAAAAAAAAAAAAAAAA4AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAADwHxiNQAAAAAAAAAAAAAAAAAAAAAABwAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAH\/8GI1AAAAAAAAAAAAAAAAAAAAAAAP\/+AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD\/AYhMAAAAAAAAAAAAAAAAAAAAAAA\/\/4GITAAAAAAAAAAAAAAAAAAAAAAAP\/+BiEwAAAAAAAAAAAAAAAAAAAAAAD\/\/gGypyWTEAYjEAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAB\/\/\/\/\/\/\/\/\/\/\/\/4AAAAAwGIxAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAf\/\/\/\/\/\/\/\/\/\/\/+AAAAAMBiNQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAB\/\/8AAf\/\/GI1AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAH\/\/wAA\/\/8YjQAAAAAAAAAAAAAAAAAAAAAA8\/\/4AAAAAAAAAAAAAAAAAAAAB\/\/\/\/\/\/\/\/\/\/\/\/4AAAOAwAAAwGI0AAAAAAAAAAAAAAAAAAAAAAPP\/+AAAAAAAAAAAAAAAAAAAAAf\/\/\/\/\/\/\/\/\/\/\/+AAAAwMAAAMBiNAAAAAAAAAAAAAAAAAAAAAADz\/\/gAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAHDAAADAYjQAAAAAAAAAAAAAAAAAAAAAA8\/\/4AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA4wAAAwGIxAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAADsBiMQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAfAYjEAAAAAAAAAAAAAAAAAAAAAAAB4AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAABwGI0AAAAAAAAAAAAAAAAAAAAAAAB+YAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAFiNAAAAAAAAAAAAAAAAAAAAAAAB\/nAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAABYjQAAAAAAAAAAAAAAAAAAAAAAAf54AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD8AAAAWI0AAAAAAAAAAAAAAAAAAAAAAAPueAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAGD\/gAAAFiNAAAAAAAAAAAAAAAAAAAAAAADjjgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAABgwcAAABYjQAAAAAAAAAAAAAAAAAAAAAAA44cAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAYYDAAAAWIxAAAAAAAAAAAAAAAAAAAAAAAOOHAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAGGARiMQAAAAAAAAAAAAAAAAAAAAAADjjwAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAABhgGYjEAAAAAAAAAAAAAAAAAAAAAAA444AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAYYBGIxAAAAAAAAAAAAAAAAAAAAAAAP\/+AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAGGAxiMQAAAAAAAAAAAAAAAAAAAAAAB\/\/AAAAAAAAAEAAAAAAAAAAAAAAAAAAAAAAAAAAAB\/wcYjEAAAAAAAAAAAAAAAAAAAAAAAP\/wAAAAAAAD\/DAAAAAAAAAAAAAAAAAAAAAAAAAAAP8OGI1AAAAAAAAAAAAAAAAAAAAAAAB\/wAAAAAAAH\/w8AAAAAAAAAAAAAAAAAAAAAAAAAAABDAAB\/\/8YjUAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAB\/\/D4AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD\/\/xiNAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP\/8PwAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAADAYjQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAH\/\/D+AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAwGI0AAAAAAAAAAAAAAAAAAAAAAP\/\/+AAAAAAB\/\/w\/wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAMBiNAAAAAAAAAAAAAAAAAAAAAAD\/\/\/gAAAAAA\/\/8P+AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAADAYhwAAAAAAAAAAAAAAAAAAAAAA\/\/\/4AAAAAAP\/\/D\/gGIcAAAAAAAAAAAAAAAAAAAAAAP\/\/+AAAAAAH+HwP4BiHAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAB\/B8B\/AYjUAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA\/gfAPwAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAYfBiNQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD+B8A\/AAAAAAAAAAAAAAAAAAAAAAAAAADAMAAHz+GI1AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAPwHwD8AAAAAAAAAAAAAAAAAAAAAAAAAAMAwAA\/4cYjUAAAAAAAAAAAAAAAAAAAAAAAD+AAAAAAA\/AfAPwAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAHDgxiNQAAAAAAAAAAAAAAAAAAAAAAA\/+AAAAAAD8B8A\/AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAYMBGI1AAAAAAAAAAAAAAAAAAAAAAAH\/8AAAAAAPwHwD8AAAAAAAAAAAAAAAAAAAAAAAAAAAAAABgwGYjUAAAAAAAAAAAAAAAAAAAAAAA\/\/4AAAAAA\/gfAPwAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAGDARiNQAAAAAAAAAAAAAAAAAAAAAAD4HgAAAAAB+B8B\/AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAcODGI1AAAAAAAAAAAAAAAAAAAAAAAOAPAAAAAAH+HwP8AAAAAAAAAAAAAAAAAAAAAAAAAH\/\/wAA\/8cYjUAAAAAAAAAAAAAAAAAAAAAAA4A8AAAAAAf+fD\/gAAAAAAAAAAAAAAAAAAAAAAAAAf\/\/AAB8\/hiNQAAAAAAAAAAAAAAAAAAAAAADgDwAAAAAA\/\/\/\/+AAAAAAAAAAAAAAAAAAAAAAAAAAAA4AAAB8GIxAAAAAAAAAAAAAAAAAAAAAAAOAOAAAAAAD\/\/\/\/wAAAAAAAAAAAAAAAAAAAAAAAAAAAPBiMQAAAAAAAAAAAAAAAAAAAAAABwHAAAAAAAf\/\/\/8AAAAAAAAAAAAAAAAAAAAAAAAAAAPAYjUAAAAAAAAAAAAAAAAAAAAAAA\/\/\/4AAAAAD\/\/\/+AAAAAAAAAAAAAAAAAAAAAAAAAAAHgAAAw\/BiNQAAAAAAAAAAAAAAAAAAAAAAD\/\/\/gAAAAAH\/\/\/wAAAAAAAAAAAAAAAAAAAAAAAAAAB4AAAHH+GI1AAAAAAAAAAAAAAAAAAAAAAAP\/\/+AAAAAAH\/\/8AAAAAAAAAAAAAAAAAAAAAAAAAAAPAAAA44cYjUAAAAAAAAAAAAAAAAAAAAAAA\/\/\/4AAAAAAH\/\/AAAAAAAAAAAAAAAAAAAAAAAAAAADwAAAGDAxiNQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAB\/AAAAAAAAAAAAAAAAAAAAAAAAAAAAcAAAAYIBGI1AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAHgAAABhgGYjUAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA\/wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAf\/\/AAGDAxiNQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD\/AAAAAAAAAAAAAAAAAAAAAAAAAAAAAB\/\/8AAMMDGI1AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP8AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA8c8YjUAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA\/wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA\/\/BiNQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAB\/AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA\/wGIZAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAH8BiMAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA\/gAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA5iNQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAfgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAH\/AAADD8GI1AAAAAAAAAAAAAAAAAAAAAAHOAAAAAAAAA\/AAAAAAAAAAAAAAAAAAAAAAAAAAAAAB\/\/AAAcf4YjUAAAAAAAAAAAAAAAAAAAAAAY4AAAAAAAAA\/wAAAAAAAAAAAAAAAAAAAAAAAAAAAAOAeAADjhxiNQAAAAAAAAAAAAAAAAAAAAADzgAAAAAAAB\/\/\/\/+AAAAAAAAAAAAAAAAAAAAAAAAABwAcAAYMDGI1AAAAAAAAAAAAAAAAAAAAAAH\/\/+AAAAAAH\/\/\/\/4AAAAAAAAAAAAAAAAAAAAAAAAAGAAwABggEYjUAAAAAAAAAAAAAAAAAAAAAAf\/\/4AAAAAAf\/\/\/\/gAAAAAAAAAAAAAAAAAAAAAAAAAYADAAGGAZiNQAAAAAAAAAAAAAAAAAAAAAB\/\/\/gAAAAAB\/\/\/\/+AAAAAAAAAAAAAAAAAAAAAAAAADAAMAAYMDGI1AAAAAAAAAAAAAAAAAAAAAAAOAAAAAAAAH\/\/\/\/4AAAAAAAAAAAAAAAAAAAAAAAAAMAAYAAwwMYjUAAAAAAAAAAAAAAAAAAAAAAA4AAAAAAAAf\/\/\/\/gAAAAAAAAAAAAAAAAAAAAAAAAAwABgADxzxiNQAAAAAAAAAAAAAAAAAAAAAADgAAAAAAAB\/\/\/\/+AAAAAAAAAAAAAAAAAAAAAAAAADgAMAAD\/8GI1AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAGAAwAAD\/AYjEAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAcAHGIxAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAADgDhiMQAAAAAAAAAAAAAAAAAAAAADz\/\/gAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAeDwYjEAAAAAAAAAAAAAAAAAAAAAA8\/\/4AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD\/4GIxAAAAAAAAAAAAAAAAAAAAAAPP\/+AAAAAAP8AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP4BiGQAAAAAAAAAAAAAAAAAAAAADz\/\/gAAAAAD\/AYjUAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA\/wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAH\/\/xiNQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD\/AAAAAAAAAAAAAAAAAAAAAAAAAAAAAB\/\/8AAP\/\/GI0AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAH8AAAAAAAAAAAAAAAAAAAAAAAAAAAAAH\/\/wAAMBiNAAAAAAAAAAAAAAAAAAAAAAADgAAAAAAAB\/AAAAAAAAAAAAAAAAAAAAAAAAAAAAAB\/\/8AADAYjQAAAAAAAAAAAAAAAAAAAAAAA4AAAAAAAAP4AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAwGI0AAAAAAAAAAAAAAAAAAAAAAAPAAAAAAAAB+AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAMBiGQAAAAAAAAAAAAAAAAAAAAAABwAAAAAAAAPwYi8AAAAAAAAAAAAAAAAAAAAAAA\/\/4AAAAAAA\/wAAAAAAAAAAAAAAAAAAAAAAAAAAAAZiLwAAAAAAAAAAAAAAAAAAAAAAD\/\/gAAAAAB\/\/\/\/+AAAAAAAAAAAAAAAAAAAAAAAAABmIvAAAAAAAAAAAAAAAAAAAAAAAP\/+AAAAAAH\/\/\/\/4AAAAAAAAAAAAAAAAAAAAAAAAAGYi8AAAAAAAAAAAAAAAAAAAAAAA\/\/4AAAAAAf\/\/\/\/gAAAAAAAAAAAAAAAAAAAAAAAAAZiLwAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAB\/\/\/\/+AAAAAAAAAAAAAAAAAAAAAAAAABmIvAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAH\/\/\/\/4AAAAAAAAAAAAAAAAAAAAAAAAAGYjUAAAAAAAAAAAAAAAAAAAAAAAAAIAAAAAAf\/\/\/\/gAAAAAAAAAAAAAAAAAAAAAAAAAf\/\/AAH\/\/xiNQAAAAAAAAAAAAAAAAAAAAAAA\/\/gAAAAAB\/\/\/\/+AAAAAAAAAAAAAAAAAAAAAAAAAB\/\/8AAP\/\/GI0AAAAAAAAAAAAAAAAAAAAAAAH\/+AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAGAAAAAMBiNAAAAAAAAAAAAAAAAAAAAAAAD\/\/gAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAABgAAAADAYjQAAAAAAAAAAAAAAAAAAAAAAA\/\/4AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAYAAAAAwGI0AAAAAAAAAAAAAAAAAAAAAAAOccAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAGAAAAAMBiLwAAAAAAAAAAAAAAAAAAAAAADjDgAAAAAAAAEAAAAAAAAAAAAAAAAAAAAAAAAAAABmIbAAAAAAAAAAAAAAAAAAAAAAAOMOAAAAAAAA\/wwGIxAAAAAAAAAAAAAAAAAAAAAAAOOPAAAAAAAH\/w8AAAAAAAAAAAAAAAAAAAAAAAAAAAABxiNQAAAAAAAAAAAAAAAAAAAAAADjjwAAAAAAH\/8PgAAAAAAAAAAAAAAAAAAAAAAAAAAAD8AAB\/wGI1AAAAAAAAAAAAAAAAAAAAAAAPv\/AAAAAAA\/\/w\/AAAAAAAAAAAAAAAAAAAAAAAAAAAB\/AAAf\/4YjUAAAAAAAAAAAAAAAAAAAAAAA+f4AAAAAAH\/\/D+AAAAAAAAAAAAAAAAAAAAAAAAAAA\/gAADwDxiNQAAAAAAAAAAAAAAAAAAAAAAB5\/gAAAAAAf\/8P8AAAAAAAAAAAAAAAAAAAAAAAAAAP2AAAMADGI1AAAAAAAAAAAAAAAAAAAAAAABj8AAAAAAD\/\/w\/4AAAAAAAAAAAAAAAAAAAAAAAAAH4YAABgAEYjUAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP\/\/D\/gAAAAAAAAAAAAAAAAAAAAAAAAAeBgAAGAAZiNQAAAAAAAAAAAAAAAAAAAAADwAAAAAAAAB\/h8D+AAAAAAAAAAAAAAAAAAAAAAAAABwGAAAYABGI1AAAAAAAAAAAAAAAAAAAAAAPAAAAAAAAAH8HwH8AAAAAAAAAAAAAAAAAAAAAAAAAH4YAAAwAMYjUAAAAAAAAAAAAAAAAAAAAAA8AAAAAAAAA\/gfAPwAAAAAAAAAAAAAAAAAAAAAAAAAD9gAADwHxiNQAAAAAAAAAAAAAAAAAAAAADwAAAAAAAAD+B8A\/AAAAAAAAAAAAAAAAAAAAAAAAAAB+AAAH\/8GI1AAAAAAAAAAAAAAAAAAAAAAPAAAAAAAAAPwHwD8AAAAAAAAAAAAAAAAAAAAAAAAAAB\/AAAD\/AYjEAAAAAAAAAAAAAAAAAAAAAA8AAAAAAAAA\/AfAPwAAAAAAAAAAAAAAAAAAAAAAAAAAA\/GIxAAAAAAAAAAAAAAAAAAAAAAP\/\/+AAAAAAPwHwD8AAAAAAAAAAAAAAAAAAAAAAAAAAABxiHAAAAAAAAAAAAAAAAAAAAAAD\/\/\/gAAAAAD8B8A\/AYhwAAAAAAAAAAAAAAAAAAAAAA\/\/\/4AAAAAA\/gfAPwGIcAAAAAAAAAAAAAAAAAAAAAAP\/\/+AAAAAAH4HwH8BiMQAAAAAAAAAAAAAAAAAAAAADwAAAAAAAAB\/h8D\/AAAAAAAAAAAAAAAAAAAAAAAAAB\/\/8YjUAAAAAAAAAAAAAAAAAAAAAA8AAAAAAAAAf+fD\/gAAAAAAAAAAAAAAAAAAAAAAAAAf\/\/AAH\/\/xiNQAAAAAAAAAAAAAAAAAAAAADwAAAAAAAAA\/\/\/\/+AAAAAAAAAAAAAAAAAAAAAAAAAB4AAAAP\/\/GI0AAAAAAAAAAAAAAAAAAAAAAPAAAAAAAAAD\/\/\/\/wAAAAAAAAAAAAAAAAAAAAAAAAAD8AAAAMBiNAAAAAAAAAAAAAAAAAAAAAADwAAAAAAAAAf\/\/\/8AAAAAAAAAAAAAAAAAAAAAAAAAAH4AAADAYjQAAAAAAAAAAAAAAAAAAAAAA8AAAAAAAAAD\/\/\/+AAAAAAAAAAAAAAAAAAAAAAAAAAAPwAAAwGI0AAAAAAAAAAAAAAAAAAAAAAPAAAAAAAAAAf\/\/\/AAAAAAAAAAAAAAAAAAAAAAAAAAAAfgAAMBiMQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAB\/\/\/AAAAAAAAAAAAAAAAAAAAAAAAAAAAA8YjEAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAH\/\/AAAAAAAAAAAAAAAAAAAAAAAAAAAAAPGIxAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAH8AAAAAAAAAAAAAAAAAAAAAAAAAAAAAfhiMQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA\/AYjAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAB+YjAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP4AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAHwYjAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP4AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAeAYjEAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP4AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAf\/\/GIxAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD+AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAH\/\/xiFwAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA\/mI1AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD+AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAADAMYjUAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP4AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAMAxiMQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA\/gAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAEYjEAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP4AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAPx\/GIxAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD+AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAH+\/xiMAAAAAAAAAAAAAAAAAAAAAAAADAAAAAA\/gAAAAAAAAAAAAAAAAAAAAAAAAAAAAAABg9iMAAAAAAAAAAAAAAAAAAAAAABw\/4AAAAA\/gAAAAAAAAAAAAAAAAAAAAAAAAAAAAAABg5iNQAAAAAAAAAAAAAAAAAAAAADx\/8AAAAA\/\/\/\/\/\/+AAAAAAAAAAAAAAAAAAAAAAAAABgYAAAAAcGI1AAAAAAAAAAAAAAAAAAAAAAfH\/4AAAAD\/\/\/\/\/\/4AAAAAAAAAAAAAAAAAAAAAAAAAGBgAAADj8YjUAAAAAAAAAAAAAAAAAAAAAB4+PgAAAAP\/\/\/\/\/\/gAAAAAAAAAAAAAAAAAAAAAAAAAYGAAAAOcxiNQAAAAAAAAAAAAAAAAAAAAAHDgOAAAAA\/\/\/\/\/\/+AAAAAAAAAAAAAAAAAAAAAAAAABgYAAABhjGI1AAAAAAAAAAAAAAAAAAAAAAcOA8AAAAD\/\/\/\/\/\/4AAAAAAAAAAAAAAAAAAAAAAAAAGBgAAAGGGYjUAAAAAAAAAAAAAAAAAAAAABw4DwAAAAP\/\/\/\/\/\/gAAAAAAAAAAAAAAAAAAAAAAAAAYGAAAAYYZiNQAAAAAAAAAAAAAAAAAAAAAHDgOAAAAA\/\/\/\/\/\/+AAAAAAAAAAAAAAAAAAAAAAAAABgYAAABjDGI1AAAAAAAAAAAAAAAAAAAAAAfHB4AAAAD\/\/\/\/\/\/4AAAAAAAAAAAAAAAAAAAAAAAAAH\/\/wAADMMYjUAAAAAAAAAAAAAAAAAAAAAA\/\/\/gAAAAP\/\/\/\/\/\/gAAAAAAAAAAAAAAAAAAAAAAAAAf\/\/AAAPzxiNQAAAAAAAAAAAAAAAAAAAAAD\/\/8AAAAA\/gAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAeOGIXAAAAAAAAAAAAAAAAAAAAAAD\/\/gAAAAD+YhcAAAAAAAAAAAAAAAAAAAAAAD\/4AAAAAP5iNQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA\/gAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAPMGI1AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD+AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAADgAAAB84YjUAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP4AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAB\/wAAAORxiNQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA\/gAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAf\/wAABxDGI1AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD+AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAADgHgAAGEEYjUAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP4AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAcAHAAAYQZiNQAAAAAAAAAAAAAAAAAAAAAH\/\/+AAAAA\/gAAAAAAAAAAAAAAAAAAAAAAAAAAAAAABgAMAABhDGI1AAAAAAAAAAAAAAAAAAAAAAf\/\/4AAAAD+AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAGAAwAADEMYjUAAAAAAAAAAAAAAAAAAAAAB\/\/\/gAAAAP4AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAwADAAAORxiNQAAAAAAAAAAAAAAAAAAAAAD\/\/+AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAADAAGAAAf+GI1AAAAAAAAAAAAAAAAAAAAAAHgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAMAAYAAAfgYjEAAAAAAAAAAAAAAAAAAAAAAOAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA4ADGIxAAAAAAAAAAAAAAAAAAAAAADgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAGAAxiMQAAAAAAAAAAAAAAAAAAAAAA4AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAABwAcYjUAAAAAAAAAAAAAAAAAAAAAAGAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAOAOAAAHDBiNQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAeDwAAA8PGI1AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA\/+AAADAMYjUAAAAAAAAAAAAAAAAAAAAAAAMAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA\/gAAAYAxiNQAAAAAAAAAAAAAAAAAAAAAA\/\/wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAABgBmI1AAAAAAAAAAAAAAAAAAAAAAH\/\/wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAGAEYjUAAAAAAAAAAAAAAAAAAAAAA\/\/\/AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAYAAAAAYAxiNQAAAAAAAAAAAAAAAAAAAAAH8D+AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAABgwAAAA4HGI1AAAAAAAAAAAAAAAAAAAAAAeAB4AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAGDAAAAB\/4YjUAAAAAAAAAAAAAAAAAAAAABwADwAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAYMAAAAD\/BiMAAAAAAAAAAAAAAAAAAAAAAHAAPAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAABgxiMAAAAAAAAAAAAAAAAAAAAAAHAAPAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAABgxiNQAAAAAAAAAAAAAAAAAAAAAHgAeAAAAAAAAAAACAAAAAAAAAAAAAAAAAAAAAAAAABgwAAAAP\/GI1AAAAAAAAAAAAAAAAAAAAAAf\/\/4AAAAAAAAAAAYAAAAAAAAAAAAAAAAAAAAAAAAAGDAAAAD\/8YjQAAAAAAAAAAAAAAAAAAAAAA\/\/\/AAAAAAAAf\/\/\/gAAAAAAAAAAAAAAAAAAAAAAAAAYMAAAAOGI0AAAAAAAAAAAAAAAAAAAAAAH\/\/gAAAAAAAf\/\/\/4AAAAAAAAAAAAAAAAAAAAAAAAAGDAAAAGBiNAAAAAAAAAAAAAAAAAAAAAAAf\/gAAAAAAAf\/\/\/+AAAAAAAAAAAAAAAAAAAAAAAAAB\/\/8AABgYjQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP\/\/\/\/gAAAAAAAAAAAAAAAAAAAAAAAAAf\/\/AAAYGI0AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD\/\/\/\/4AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACBiNAAAAAAAAAAAAAAAAAAAAAAB\/AeAAAAAAB\/\/\/\/+AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAwYjUAAAAAAAAAAAAAAAAAAAAAA\/8HgAAAAAAf\/\/\/\/gAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP\/xiNQAAAAAAAAAAAAAAAAAAAAAD\/4eAAAAAAB\/\/\/\/8AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA\/\/GIbAAAAAAAAAAAAAAAAAAAAAAf\/h4AAAAAAP8fg\/GIbAAAAAAAAAAAAAAAAAAAAAAcDx4AAAAAAP4PgPmIbAAAAAAAAAAAAAAAAAAAAAAcB54AAAAAAP4PgH2I1AAAAAAAAAAAAAAAAAAAAAAcA94AAAAAAP4HwH4AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA8wYjUAAAAAAAAAAAAAAAAAAAAABwD\/gAAAAAA\/AfAPgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAHzhiNQAAAAAAAAAAAAAAAAAAAAAHgH+AAAAAAD8B8A\/AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA5HGI1AAAAAAAAAAAAAAAAAAAAAAfwP4AAAAAAP4HwD8AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAHEMYjUAAAAAAAAAAAAAAAAAAAAAA\/AfgAAAAAA\/gfAPwAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAYQRiNQAAAAAAAAAAAAAAAAAAAAAB8A+AAAAAAD+B+A\/AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAABhBmI1AAAAAAAAAAAAAAAAAAAAAADwB4AAAAAAH8D8H8AAAAAAAAAAAAAAAAAAAAAAAAAA4AAAAGEMYjUAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAf+P\/\/wAAAAAAAAAAAAAAAAAAAAAAAAAHwAAAAMQxiNQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAB\/4\/\/\/AAAAAAAAAAAAAAAAAAAAAAAAAAhgAAAA5HGI1AAAAAAAAAAAAAAAAAAAAAAQAAAAAAAAAD\/j\/\/8AAAAAAAAAAAAAAAAAAAAAAAAACGAAAAB\/4YjUAAAAAAAAAAAAAAAAAAAAAB4AAAAAAAAAP+H\/\/gAAAAAAAAAAAAAAAAAAAAAAAAAIYAAAAB+BiMAAAAAAAAAAAAAAAAAAAAAAD+AAAAAAAAAf4f\/+AAAAAAAAAAAAAAAAAAAAAAAAAAxBiMAAAAAAAAAAAAAAAAAAAAAAAPwAAAAAAAAP4P\/8AAAAAAAAAAAAAAAAAAAAAAAAAAfBiGwAAAAAAAAAAAAAAAAAAAAAAB\/AAAAAAAAD4H\/5iNQAAAAAAAAAAAAAAAAAAAAAAAH4AAAAAAAAYB\/wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAcMGI1AAAAAAAAAAAAAAAAAAAAAAAAD4AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAADw8YjUAAAAAAAAAAAAAAAAAAAAAAAAAgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAMAxiNQAAAAAAAAAAAAAAAAAAAAAAACAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAABgDGI1AAAAAAAAAAAAAAAAAAAAAAeD\/AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAGAGYjUAAAAAAAAAAAAAAAAAAAAAB4f\/AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAf\/\/AAAYARiNQAAAAAAAAAAAAAAAAAAAAAHj\/8AAAAA\/\/\/\/\/\/+AAAAAAAAAAAAAAAAAAAAAAAAAB\/\/8AABgDGI1AAAAAAAAAAAAAAAAAAAAAAeP34AAAAD\/\/\/\/\/\/4AAAAAAAAAAAAAAAAAAAAAAAAAAADgAADgcYjUAAAAAAAAAAAAAAAAAAAAAB44HgAAAAP\/\/\/\/\/\/gAAAAAAAAAAAAAAAAAAAAAAAAAAA8AAAH\/hiNQAAAAAAAAAAAAAAAAAAAAAHjgPAAAAA\/\/\/\/\/\/+AAAAAAAAAAAAAAAAAAAAAAAAAAAPAAAAP8GIxAAAAAAAAAAAAAAAAAAAAAAeOA8AAAAD\/\/\/\/\/\/4AAAAAAAAAAAAAAAAAAAAAAAAAAB4BiMAAAAAAAAAAAAAAAAAAAAAAHjgPAAAAA\/\/\/\/\/\/+AAAAAAAAAAAAAAAAAAAAAAAAAAB5iNQAAAAAAAAAAAAAAAAAAAAAHjgOAAAAA\/\/\/\/\/\/+AAAAAAAAAAAAAAAAAAAAAAAAAADwAAAY\/\/GI1AAAAAAAAAAAAAAAAAAAAAAf\/D4AAAAD\/\/\/\/\/\/4AAAAAAAAAAAAAAAAAAAAAAAAAA8AAABj\/8YjAAAAAAAAAAAAAAAAAAAAAAB\/8PgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAHAYjAAAAAAAAAAAAAAAAAAAAAAAH8PAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAeAYjUAAAAAAAAAAAAAAAAAAAAAAAEOAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAf\/\/AAH\/\/xiNQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAB\/\/8AAf\/\/Bsqclk0AGITAAAAAAAAAAAAAAAAAAAAAAf\/\/4BiEwAAAAAAAAAAAAAAAAAAAAAH\/\/+AYhMAAAAAAAAAAAAAAAAAAAAAB\/\/\/gGITAAAAAAAAAAAAAAAAAAAAAAP\/\/4BiEQAAAAAAAAAAAAAAAAAAAAAB4GIRAAAAAAAAAAAAAAAAAAAAAADgYhEAAAAAAAAAAAAAAAAAAAAAAOBiEQAAAAAAAAAAAAAAAAAAAAAA4GIRAAAAAAAAAAAAAAAAAAAAAABgGypyWTIAYhoAAAAAAAAAAAAAAAAAAAAAAAMAAAAAAAAAABBiGwAAAAAAAAAAAAAAAAAAAAAA\/\/wAAAAAAAAP8MBiGwAAAAAAAAAAAAAAAAAAAAAB\/\/8AAAAAAAB\/8PBiGwAAAAAAAAAAAAAAAAAAAAAD\/\/8AAAAAAAH\/8PhiGwAAAAAAAAAAAAAAAAAAAAAH8D+AAAAAAAP\/8PxiGwAAAAAAAAAAAAAAAAAAAAAHgAeAAAAAAAf\/8P5iGwAAAAAAAAAAAAAAAAAAAAAHAAPAAAAAAAf\/8P9iHAAAAAAAAAAAAAAAAAAPAAAHAAPAAAAAAA\/\/8P+AYhwAAAAAAAAAAAAAAAAADwAABwADwAAAAAAP\/\/D\/gGIcAAAAAAAAAAAAAAAA\/\/\/wAAeAB4AAAAAAH+HwP4BiHAAAAAAAAAAAAAAAAP\/\/8AAH\/\/+AAAAAAB\/B8B\/AYhwAAAAAAAAAAAAAAAD\/\/\/AAA\/\/\/AAAAAAA\/gfAPwGIcAAAAAAAAAAAAAAAA\/\/\/wAAH\/\/gAAAAAAP4HwD8BiHAAAAAAAAAAAAAAAAPAPAAAAf\/gAAAAAAD8B8A\/AYhwAAAAAAAAAAAAAAAB8DwAAAAAAAAAAAAA\/AfAPwGIcAAAAAAAAAAAAAAAAPg8AAAAAAAAAAAAAPwHwD8BiHAAAAAAAAAAAAAAAAA+PAAAB\/AeAAAAAAD8B8A\/AYhwAAAAAAAAAAAAAAAAD7wAAA\/8HgAAAAAA\/gfAPwGIcAAAAAAAAAAAAAAAAAP8AAAP\/h4AAAAAAH4HwH8BiHAAAAAAAAAAAAAAAAAB\/AAAH\/4eAAAAAAB\/h8D\/AYhwAAAAAAAAAAAAAAAAAHwAABwPHgAAAAAAf+fD\/gGIcAAAAAAAAAAAAAAAAAAAAAAcB54AAAAAAD\/\/\/\/4BiGwAAAAAAAAAAAAAAAAAPAAAHAPeAAAAAAA\/\/\/\/9iGwAAAAAAAAAAAAAAAAAPAAAHAP+AAAAAAAf\/\/\/9iGwAAAAAAAAAAAAAAAP\/\/8AAHgH+AAAAAAAP\/\/\/5iGwAAAAAAAAAAAAAAAP\/\/8AAH8D+AAAAAAAH\/\/\/xiGwAAAAAAAAAAAAAAAP\/\/8AAD8B+AAAAAAAB\/\/\/BiGwAAAAAAAAAAAAAAAP\/\/8AAB8A+AAAAAAAAf\/8BiGgAAAAAAAAAAAAAAAPAPAAAA8AeAAAAAAAAB\/GINAAAAAAAAAAAAAAAAfA9iDQAAAAAAAAAAAAAAAD4PYg0AAAAAAAAAAAAAAAAPj2INAAAAAAAAAAAAAAAAA+9iDQAAAAAAAAAAAAAAAAD\/YhwAAAAAAAAAAAAAAAAAfwAAAAAAAAAAAP\/\/\/\/\/\/gGIcAAAAAAAAAAAAAAAAAB8AAAAAAAAAAAD\/\/\/\/\/\/4BiHAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA\/\/\/\/\/\/+AYhwAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP\/\/\/\/\/\/gGIcAAAAAAAAAAAAAAAAB4DwAAAAAAAAAAD\/\/\/\/\/\/4BiHAAAAAAAAAAAAAAAAAeA8AAAAAAAAAAA\/\/\/\/\/\/+AYhwAAAAAAAAAAAAAAAAHgPAAAAHgAAAAAP\/\/\/\/\/\/gGIcAAAAAAAAAAAAAAAAB4DwAAAB4AAAAAD\/\/\/\/\/\/4BiGwAAAAAAAAAAAAAAAAAAAAAAAeAAAAAAAAP\/B\/xiGwAAAAAAAAAAAAAAAAAAAAAAAeAAAAAAAAfwAP5iGwAAAAAAAAAAAAAAAAAAAAAAAeAAAAAAAA\/gAD9iHAAAAAAAAAAAAAAAAAAAAAAAAeAAAAAAAB\/AAB+AYhwAAAAAAAAAAAAAAAAABAAAAAHgAAAAAAAfgAAfwGIcAAAAAAAAAAAAAAAA8H+AAAAAAAAAAAAAH4AAD8BiHAAAAAAAAAAAAAAAAPD\/4AAAAAAAAAAAAD+AAA\/AYhwAAAAAAAAAAAAAAADx\/+AAAAAAAAAAAAA\/gAAPwGIcAAAAAAAAAAAAAAAA8fvwAAAAAAAAAAAAP4AAH8BiHAAAAAAAAAAAAAAAAPHA8AAAAAAAAAAAAD+AAB\/AYhwAAAAAAAAAAAAAAADxwHgAAAAAAAAAAAA\/wAA\/wGIcAAAAAAAAAAAAAAAA8cB4AAAAAAAAAAAAP+AAf8BiHAAAAAAAAAAAAAAAAPHAeAAAAAAAAAAAAB\/4AP\/AYhwAAAAAAAAAAAAAAADxwHAAAAAAAAAAAAAf\/\/\/\/gGIcAAAAAAAAAAAAAAAA\/+HwAAAB4AAAAAAAD\/\/\/\/4BiGwAAAAAAAAAAAAAAAP\/h8AAAB+YAAAAAAAf\/\/\/9iGwAAAAAAAAAAAAAAAA\/h4AAAH+cAAAAAAAf\/\/\/5iGwAAAAAAAAAAAAAAAAAhwAAAH+eAAAAAAAP\/\/\/xiGwAAAAAAAAAAAAAAAAAAAAAAPueAAAAAAAD\/\/\/hiGwAAAAAAAAAAAAAAAABgAAAAOOOAAAAAAAA\/\/+BiGgAAAAAAAAAAAAAAAB\/\/gAAAOOHAAAAAAAAP\/2ITAAAAAAAAAAAAAAAAP\/\/gAAA44cBiEwAAAAAAAAAAAAAAAH\/\/4AAAOOPAYhMAAAAAAAAAAAAAAAD+B\/AAADjjgGITAAAAAAAAAAAAAAAA8ADwAAA\/\/4BiEgAAAAAAAAAAAAAAAOAAeAAAH\/9iEgAAAAAAAAAAAAAAAOAAeAAAD\/9iEgAAAAAAAAAAAAAAAOAAeAAAB\/xiDgAAAAAAAAAAAAAAAPAA8GIOAAAAAAAAAAAAAAAA\/\/\/wYhEAAAAAAAAAAAAAAAB\/\/+AAADhiEQAAAAAAAAAAAAAAAD\/\/wAAAOGIRAAAAAAAAAAAAAAAAD\/8AAAA8YhEAAAAAAAAAAAAAAAAAAAAAABxiEwAAAAAAAAAAAAAAAAAAAAAAP\/+AYhMAAAAAAAAAAAAAAAAAAAAAAD\/\/gGITAAAAAAAAAAAAAAAAAAAAAAA\/\/4BiEwAAAAAAAAAAAAAAAAAAAAAAP\/+AYhoAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAABBiGwAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP8MBiGwAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAB\/8PBiJAAAAAAAAAAAAAAAAAAAAAAAOAAAAAAAAAH\/8PgAAAAAAAAAA+xiJAAAAAAAAAAAAAAAAPAAAAAAOAAAAAAAAAP\/8PwAAAAAAAAAD+5iJAAAAAAAAAAAAAAAAPgAAAAAPAAAAAAAAAf\/8P4AAAAAAAAAD+9iJAAAAAAAAAAAAAAAAPwAAAAAHAAAAAAAAAf\/8P8AAAAAAAAAHudiJAAAAAAAAAAAAAAAAP8AAAAAP\/+AAAAAAA\/\/8P+AAAAAAAAAHONiJQAAAAAAAAAAAAAAAP+AAAAAP\/+AAAAAAA\/\/8P+AAAAAAAAAGOOAYiQAAAAAAAAAAAAAAAD38AAAAD\/\/gAAAAAAf4fA\/gAAAAAAAABzjYiQAAAAAAAAAAAAAAADx\/gAAAD\/\/gAAAAAAfwfAfwAAAAAAAABznYiQAAAAAAAAAAAAAAADw\/\/AAAAAAAAAAAAA\/gfAPwAAAAAAAAA\/\/YiQAAAAAAAAAAAAAAADwP\/AAAAAAAAAAAAA\/gfAPwAAAAAAAAA\/+YiQAAAAAAAAAAAAAAADwD\/AAAAHgAAAAAAA\/AfAPwAAAAAAAAAP8YhwAAAAAAAAAAAAAAADwAfAAAAfmAAAAAAA\/AfAPwGIcAAAAAAAAAAAAAAAA8AAAAAAf5wAAAAAAPwHwD8BiJAAAAAAAAAAAAAAAAPAAAAAAH+eAAAAAAD8B8A\/AAAAAAAAABwxiJAAAAAAAAAAAAAAAAPAAAAAAPueAAAAAAD+B8A\/AAAAAAAAADw5iJAAAAAAAAAAAAAAAAAAAAAAAOOOAAAAAAB+B8B\/AAAAAAAAAHw9iJAAAAAAAAAAAAAAAAAAAAAAAOOHAAAAAAB\/h8D\/AAAAAAAAAHAdiJQAAAAAAAAAAAAAAAAAAAAAAOOHAAAAAAB\/58P+AAAAAAAAAHAOAYiUAAAAAAAAAAAAAAAAAAAAAADjjwAAAAAAP\/\/\/\/gAAAAAAAABwDgGIkAAAAAAAAAAAAAAAAAAAAAAA444AAAAAAD\/\/\/\/wAAAAAAAAAcB2IkAAAAAAAAAAAAAAAAAAAAAAA\/\/4AAAAAAB\/\/\/\/wAAAAAAAAAfD2IkAAAAAAAAAAAAAAAA\/\/\/wAAAf\/wAAAAAAA\/\/\/\/gAAAAAAAAAP\/2IkAAAAAAAAAAAAAAAA\/\/\/wAAAP\/wAAAAAAAf\/\/\/AAAAAAAAAAH\/mIkAAAAAAAAAAAAAAAA\/\/\/wAAAH\/AAAAAAAAH\/\/8AAAAAAAAAAD+GIbAAAAAAAAAAAAAAAAf\/\/wAAAAAAAAAAAAAB\/\/wGIaAAAAAAAAAAAAAAAAPAAAAAAAAAAAAAAAAAH8YhMAAAAAAAAAAAAAAAAcAAAAADgBwGIkAAAAAAAAAAAAAAAAHAAAAAA4A8AAAAAAAAAAAAAAAAAAAAAH\/2IkAAAAAAAAAAAAAAAAHAAAAAP\/\/8AAAAAAAAAAAAAAAAAAAAAP\/2IkAAAAAAAAAAAAAAAADAAAAAP\/\/4AAAAAAAAAAAAAAAAAAAAAf\/2IjAAAAAAAAAAAAAAAAAAAAAAP\/\/4AAAAD\/\/\/\/\/\/4AAAAAAAAAeYiMAAAAAAAAAAAAAAAAAAAAAA\/\/+AAAAAP\/\/\/\/\/\/gAAAAAAAABxiIwAAAAAAAAAAAAAAAABgAAAAOAAAAAAA\/\/\/\/\/\/+AAAAAAAAAHGIjAAAAAAAAAAAAAAAAH\/+AAAA4AAAAAAD\/\/\/\/\/\/4AAAAAAAAAcYiMAAAAAAAAAAAAAAAA\/\/+AAAAAAAAAAAP\/\/\/\/\/\/gAAAAAAAAA5iJAAAAAAAAAAAAAAAAH\/\/4AAAOAAAAAAA\/\/\/\/\/\/+AAAAAAAAAH\/9iJAAAAAAAAAAAAAAAAP4H8AAAOAAAAAAA\/\/\/\/\/\/+AAAAAAAAAH\/9iJAAAAAAAAAAAAAAAAPAA8AAAPAAAAAAA\/\/\/\/\/\/+AAAAAAAAAH\/9iEQAAAAAAAAAAAAAAAOAAeAAAHGITAAAAAAAAAAAAAAAA4AB4AAA\/\/4BiEwAAAAAAAAAAAAAAAOAAeAAAP\/+AYiQAAAAAAAAAAAAAAADwAPAAAD\/\/gAAAAAAAAAAAAAAAAAAAAAP\/YiQAAAAAAAAAAAAAAAD\/\/\/AAAD\/\/gAAAAAAAAAAAAAAAAAAAAA\/\/YiQAAAAAAAAAAAAAAAB\/\/+AAAAAAAAAAAAAAAAAAAAAAAAAAAB\/\/YiQAAAAAAAAAAAAAAAA\/\/8AAAAAAAAAAAAAfAAAAAAAAAAAAAB\/+YiQAAAAAAAAAAAAAAAAP\/wAAAAAAgAAAAPwfAAAAAAAAAAAAABzHYiQAAAAAAAAAAAAAAAAAAAAAAA\/\/gAAAAPwfAAAAAAAAAAAAABzDYjAAAAAAAAAAAAAAAAAAAAAAAB\/\/gAAAAPwfAAAAAAAAAAAAABzjAAAAAAAAAAAAAABgYjAAAAAAAAAAAAAAAAA\/gPAAAD\/\/gAAAAPwfAAAAAAAAAAAAABxngAAAAAAAAAAAAABgYjAAAAAAAAAAAAAAAAB\/4PAAAD\/\/gAAAAP\/\/\/\/\/\/gAAAAAAAAB5\/AAAAAAAAAAAAAAAwYjAAAAAAAAAAAAAAAAB\/8PAAADnHAAAAAP\/\/\/\/\/\/gAAAAAAAAA5\/AAAAAAAAAAAAAAAwYjEAAAAAAAAAAAAAAAD\/8PAAADjDgAAAAP\/\/\/\/\/\/gAAAAAAAAAY+AAAAAAAAAAAAAAAP\/GIxAAAAAAAAAAAAAAAA4HjwAAA4w4AAAAD\/\/\/\/\/\/4AAAAAAAAAAAAAAAAAAAAAAAAAAP\/xiIwAAAAAAAAAAAAAAAOA88AAAOOPAAAAAf\/\/\/\/\/+AAAAAAAAAHGIjAAAAAAAAAAAAAAAA4B7wAAA448AAAAB\/\/\/\/\/\/4AAAAAAAAAcYiMAAAAAAAAAAAAAAADgH\/AAAD7\/wAAAAD\/\/\/\/\/\/gAAAAAAAABxiMQAAAAAAAAAAAAAAAPAP8AAAPn+AAAAAD\/\/\/\/\/+AAAAAAAAADgAAAAAAAAAAAAAAAA8wYjEAAAAAAAAAAAAAAAD+B\/AAAB5\/gAAAAAAfAAAAAAAAAAAAAB\/\/AAAAAAAAAAAAAAAfOGIxAAAAAAAAAAAAAAAAfgPwAAAGPwAAAAAAHwAAAAAAAAAAAAAf\/wAAAAAAAAAAAAAAORxiMQAAAAAAAAAAAAAAAD4B8AAAAAAAAAAAAB8AAAAAAAAAAAAAH\/8AAAAAAAAB\/+AAAHEMYjEAAAAAAAAAAAAAAAAeAPAAAAAAAAAAAAAfAAAAAAAAAAAAAAAAAAAAAAAAAf\/gAABhBGIxAAAAAAAAAAAAAAAAAAAAAAB4AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD\/4AAAYQZiMQAAAAAAAAAAAAAAAAAAAAAB\/gAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA4AAAAGEMYjEAAAAAAAAAAAAAAAAAAAAAA\/8AAAAAAAAfAAAAAAAAAAAAAcAAAAAAAAAAAGAAAAAxDGIxAAAAAAAAAAAAAAAAAAAAAAf\/gAAAAAD8HwAAAAAAAAAAAAHDgAAAAAAAAABgAAAAORxiMQAAAAAAAAAAAAAAAAAAAAAHz4AAAAAA\/B8AAAAAAAAAAAABw4AAAAAAAAAAAAAAAB\/4YjEAAAAAAAAA4AAAAAAAAAAAB4PAAAAAAPwfAAAAAAAAAAAAAcOAAAAAAAAAAAAAAAAH4GIkAAAAAAAAAPAAAAAAAAAAAA8DwAAAAAD8HwAAAAAAAAAAAAHDgGIsAAAAAAAAAPgAAAAAAAAAAA8DwAAAAAD\/\/\/\/\/\/4AAAAAAAAHDgAAAAAAAAAAGYiwAAAAAAAAA\/gAAAAAAAAAADwPAAAAAAP\/\/\/\/\/\/gAAAAAAAAcOAAAAAAAAAAAZiMAAAAAAAAADvgAAAAAAAAAAPA8AAAAAA\/\/\/\/\/\/+AAAAAAAABw4AAAAAAAAAABgAAADBiMAAAAAAAAADn4AAAAAcAOAAPA8AAAAAA\/\/\/\/\/\/+AAAAAAAABw4AAAAAAAAAABgAAAD5iMQAAAAAAAADh\/4AAAAcAeAAPA8AAAAAAf\/\/\/\/\/+AAAAAAAAB\/\/8AAAAAAAAAB4AAAA\/AYjEAAAAAAAAA4P+AAAB\/\/\/gAD\/\/\/gAAAAH\/\/\/\/\/\/gAAAAAAAAf\/\/AAAAAAAAAY\/AAAAB8GIxAAAAAAAAAOAfgAAAf\/\/wAA\/\/\/4AAAAA\/\/\/\/\/\/4AAAAAAAAH\/\/wAAAAAAAAGf4AAAADxiMQAAAAAAAADgAYAAAH\/\/8AAP\/\/+AAAAAD\/\/\/\/\/+AAAAAAAAAAAAAAAAAAAABmGAAAAAcYjEAAAAAAAAA4AAAAAB\/\/8AAD\/\/\/gAAAAAAfAAAAAAAAAAAAAAAAAAAAAAAAAZhgAAAA\/GIxAAAAAAAAAOAAAAAABwAAAAAAAAAAAAAAHwAAAAAAAAAAAAAAAAAAAAAAAAGYYAAAB+BiMAAAAAAAAAAAAAAAAAcAAAAAAAAAAAAAAB8AAAAAAAAAAAAAAAAAAAAAAAAB\/OAAAD9iMAAAAAAAAAAAAAAAAAA8AAAAAAAAAAAAAB8AAAAAAAAAAAAAAAAAAAAAAAAAfMAAADhiDgAAAAAAAAAAAAAAAAD8wGIsAAAAAAAAAAAAAAAAA\/zgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAGAYjAAAAAAAAAAAAAAAAAD\/PAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAeAAAABgYjAAAAAAAAAA\/\/+AAAAH3PAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAfAAAABgYjAAAAAAAAAA\/\/+AAAAHHHAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAb4AAAAwYjAAAAAAAAAAf\/+AAAAHHDgAAAAAAAAAAAAAAAAAAAAAAAAAAAY+AAAAAAAAAY\/gAAAwYjEAAAAAAAAAOAAAAAAHHDgAAAAAAAAAAAAf\/\/\/\/gAAAAAAAAA5\/AAAAAAAAAYPgAAAP\/GIxAAAAAAAAABgAAAAABxx4AAAAAAAAAAAAH\/\/\/\/4AAAAAAAAAOfwAAAAAAAAGAIAAAP\/xiLAAAAAAAAAAYAAAAAAcccAAAAeAAAAAAAB\/\/\/\/+AAAAAAAAAHHcAAAAAAAABgGIlAAAAAAAAABgAAAAAB\/\/wAAAH5gAAAAAAH\/\/\/\/4AAAAAAAAAc44BiLAAAAAAAAAAAAAAAAAP\/4AAAH+cAAAAAAB\/\/\/\/+AAAAAAAAAHOOAAAAAAAAAA2IxAAAAAAAAAAAAAAAAAf\/gAAAf54AAAAAAH\/\/\/\/4AAAAAAAAAc4wAAAAAAAAH\/4AAADzBiMQAAAAAAAAAAAAAAAAD\/gAAAPueAAAAAAB\/\/\/\/+AAAAAAAAAH+cAAAAAAAAB\/+AAAB84YjEAAAAAAAAAH\/wAAAAAAAAAADjjgAAAAAAAAAP4AAAAAAAAAB\/PAAAAAAAAAf\/gAAA5HGIxAAAAAAAAAH\/\/AAAAAAAAAAA44cAAAAAAAAAAfgAAAAAAAAAPzwAAAAAAAAHDAAAAcQxiMQAAAAAAAAB\/\/4AAAAAAAAAAOOHAAAAAAAAAAD8AAAAAAAAAB44AAAAAAAAAcwAAAGEEYjEAAAAAAAAA8AeAAAAAAAAAADjjwAAAAAAAAAAfgAAAAAAAAAAAAAAAAAAAAB8AAABhBmIxAAAAAAAAAOADgAAB\/\/\/wAAA444AAAAAAAAAAH4AAAAAAAAAAAAAAAAAAAAAPAAAAYQxiMQAAAAAAAADAAcAAAf\/\/8AAAP\/+AAAAAAAAAAB\/AAAAAAAAAA+wAAAAAAAAAB4AAADEMYjEAAAAAAAAA4AOAAAH\/\/\/AAAB\/\/AAAAAAAAAAAPwAAAAAAAAA\/uAAAAAAAAAY\/AAAA5HGIxAAAAAAAAAPAHgAAB\/\/\/wAAAP\/wAAAAAAAAAAD8AAAAAAAAAP7wAAAAAAAAGf4AAAH\/hiMQAAAAAAAAB\/\/4AAAAAAAAAAB\/wAAAAAAAAAAB\/AAAAAAAAAHucAAAAAAAABmGAAAAfgYi0AAAAAAAAAf\/8AAAAAAAAAAAAAAAAAAAAAAAAfwAAAAAAAAFzjAAAAAAAAAZhgYi0AAAAAAAAAH\/wAAAAAAAAAAAAAAAAAAAAAAAA\/wAAAAAAAAdjjgAAAAAAAAZhgYi0AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAB\/wAAAAAAAA5zjAAAAAAAAAfzgYjEAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAf\/\/\/\/wAAAAAAAAxznAAAAAAAAAHzAAAAAcGIxAAAAAAAAAD8DgAAB\/\/\/wAA\/\/\/4AAAAAAH\/\/\/\/8AAAAAAAAAP\/wAAAAAAAAAAAAAAOPxiMQAAAAAAAAB\/g4AAAf\/\/8AAP\/\/+AAAAAAB\/\/\/\/+AAAAAAAAAD\/4AAAAAAAAAAAAAADnMYjEAAAAAAAAA\/8OAAAH\/\/\/AAD\/\/\/gAAAAAAf\/\/\/\/gAAAAAAAAAP8AAAAAAAAAAAAAABhjGIxAAAAAAAAAPHjgAAB\/\/\/wAA\/\/\/4AAAAAAH\/\/\/\/wAAAAAAAAAAAAAAAAAAAAH\/4AAAYYZiMQAAAAAAAADg84AAAAAAAAAAAAAAAAAAAB\/\/\/\/4AAAAAAAAAAAAAAAAAAAAB\/+AAAGGGYjEAAAAAAAAAwHOAAAAAAAAAAAAAAAAAAAAf\/\/\/8AAAAAAAAAAAAAAAAAAAAAP\/gAABjDGIxAAAAAAAAAOA7gAAAAAAAAAAAAAAAAAAAH\/\/\/wAAAAAAAAAH\/\/wAAAAAAAADgAAAAMwxiMQAAAAAAAADwP4AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAB\/\/8AAAAAAAAAYAAAAD88YjEAAAAAAAAA\/B+AAAHn\/\/AAD\/\/\/gAAAAAAAAAAAAAAAAAAAAf\/\/AAAAAAAAAGAAAAAeOGIsAAAAAAAAAHwPgAAB5\/\/wAA\/\/\/4AAAAAAAAAAAAAAAAAAAAAHngAAAAAAAAAwYiwAAAAAAAAAPAOAAAHn\/\/AAD\/\/\/gAAAAAAAAAAAAAAAAAAAAAwHAAAAAAAAADNiMQAAAAAAAAAAAAAAAef\/8AAP\/\/+AAAAAAAAAAAAAAAAAAAAAHAMAAAAAAAAB+wAAAA\/8YjEAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAABwDgAAAAAAAAP\/AAAA\/\/GIwAAAAAAAAAPAAAAAAAAAAAAAAAAAAAAAAAAP+AAAAAAAAAAAcB4AAAAAAAAA38AAAOGIwAAAAAAAAAD4AAAAAAAAAAAAAAAAAAAAAAD\/\/wAAAAAAAAAAeDwAAAAAAAAHzEAAAYGIwAAAAAAAAAAfgAAAAAAAAAAAAAIAAAAAAAP\/\/8AAAAAAAAAAP\/wAAAAAAAAH\/AAAAYGIwAAAAAAAAAAB8AAAAAAAAAAAP\/4AAAAAAAf\/\/+AAAAAAAAAAH\/gAAAAAAAAA\/8AAAYGIwAAAAAAAAAAAPgAAAB\/\/wAAAf\/4AAAAAAA\/\/\/\/AAAAAAAAAAD\/AAAAAAAAAAz8AAAIGIwAAAAAAAAAAAAgAAAB\/\/wAAA\/\/4AAAAAAB\/\/\/\/gAAAAAAAAAAAAAAAAAAAAADAAAAMGIxAAAAAAAAAAAAAAAAB\/\/wAAA\/\/4AAAAAAD\/\/\/\/wAAAAAAAAAAAAAAAAAAAAAAAAAAP\/xiMQAAAAAAAAAw\/gAAAAf\/8AAAOccAAAAAAA\/\/\/\/+AAAAAAAAAAAAAAAAAAAAAAAAAAD\/8YiQAAAAAAAAAcf8AAAAAAOAAADjDgAAAAAAf\/AP\/gAAAAAAAAAf\/YisAAAAAAAAA8\/+AAAAAAHAAADjDgAAAAAAf8AB\/gAAAAAAAAA\/\/AAAAAAAAAWIsAAAAAAAAAOODgAAAAABwAAA448AAAAAAH8AAP8AAAAAAAAAf\/wAAAAAAAAHgYjEAAAAAAAAA44PAAAAAAHgAADjjwAAAAAA\/wAAfwAAAAAAAAB4AAAAAAAAAAB4AAAAP4GIxAAAAAAAAAMOBwAAAAAB4AAA+\/8AAAAAAP4AAH8AAAAAAAAAcAAAAAAAAAAADwAAAH\/hiMQAAAAAAAADjg4AAAAf\/+AAAPn+AAAAAAD+AAA\/AAAAAAAAAHAAAAAAAAAAAACAAADgcYjEAAAAAAAAA8ceAAAAH\/\/AAAB5\/gAAAAAA\/AAAPwAAAAAAAABwAAAAAAAAAAAAAAABwDGIxAAAAAAAAAH\/\/gAAAB\/\/wAAAGPwAAAAAAPwAAD8AAAAAAAAAOAAAAAAAAAAAAAAAAYARiMQAAAAAAAAA\/\/wAAAAf\/4AAAAAAAAAAAAD+AAA\/AAAAAAAAAH\/8AAAAAAAAAAAAAAGAGYjEAAAAAAAAAD\/wAAAAAAAAAAAAAAAAAAAA\/gAAfwAAAAAAAAB\/\/AAAAAAAAAAAAAABgBGIxAA\/\/\/\/\/\/wAAAAAAAAAAAAAAAeAAAAAAAP8AAH8AAAAAAAAAf\/wAAAAAAAAB\/gAAAcAxiMQAP\/\/\/\/\/8AAAAAAAAAAAAAA4f4AAAAAAB\/AAD\/AAAAAAAAAAAAAAAAAAAAA\/8AAADgcYjEAD\/\/\/\/\/\/AAAAAAAAAAAAAA+P\/AAAAAAAf8AB\/gAAAAAAAAAAAAAAAAAAAAfPgAAAf+GIxAA4AAAADwAAAAAAB5\/\/\/gAPj\/4AAAAAAH\/wB\/4AAAAAAAAAAAAAAAAAAAAGAYAAAD+BiLQAOAAAAA8AAAAAAAef\/\/8AH48+AAAAAAA\/\/\/\/+AAAAAAAAAA+wAAAAAAAADAGBiLQAOAAAAA8D\/\/4AAAef\/\/8AHh4OAAAAAAA\/\/\/\/8AAAAAAAAAD+4AAAAAAAABgGBiLQAOAAAAA8D\/\/4AAAef\/\/8APB4OAAAAAAAf\/\/\/4AAAAAAAAAD+8AAAAAAAAB\/+BiMQAOAAAAA8B\/\/4AAAAAAAcAPB4PAAAAAAAP\/\/\/4AAAAAAAAAHucAAAAAAAAA\/8AAABwwYjEADgAAAAPAOAAAAAAAAAHADwcDwAAAAAAB\/\/\/8AAAAAAAAABzjAAAAAAAAAAAAAAA8PGIxAA4AAAADwBgAAAAAAAAAAA8HA8AAAAAAAP\/\/8AAAAAAAAAAY44AAAAAAAAAHgAAAMAxiMQAOAAAAA8AYAAAAAAAAAAAPDwPAAAAAAAA\/\/8AAAAAAAAAAHOMAAAAAAAABj8AAAGAMYjEADgAAAAPAGAAAAAAAAAAADw8DgAAAAAAAB\/4AAAAAAAAAABznAAAAAAAAAZ\/gAABgBmIxAA4AAAADwAAAAAAAAAAAAAefB4AAAAAAAAAAAAAAAAAAAAAP\/wAAAAAAAAGYYAAAYARiMQAOAAAAA8AAAAAAAAAAAAAH\/h+AAAAAAAAAAAAAAAAAAAAAD\/4AAAAAAAABmGAAAGAMYjEADgAABAPAAAAAAAAAAAAAA\/4fAAAAAAAAAAAAAAAAAAAAAAP8AAAAAAAAAZhgAAA4HGIxAA4AAAYDwB\/8AAAAAAAAAAH8HgAAAAAAAAAP8AAAAAAAAAAAAAAAAAAAAAH84AAAH\/hiMQAOAD\/8A8B\/\/wAAAAAAAAAAcBgAAAAAAADwH\/wAAAAAAAAAAAAAAAAAAAAAfMAAAA\/wYiQADgA\/+APAf\/+AAAAAAAAAAAAAAAAAAAAB8D\/+AAAAAAAAAf\/\/YiQADgBjCAPA8AeAAADwAAAAAAAAAAAAAAAD8H\/\/AAAAAAAAAf\/\/YiQADgBhDAPA4AOAAAD4AAAAAAAAAAAAAAAH8H\/\/AAAAAAAAAf\/\/Yi0ADgBhBAPAwAHAAAD8AAAAAAAAAAAAAAAP8P\/\/gAAAAAAAAfAAAAAAAAAAADjgYi0ADgBhhgPA4AOAAAD\/AAAAAAAAAAAAAAAP8P\/\/gAAAAAAAAf+AAAAAAAAAADjgYi0ADgBhhgPA8AeAAAD\/gAAAAAAAAAAAAAAf8P\/\/wAAAAAAAAH\/4AAAAAAAAADjgYiQADgA5zAPAf\/+AAAD38AAAAAAAAAAAAAAf4P4fwAAAAAAAAAP\/YiwADgAY\/APAf\/8AAADx\/gAAAAAAAAAAAAAfgf4fwAAAAAAAAAA\/AAAAAAAAAH9iMQAOAAB4A8Af\/AAAAPD\/8AAAAAAAAAAAAD+B\/g\/AAAAAAAAAAD8AAAAAAAAA\/8AAAAAcYjEADgAAAAPAAAAAAADwP\/AAAAAAAAAAAAA\/gfwPwAAAAAAAAAP\/AAAAAAAAAf\/gAAAA\/GIxAA4AIAQDwAAAAAAA8A\/wAAAB4AAAAAAAPwP8D8AAAAAAAAB\/+AAAAAAAAAGGYAAAB\/BiMQAOACAGA8A\/A4AAAPAB8AAAB+YAAAAAAD8D\/A\/AAAAAAAAB\/wAAAAAAAAADBmAAAD+AYjEADgP\/\/APAf4OAAADwAAAAAB\/nAAAAAAA\/A\/gPwAAAAAAAAfAAAAAAAAAAAYZgAAD9gGIxAA4D\/\/wDwP\/DgAAA8AAAAAAf54AAAAAAPwP4D8AAAAAAAAH\/\/wAAAAAAAAH+4AAH4YBiMQAOACAAA8Dx44AAAPAAAAAAPueAAAAAAD+H+A\/AAAAAAAAB\/\/8AAAAAAAAA\/MAAJ4GAYjEADgAgAAPA4POAAAAAAAAAADjjgAAAAAA\/h\/AfwAAAAAAAAf\/\/AAAAAAAAAAAAAOcBgGIxAA4AAAQDwMBzgAAAAAAAAAA44cAAAAAAH\/\/wH8AAAAAAAAAAAAAAAAAAAAB\/gADH4YBiMQAOAAAGA8DgO4AAAD+A8AAAOOHAAAAAAB\/\/8H\/AAAAAAAAAAAAAAAAAAAAA\/8AAgP2AYjEADgA\/\/APA8D+AAAB\/4PAAADjjwAAAAAAf\/+D\/gAAAAAAAAAAAAAAAAAAAAfPgAAAfgGIxAA4AP\/gDwPwfgAAAf\/DwAAA444AAAAAAD\/\/g\/4AAAAAAAAAAAAAAAAAAAAGAYAAAB\/BiMQAOAGMIA8B8D4AAAP\/w8AAAP\/+AAAAAAA\/\/4P8AAAAAAAAAAAAAAAAAAAADAGAAAAD8YjEADgBhDAPAPAOAAADgePAAAB\/\/AAAAAAAH\/8D\/AAAAAAAAAAAAAAAAAAAAAYBgAAAAHGItAA4AYQQDwAAAAAAA4DzwAAAP\/wAAAAAAA\/+A\/gAAAAAAAAAAAAAAAAAAAAH\/4GItAA4AYYYDwAAAAAAA4B7wAAAH\/AAAAAAAAP8A\/AAAAAAAAAAAAAAAAAAAAAD\/wGIbAA4AYYYDwAAAAAAA4B\/wAAAAAAAAAAAAAAAA8GIOAA4AOcwDwAAAAAAA8A\/wYiQADgAY\/APAAAAAAAD+B\/AAAAAAAAAAAAAAAAAAAAAAAAAAAAPsYiQADgAAeAPAAAAAAAB+A\/AAD\/\/\/gAAAAAAAAAAAAAAAAAAAAA\/uYiQADgAAAAPAAAAAAAA+AfAAD\/\/\/gAAAAAAAAAAAAAAAAAAAAA\/vYiwADgAAAAPAAAAAAAAeAPAAD\/\/\/gAAAAAAAAAAAAAAAAAAAAB7nAAAAAAAAAYBiLAAOAAAAA8AAAAAAAAAAAAAP\/\/+AAAAAAAAAAAAAAAAAAAAAHOMAAAAAAAAB4GIsAA4AHDADwAP\/gAAAAAAAAAAcB4AAAAAAAAAAAAAAAAAAAAAY44AAAAAAAAHwYiwADgA8PAPAB\/+AAAAAAAAAADgDgAAAAAAAAAAAAAAAAAAAABzjAAAAAAAAAb5iLQAOADAMA8AP\/4AAAAAAAAAAOAPAAAAAAAAAAAAAAAAAAAAAHOcAAAAAAAABj+BiMQAOAGAMA8APAAAAAAAAAAAAOAPAAAAAAAAAAAAAAAAAAAAAD\/8AAAAAAAABg+AAB\/\/8YjEADgBgBgPADgAAAAAAAAAAADgDwAAAAAAAAAAAAAAAAAAAAA\/+AAAAAAAAAYAgAAP\/\/GIwAA4AYAQDwA4AAAAAAAAAAAA8B4AAAAAAAAAAAAAAAAAAAAAD\/AAAAAAAAAGAAAAAwGIwAA4AYAwDwA4AAAAAAAAAAAA\/\/4AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAwGIwAA4AOBwDwAcAAAAAAAAAAAAf\/wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAwGIwAA4AH\/gDwA\/\/gAAAAAAAAAAP\/gAAAAAAAAAAAAAAAAAAAAAHDAAAAAAAAAAAAAAAwGItAA4AD\/ADwA\/\/gAAAAAAAAAAD+AAAAAAAAAAAAAAAAAAAAAAPDgAAAAAAAAH\/4GItAA4AAAADwA\/\/gAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAfDwAAAAAAAAH\/4GItAA4AAAADwAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAcBwAAAAAAAAD\/4GIxAA4GP\/wDwAAAAAAB5\/\/wAAAAAAAAAAAAAAAQAAAAAAAAAAAcA4AAAAAAAADgAAAAAMBiMQAOBj\/8A8AAAAAAAef\/8AAAAAAAAAAAAAAP8MAAAAAAAAAAHAOAAAAAAAAAYAAAAADAYjEADgAAAAPAAAAAAAHn\/\/AAAA\/\/gAAAAAAAf\/DwAAAAAAAAABwHAAAAAAAAAGAAAAf\/\/GIxAA4AAAADwAAAAAAB5\/\/wAAAf\/4AAAAAAAf\/w+AAAAAAAAAAfDwAAAAAAAAB\/gAAH\/\/xiMQAOB\/\/8A8AB\/gAAAAAAAAAAP\/+AAAAAAAP\/8PwAAAAAAAAAD\/8AAAAAAAAA\/8AAA4DAYjEADgf\/\/APAB\/8AAAAAAAAAAD\/\/gAAAAAAH\/\/D+AAAAAAAAAAf+AAAAAAAAAfPgAADAwGIxAA4AAAADwAf\/gAAAAAAAAAA4AAAAAAAAB\/\/w\/wAAAAAAAAAD+AAAAAAAAAGAYAAAcMBiMQAOAAAAA8APB4AAAAAAAAAAOAAAAAAAAA\/\/8P+AAAAAAAAAAAAAAAAAAAADAGAAADjAYjEADgAAAAPADgOAAAH\/\/\/AAADgAAAAAAAAP\/\/D\/gAAAAAAAAAAAAAAAAAAAAYBgAAAOwGIxAA4AD\/ADwA4BwAAB\/\/\/wAAA4AAAAAAAAH+HwP4AAAAAAAAAD\/wAAAAAAAAH\/4AAAB8BiMQAOAD\/4A8AOAcAAAf\/\/8AAAHAAAAAAAAB\/B8B\/AAAAAAAAAD\/8AAAAAAAAA\/8AAAAHAYiQADgA4HAPADgOAAAH\/\/\/AAAD\/\/gAAAAAA\/gfAPwAAAAAAAAB\/\/Yi0ADgBwDAPAD4eAAAADgPAAAD\/\/gAAAAAA\/gfAPwAAAAAAAAB\/+AAAAAAAAAHBgYjEADgBgBgPAB\/+AAAAHAHAAAD\/\/gAAAAAA\/AfAPwAAAAAAAABzHAAAAAAAAAfhgAAD4DGIxAA4AYAYDwAP\/AAAABwB4AAA\/\/4AAAAAAPwHwD8AAAAAAAAAcwwAAAAAAAAH8YAAD\/AxiMQAOAGAMA8AB\/AAAAAcAeAAAAAAAAAAAAD8B8A\/AAAAAAAAAHOMAAAAAAAABhmAAAw4MYjEADgAwDAPAAAAAAAAHAHgAAAAAAAAAAAA\/AfAPwAAAAAAAABxngAAAAAAAAwdgAAYGDGIxAA4AHDgDwAAAAAAAB4DwAAAAAIAAAAAAP4HwD8AAAAAAAAAefwAAAAAAAAGD4AAGAwxiMQAOAD\/\/48ADHwAAAAf\/8AAAD\/+AAAAAAB+B8B\/AAAAAAAAADn8AAAAAAAAB4eAABgMMYjEADgA\/\/+PABz+AAAAD\/+AAAB\/\/gAAAAAAf4fA\/wAAAAAAAAAY+AAAAAAAAAODgAAYBjGIxAA4AAAADwAc\/gAAAAf\/AAAA\/\/4AAAAAAH\/nw\/4AAAAAAAAAAAAAAAAAAAAEAAAAHAcxiMQAOAAAAA8AOO4AAAAB\/AAAAP\/+AAAAAAA\/\/\/\/+AAAAAAAAAAAAAAAAAAAAB4AAAA4DsYjEADgAAAAPADnHAAAAAAAAAADnHAAAAAAAP\/\/\/\/AAAAAAAAAADgAAAAAAAAAB4AAAHgfGIxAA4AP\/wDwA5xwAAAAAAAAAA4w4AAAAAAB\/\/\/\/wAAAAAAAAAH\/AAAAAAAAAADwAAAYDxiLQAOAAB4A8AOcYAAAAAAAAAAOMOAAAAAAAP\/\/\/4AAAAAAAAAD\/4AAAAAAAAAACBiLQAOAAAMA8AP84AAAAf\/8AAAOOPAAAAAAAH\/\/\/wAAAAAAAAAD\/8AAAAAAAAAB4BiLwAOAAAMA8AP54AAAAf\/8AAAOOPAAAAAAAB\/\/\/AAAAAAAAAAHgcAAAAAAAABj8AAB2IwAA4AAAQDwAfngAAAB\/\/wAAA+\/8AAAAAAAB\/\/wAAAAAAAAAAcA4AAAAAAAAGf4AAHwGIwAA4AAAYDwAPHAAAAB\/\/wAAA+f4AAAAAAAAH8AAAAAAAAAAAcA4AAAAAAAAGYYAAG4GIwAA4AAAwDwAAAAAAAAADgAAAef4AAAAAAAAAAAAAAAAAAAAAcAwAAAAAAAAGYYAAGOGIwAA4AP\/wDwAAAAAAAAABwAAAGPwAAAAAAAAAAAAAAAAAAAAAOBwAAAAAAAAGYYAAGHmIxAA4AP\/wDwO\/\/gAAAAABwAAAAAAAAAAAAAAAAP4AAAAAAAAAf\/\/gAAAAAAAH84AAGB8BiMQAOAAAAA8Dv\/4AAAAAAeAAAAAAAAAAAAAAAAD+AAAAAAAAAH\/\/4AAAAAAAAfMAABgH8YjEADgAAAAPA7\/+AAAAAAHgAADgAAAAAAAAAAAA\/gAAAAAAAAB\/\/+AAAAAAAAAAAAAYAPGIxAA4AAAADwAAAAAAAB\/\/4AAA4AAAAAAAAAAAAP4AAAAAAAAAf\/\/gAAAAAAAB\/gAAGAARiLwAOAD8AA8AAAAAAAAf\/8AAAPAAAAAAAAAAAAD+AAAAAAAAAAAAAAAAAAAAA\/8AABmIvAA4A\/+ADwAAAAAAAB\/\/wAAAcAAAAAAAAAAAAP4AAAAAAAAAAAAAAAAAAAAHz4AAGYi0ADgPx8APAAf+AAAAH\/+AAAD\/\/gAAAAAAAAAA\/gAAAAAAAAAY+AAAAAAAAAYBgYi0ADgOAOAPAB\/+AAAAAAAAAAD\/\/gAAAAAAAAAA\/gAAAAAAAAA5\/AAAAAAAAAwBgYjEADgYAHAPAD\/+AAAAAAAAAAD\/\/gAAAAAAAAAA\/gAAAAAAAAA5\/AAAAAAAAAYBgAAD4DGIxAA4GAAwDwA\/\/AAAAADwAAAA\/\/4AAAAAAAAAAP4AAAAAAAAAcdwAAAAAAAAH\/4AAD\/AxiMQAOBgAMA8AOY4AAAAD8wAAAAAAAAAAAAAAAAD+AAAAAAAAAHOOAAAAAAAAA\/8AAAw4MYjEADgYADAPADmGAAAAD\/OAAAAAAAAAAAAAAAAA\/gAAAAAAAABzjgAAAAAAAAQAAAAYGDGIxAA4GAAwDwA5xgAAAA\/zwAAAAAAAAAAAAAAAAP4AAAAAAAAAc4wAAAAAAAAHgAAAGAwxiMQAOBgAMA8AOM8AAAAfc8AAAY\/+AAAAAAAAAAD+AAAAAAAAAH+cAAAAAAAAAHgAABgMMYjEADgYADAPADz+AAAAHHHAAAeP\/gAAAAAAAAAA\/gAAAAAAAAB\/PAAAAAAAAAAPAAAYBjGIxAA4GAAwDwAc\/gAAABxw4AAPj\/4AAAAAAAAAAP4AAAAAAAAAPzwAAAAAAAAAAIAAHAcxiMQAOB\/\/8A8ADHwAAAAccOAAD4\/4AAAAAAAAAAD+AAAAAAAAAB44AAAAAAAAAAAAAA4DsYjEADgf\/\/APAAAAAAAAHHHgAB8OPAAAAAAAAAAA\/gAAAAAAAAAAAAAAAAAAAAf\/gAAHgfGIxAA4AAAADwAA8AAAABxxwAAeDh4AAAAAAAAAAP4AAAAAAAAAABwAAAAAAAAH\/4AAAYDxiLQAOAAAAA8A4\/wAAAAf\/8AAPA4OAAAAAAAAAAD+AAAAAAAABw4cAAAAAAAAA\/+BiMAAOAAAAA8B4\/4AAAAP\/4AAPA4OAAAAA\/\/\/\/\/\/+AAAAAAAABw4cAAAAAAAAA4AAAADBiMQAOAAAAA8D594AAAAH\/4AAPAAPAAAAA\/\/\/\/\/\/+AAAAAAAABw4cAAAAAAAAAYAAABDCAYjEADgAAAAPA4cOAAAAA\/4AADwADwAAAAP\/\/\/\/\/\/gAAAAAAAAcOHAAAAAAAAAGAAAAfwgGIxAA4AAAADwOHBgAAAAAAAAA8AA8AAAAD\/\/\/\/\/\/4AAAAAAAAHDhwAAAAAAAAAAAAAB\/4BiMQAOAAAAA8HBwcAAAAAAAAAPAAeAAAAA\/\/\/\/\/\/+AAAAAAAABw4cAAAAAAAAAA4AAAD\/8YjEADgAAAAPBwcHAAAAAAAAAB4AHgAAAAP\/\/\/\/\/\/gAAAAAAAAcOHAAAAAAAAAffgAAAwvGIxAA4AAAADwcODgAAAAAAAAAfAH4AAAAD\/\/\/\/\/\/4AAAAAAAAHDhwAAAAAAAAH+4AAGMIBiMQAOAAAAA8Djg4AAAef\/\/4AD+f8AAAAA\/\/\/\/\/\/+AAAAAAAABw4cAAAAAAAABnGAAB\/CAYjEADgAAAAPA94eAAAHn\/\/\/AAf\/+AAAAAAAAAAAAAAAAAAAAAf\/\/AAAAAAAAAwxgAAD\/wGIxAA4AAAADwP+PgAAB5\/\/\/wAD\/\/AAAAAAAAAAAAAAAAAAAAAH\/\/wAAAAAAAAGAYAAAN\/xiMQAP\/\/\/\/\/8B\/DwAAAef\/\/8AAf\/gAAAAAAAAAAAAAAAAAAAAB\/\/8AAAAAAAAB4eAAADCcYjEAD\/\/\/\/\/\/APg4AAAAAAAHAAA\/AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAOHAAAAwgGIxAA\/\/\/\/\/\/wAAAAAAAAAABwAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAIBiBgAP\/\/\/\/\/8AbKnJZNjYAYiQAD\/\/\/\/\/\/AAAAAAAAAAAAAAAIAAAAAAAAAAAAAAAAAAAAAAHOcYiQAD\/\/\/\/\/\/BwAAAAAAAAcAAAAAAAAAAAAAAAAAAAAAAAAAAAD\/8YiQAD\/\/\/\/\/\/B8AAAAAAH\/\/wAAAAAAAAAAAAAAAAcAAAAAAAAAD\/4Yi0ADgAAAAPB+AAAAAAH\/\/wAAAP8AAAAAAAAAAAcAAAAAAAAAA\/wAAAAAAAAB\/+AYi0ADgAAAAPB3gAAAAAH\/\/wAAA\/\/AAAAAAAAAAAcAAAAAAAAAAAAAAAAAAAAA\/+AYjEADgAAAAPBz\/AAAAAH\/\/wAAB\/\/gAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAQAAAB\/\/8GIxAA4AAAADwcP4AAAAA8HAAAAcAYAAAAAAAAAAAAAAAAAAAAAcMAAAAAAAAAEAAAAP\/\/BiLwAOAAAAA8HA+AAAAAHhwAAAGAGAAAAAAAAAAAAAAAAAAAAAPDgAAAAAAAAAAAAAA2IvAA4AAAADwcAAAAAAAHnAAAAYAYAAAAAAAAAAHAAAAAAAAAB8PAAAAAAAAAAAAAADYi8ADgAAAAPBwAAAAAAAH8AAAB4HgAAAAAAAAAAcAAAAAAAAAHAcAAAAAAAAAAAAAANiLwAOAAAAA8AAAAAAAAAPwAAAD\/8AAAAAAAAAABwAAAAAAAAAcA4AAAAAAAAACAAAA2IsAA4AAAADwAAAAAAAAAPAAAAH\/gAAAAAAAAAAAAAAAAAAAABwDgAAAAAAAAAIYiwADgAAAAPAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAHAcAAAAAAAAAAhiLAAOAAAEA8AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAfDwAAAAAAAAACGIwAA4AAAYDwAAAAAAAAAHAAAAHgYAAAAAAAAAAAAAAAAAAAAA\/\/AAAAAAAAAAAAAAAA2IwAA4AP\/wDwf\/4AAAAB\/\/8AAAP4YAAAAAAAAAAHAAAAAAAAAAf+AAAAAAAAAAeAAAAA2IxAA4AP\/gDwf\/4AAAAB\/\/8AAAf8YAAAAAAAAAAHAAAAAAAAAAP4AAAAAAAAAQ\/AAAf\/\/BiMQAOAGMIA8BgAAAAAAf\/\/AAAGHGAAAAAAAAAABwAAAAAAAAAAAAAAAAAAAAEYYAAH\/\/wYjAADgBhDAPAYAAAAAAH\/\/wAABg5gAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAABECAAA4DYjAADgBhBAPAIAAAAAADwcAAABgdgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAABECAAAMDYjAADgBhhgPAIAAAAAAB4cAAAB4PgAAAAAAAAAAAAAAAAAAAAB\/8AAAAAAAABGCAAAHDYjAADgBhhgPAAAAAAAAAecAAAA8HgAAAAAAABwAAAAAAAAAAAD\/8AAAAAAAAB+GAAADjYjAADgA5zAPAAAAAAAAAH8AAAAcDgAAAAAAABwAAAAAAAAAAAH\/8AAAAAAAAAOMAAAA7YjAADgAY\/APAP8AAAAAAD8AAAAAAAAAAAAAABwAAAAAAAAAAAHgAAAAAAAAAAAAAAAAfYjAADgAAeAPA\/\/AAAAAAA8AAAAAAAAAAAAAABwAAAAAAAAAAAHAAAAAAAAAABgAAAAAHYisADgAAAAPB\/\/gAAAAAAAAAAAAAAAAAAAAABwAAAAAAAAAAAHAAAAAAAAAAB2IsAA4AAAADwcAYAAAAAAAAAAAAAAAAAAAAAAf\/\/AAAAAAAAABwAAAAAAAAAATAYjEADgAgBAPBgBgAAAAAAAAAAAAAAAAAAAAAB\/\/8AAAAAAAAADgAAAAAAAAABHAAAAPgMGIxAA4AIAYDwYAYAAAAAAAAAAAAAAAAAAAAAAf\/\/AAAAAAAAAB\/\/AAAAAAAAAQeAAAP8DBiMQAOA\/\/8A8HgeAAAAAA4HAAAAAAAAAAAAAAHAAAAAAAAAAAAf\/wAAAAAAAAEB4AADDgwYjEADgP\/\/APA\/\/AAAAAAOBwAAAAAAAAAAAAABwAAAAAAAAAAAH\/8AAAAAAAABAAAABgYMGIxAA4AIAADwH\/gAAAAADgcAAAAOAAAAAAAAAcAAAAAAAAAAAAAAAAAAAAAAAQAAAAYDDBiMQAOACAAA8AAAAAAAAAAAAAAADgAAAAAAAAHAAAAAAAAAAAAAAAAAAAAAAAABAAAGAwwYjEADgAABAPAAAAAAAAAAAAAAAA4AAAAAAAABwAAAAAAAAAAAAAAAAAAAAAAAAQAABgGMGIxAA4AAAYDwHgYAAAAAAAAAAAAOAAAAAAAAAcAAAAAAAAAAAAP\/AAAAAAAAAf\/gAAcBzBiMQAOAD\/8A8D+GAAAAAAAAAAAADgAAAAAAAAAAAAAAAAAAAAAP\/wAAAAAAAAHBAAADgOwYjEADgA\/+APB\/xgAAAAHB\/AAAAAAAAAAAAAAAAAAAAAAAAAAAH\/8AAAAAAAAAYQAAAeB8GIxAA4AYwgDwYcYAAAABw\/4AAAAAAAAAAAAAAAAAAAAAAAAAAB\/+AAAAAAAAADEAAABgPBiLAAOAGEMA8GDmAAAAAcf\/AAAAAAAAAAAAAAAAAAAAAAAAAAAcxwAAAAAAAAANGIsAA4AYQQDwYHYAAAABxw8AAAAAAAAAAAAAAAAAAAAAAAAAABzDAAAAAAAAAAcYi8ADgBhhgPB4PgAAAAHHBwAAAAAAAAAAAAAAAAAAAAAAAAAAHOMAAAAAAAAAAwAABxiLwAOAGGGA8DweAAAAAcYDgAAAAAAAAAAAAAAAAAAAAAAAAAAcZ4AAAAAAAAAHgAAH2IwAA4AOcwDwHA4AAAABxgOAAAAAAAAAAAAAAAP\/AAAAAAAAAB5\/AAAAAAAAAQ\/AAAbgGIwAA4AGPwDwAAAAAAAB5wcAAAAAAAAAAAAAAA\/\/AAAAAAAAAA5\/AAAAAAAAARhgAAY4GIwAA4AAHgDwAAAAAAAB\/48AAAAegAAAAAAAAB\/\/AAAAAAAAAAY+AAAAAAAAARAgAAYeGIwAA4AAAADwcAAAAAAA\/44AAAB+wAAAAAAAAB\/+AAAAAAAAABwAAAAAAAAAARAgAAYH2IxAA4AAAADwHwAAAAAAA4wAAAB+4AAAAAAAABzHAAAAAAAAABwAAAAAAAAAARggAAYB\/BiMQAOAAAAA8AHgAAAAAAAAAAAA5mAAAAAAAAAcwwAAAAAAAAAcAAAAAAAAAAH4YAAGADwYjEADgAcMAPAAPgAAAAAAAAAAAMZgAAAAAAAAHOMAAAAAAAAADgAAAAAAAAAAOMAABgAEGIvAA4APDwDwAAIAAAAAP\/gAAADmYAAAAAAAABxngAAAAAAAAB\/\/AAAAAAAAAAAAAAYYi8ADgAwDAPAAAAAAAAD\/\/gAAAHbgAAAAAAAAHn8AAAAAAAAAH\/8AAAAAAAAAAAAABhiJAAOAGAMA8BD4AAAAAP\/\/AAAAf+AAAAAAAAAOfwAAAAAAAAAf\/xiGwAOAGAGA8DH8AAAAAeAPAAAAP8AAAAAAAAAGPhiMQAOAGAEA8HPeAAAAAcAHAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAH\/4AAA+AwYjEADgBgDAPBjBgAAAAGAA4AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA\/+AAA\/wMGIxAA4AOBwDwYwYAAAABwAcAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAEAAAAMODBiMQAOAB\/4A8GMGAAAAAeAPAAAA4AAAAAAAAAH\/\/wAAAAAAAAHAAAAAAAAAAABAAAAGBgwYjEADgAP8APB5zgAAAAD\/\/wAAAGAAAAAAAAAB\/\/8AAAAAAAABw4AAAAAAAAAAAAAABgMMGIxAA4AAAADwP\/wAAAAA\/\/4AAAAwAAAAAAAAAf\/\/AAAAAAAAAcOAAAAAAAAAADEAAAYDDBiMQAOBj\/8A8A\/4AAAAAD\/4AAAA\/+AAAAAAAAAAAAAAAAAAAAHDgAAAAAAAAAHxAAAGAYwYjEADgY\/\/APAAAAAAAAAAAAAAAP\/gAAAAAAAAAAAAAAAAAAABw4AAAAAAAAAAP4AABwHMGIxAA4AAAADwAAAAAAAAAAAAAAD\/4AAAAAAAAAAAAAAAAAAAAcOAAAAAAAAAADHgAAOA7BiMQAOAAAAA8AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAHDgAAAAAAAAAGxAAAB4HwYjEADgf\/\/APAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAABw4AAAAAAAAAA\/wAAAGA8GItAA4H\/\/wDwAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAcOAAAAAAAAAADfgGIwAA4AAAADwf\/4AAAAAAAAAAADgAAAAAAAAAAAAAAAAAAAAAf\/\/AAAAAAAAADEgAAAwGIwAA4AAAADwf\/4AAAAAAAAAAABgAAAAAAAAAAAAAAAAAAAAAf\/\/AAAAAAAAAAEAAAQwmIwAA4AAAADwGAAAAAABwAAAAAAwAAAAAAAAAAAAAAAAAAAAAf\/\/AAAAAAAAAAAAAAfwmIwAA4AD\/ADwGAAAAAAB4AAAAAD\/4AAAAAAAAAPsAAAAAAAAAAAAAAAAAAAAAAAAAAH\/mIxAA4AP\/gDwCAAAAAAB8AAAAAD\/4AAAAAAAAA\/uAAAAAAAAAAAAAAAAAAAAAAAAAAA\/\/BiMQAOADgcA8AgAAAAAAfwAAAAA\/+AAAAAAAAAP7wAAAAAAAAAAAAAAAAAAAAAAAAAAMLwYjAADgBwDAPAAAAAAAAHfAAAAAAAAAAAAAAAAHucAAAAAAAAAAAAAAAAAAAADAAAABjCYjAADgBgBgPAAAAAAAAHPwAAAAAAAAAAAAAAAHOMAAAAAAAAAAAAAAAAAAAAA4AAAB\/CYjAADgBgBgPAP8AAAAAHD\/wAAAB6AAAAAAAAAGOOAAAAAAAAAAAAAAAAAAAAAHAAAAP\/YjEADgBgDAPA\/\/AAAAAHB\/wAAAH7AAAAAAAAAHOMAAAAAAAAAAAAAAAAAAAAAA4AAADf8GIxAA4AMAwDwf\/4AAAABwD8AAAB+4AAAAAAAABznAAAAAAAAAAAAAAAAAAAAAABgAAAwnBiMAAOABw4A8HAGAAAAAcADAAAA5mAAAAAAAAAP\/wAAAAAAAAAAAAAAAAAAAAAAAAAAMJiMAAOAD\/\/48GAGAAAAAcAAAAAAxmAAAAAAAAAP\/gAAAAAAAAAGPgAAAAAAAAAAAAAAAJiJAAOAD\/\/48GAGAAAAAcAAAAAA5mAAAAAAAAAD\/AAAAAAAAAAOfxiJAAOAAAAA8HgeAAAAAAAAAAAAduAAAAAAAAAAAAAAAAAAAAAOfxiLAAOAAAAA8D\/8AAAAAAAAAAAAf+AAAAAAAAAAAAAAAAAAAAAcdwAAAAAAAAA\/GIsAA4AAAADwH\/gAAAAAAAAAAAA\/wAAAAAAAAAAAAAAAAAAAABzjgAAAAAAAAP\/Yi0ADgA\/\/APAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAHOOAAAAAAAABgGAYi0ADgAAeAPAeBgAAAAAAAAAAAGBgAAAAAAAB\/\/8AAAAAAAAAHOMAAAAAAAABACAYi0ADgAADAPA\/hgAAAAH\/\/wAAAGBgAAAAAAAB\/\/8AAAAAAAAAH+cAAAAAAAABACAYi0ADgAADAPB\/xgAAAAH\/\/wAAA\/\/gAAAAAAAB\/\/8AAAAAAAAAH88AAAAAAAABgCAYjEADgAABAPBhxgAAAAD\/\/wAAA\/\/gAAAAAAAAB54AAAAAAAAAD88AAAAAAAAB4eAAAA8wGIxAA4AAAYDwYOYAAAAAcAAAAABgAAAAAAAAAAwHAAAAAAAAAAeOAAAAAAAAAH+AAAAfOBiMQAOAAAMA8GB2AAAAADAAAAAAYAAAAAAAAAAcAwAAAAAAAAAAAAAAAAAAAAAAAAAAORwYjEADgA\/\/APB4PgAAAAAwAAAAAAAAAAAAAAAAHAOAAAAAAAAAAAAAAAAAAAAAB4AAAHEMGIxAA4AP\/wDwPB4AAAAAMAAAAAAAAAAAAAAAABwHgAAAAAAAAAPsAAAAAAAAAQ\/AAABhBBiMQAOAAAAA8BwOAAAAAAAAAAAA4AAAAAAAAAAeDwAAAAAAAAAP7gAAAAAAAAEYYAAAYQYYjEADgAAAAPAAAAAAAAAAAAAAAGAAAAAAAAAAD\/8AAAAAAAAAD+8AAAAAAAABECAAAGEMGIxAA4AAAADwAAAAAAAAAAAAAAAwAAAAAAAAAAf+AAAAAAAAAB7nAAAAAAAAARAgAAAxDBiMQAOAAAAA8AAAAAAAAD\/4AAAA\/+AAAAAAAAAD\/AAAAAAAAABc4wAAAAAAAAEYIAAAORwYjEADgA\/AAPAAAAAAAAD\/\/gAAAP\/gAAAAAAAAAAAAAAAAAAAB2OOAAAAAAAAB+GAAAB\/4GIxAA4A\/+ADwAAAAAAAA\/\/8AAAD\/4AAAAAAAAAAAAAAAAAAAA5zjAAAAAAAAADjAAAAH4BiJAAOA\/HwA8AAAAAAAAeAPAAAAAAAAAAAAAAAAAAAAAAAAAAMc5xiMQAOA4A4A8AAAAAAAAcAHAAAAAAAAAAAAAAAAAAAAAAAAAAAP\/wAAAAAAAAAwYAAH\/\/wYjEADgYAHAPAAAAAAAAGAA4AAAB\/gAAAAAAAAAAAAAAAAAAAAD\/4AAAAAAAAAMGAAB\/\/8GIkAA4GAAwDwAAAAAAABwAcAAAB\/4AAAAAAAAAAAAAAAAAAAAAP8GITAA4GAAwDwAAAAAAAB4A8AAAB\/4BiLAAOBgAMA8AH+AAAAAP\/\/AAAA7MAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA\/GIxAA4GAAwDwB\/4AAAAA\/\/4AAADkYAAAAAAAAAPsAAAAAAAAAAAAAAAAAAAAAP\/AAAAP4BiMQAOBgAMA8Af+AAAAAD\/4AAAA5mAAAAAAAAAP7gAAAAAAAAH\/\/wAAAAAAAAGEYAAAH\/gYjEADgYADAPAOAAAAAAAAAAAAAPZgAAAAAAAAD+8AAAAAAAAB\/\/8AAAAAAAABBiAAATgcGIxAA4GAAwDwDgAAAAAAAAAAAAB34AAAAAAAAB7nAAAAAAAAAf\/\/AAAAAAAAAQYgAANwDBiMQAOB\/\/8A8AYAAAAAAH4HAAAAM+AAAAAAAAAc4wAAAAAAAAAHngAAAAAAAAEGIAAOYAQYjEADgf\/\/APAGAAAAAAD\/BwAAAAAAAAAAAAAAGOOAAAAAAAAADAcAAAAAAAABjOAADGAGGIxAA4AAAADwD\/4AAAAB\/4cAAAAAAAAAAAAAABzjAAAAAAAAABwDAAAAAAAAAPjAAA5gBBiMQAOAAAAA8A\/+AAAAAePHAAAD8AAAAAAAAAAc5wAAAAAAAAAcA4AAAAAAAAA\/AAADcAwYjEADgAAAAPAP\/gAAAAHB5wAAB\/gAAAAAAAAAD\/8AAAAAAAAAHAeAAAAAAAAA\/8AAATgcGIxAA4AAAADwAAAAAAABgOcAAAf8AAAAAAAAAA\/+AAAAAAAAAB4PAAAAAAAAAYBgAAAf+BiMQAOAAAAA8AAAAAAAAcB3AAAHHAAAAAAAAAAD\/AAAAAAAAAAP\/wAAAAAAAAEAIAAAD+AYi0ADgAAAAPAAAAAAAAHgfwAABhwAAAAAAAAAAAAAAAAAAAAAB\/4AAAAAAAABACAYi0ADgAAAAPAB+AAAAAH4PwAABhwAAAAAAAAAAAAAAAAAAAAAA\/wAAAAAAAABgCAYjAADgAAAAPAD\/AAAAAD4HwAABhwAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAB4eAAAGAYjAADgAAAAPAH\/gAAAAB4BwAABhwAAAAAAAAB\/\/8AAAAAAAAAAAAAAAAAAAAAf4AAAGAYjAADgAAAAPAGDgAAAAAAAAAAB\/\/gAAAAAAAB\/\/8AAAAAAAAAAAAAAAAAAAAAAAAAADAYjAADgAAAAPAOBgAAAAAAAAAAB\/\/gAAAAAAAB\/\/8AAAAAAAAAB\/8AAAAAAAAAAAAAADAYjEADgAAAAPAOBgAAAAHgAAAAB\/\/gAAAAAAAAAAAAAAAAAAAAD\/8AAAAAAAAAAAAAAA\/8GIxAA\/\/\/\/\/\/wDgYAAAAAfAAAAAAAAAAAAAAAAAAAAAAAAAAAAB\/\/AAAAAAAAAAAAAAA\/\/BiIwAP\/\/\/\/\/8AeeAAAAAA\/AAAAAAAAAAAAAAAAAAAAAAAAAAAAeGIrAA\/\/\/\/\/\/wB\/wAAAAAAPgAAAAAAAAAAAAAAZwAAAAAAAAAABwAAAAAAAAAAZiMQAAAAAAAAAH4AAAAAAAfAAAAAAAAAAAAAAHcAAAAAAAAAAAcAAAAAAAAAAHAAAAAIAQYjEAAAAAAAAAAAAAAAAAAAQAAAAAAAAAAAAAB\/\/8AAAAAAAAAHAAAAAAAAAABMAAAACAGGIxAAAAAAAAAAAAAAAABwAAAAAAAAAAAAAAAAf\/\/AAAAAAAAAA4AAAAAAAAAARwAAAP\/\/BiMQAAAAAAAAAM8AAAAAeAAAAAAAAAAAAAAAAD\/\/wAAAAAAAAAf\/wAAAAAAAAEHgAAD\/\/wYjAAAAAAAAAAHfgAAAAHwAAAAAAAAAAAAAAAAHAAAAAAAAAAAH\/8AAAAAAAABAeAAACAYjAAAAAAAAAAHfgAAAAH8AAAAAB6AAAAAAAAAHAAAAAAAAAAAH\/8AAAAAAAABAAAAACAYisAAAAAAAAAOZgAAAAHfAAAAAH7AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAABGIxAAAAAAAAADOYAAAABz8AAAAB+4AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP\/BiMQAAAAAAAAA7mAAAAAcP\/AAAA5mAAAAAAAAGcAAAAAAAAAAAAAAAAAAAAAAAAAAAAP\/wYjAAAAAAAAAAHxgAAAAHB\/wAAAMZgAAAAAAAB3AAAAAAAAAAAA+wAAAAAAAAAAAAAADgYjAAAAAAAAAAHzgAAAAHAPwAAAOZgAAAAAAAB\/\/8AAAAAAAAAD+4AAAAAAAAAAAAAAGAYjAAAAAAAAAADzAAAAAHAAwAAAHbgAAAAAAAB\/\/8AAAAAAAAAD+8AAAAAAAAB\/+AAAGAYjAAAAAAAAAAAAAAAAAHAAAAAAH\/gAAAAAAAA\/\/8AAAAAAAAAHucAAAAAAAAA\/+AAAGAYjAAAAAAAAAAAAAAAAAHAAAAAAD\/AAAAAAAAAHAAAAAAAAAAAHOMAAAAAAAAAQAAAACAYjAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAHAAAAAAAAAAAGOOAAAAAAAAAQAAAADAYjEAAAAAAAABv\/gAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAHOMAAAAAAAAAAAAAAD\/8GIxAAAAAAAAAb\/4AAAAAP\/gAAAAAAAAAAAAAAAAAAAAAAAAAABznAAAAAAAAAAAAAAA\/\/BiLAAAAAAAAAG\/+AAAAAP\/+AAAH\/+AAAAAAAAAf\/wAAAAAAAAAP\/wAAAAAAAAA\/GIsAAAAAAAAAAAAAAAAA\/\/8AAAf\/4AAAAAAAAB\/\/AAAAAAAAAA\/+AAAAAAAAAP\/Yi0AAAAAAAAAAAAAAAAHgDwAAB\/\/gAAAAAAAAH\/8AAAAAAAAAA\/wAAAAAAAABgGAYjEAAAAAAAAAB\/gAAAAHABwAAAAAAAAAAAAAAAB4AAAAAAAAAAAAAAAAAAAABACAAAA\/gGIxAAAAAAAAAB\/4AAAABgAOAAAAAAAAAAAAAAAAHAAAAAAAAAAAAAAAAAAAAAQAgAAAf+BiMQAAAAAAAAAf+AAAAAcAHAAAAAAAAAAAAAAAAAwAAAAAAAAAAAAAAAAAAAAGAIAAAOBwYjEAAAAAAAAAOzAAAAAHgDwAAAAAAAAAAAAAAAAMAAAAAAAAB\/\/8AAAAAAAAB4eAAAHAMGIxAAAAAAAAADkYAAAAA\/\/8AAAf\/4AAAAAAAAAAHgAAAAAAAAf\/\/AAAAAAAAAH+AAABgBBiMQAAAAAAAAA5mAAAAAP\/+AAAH\/+AAAAAAAAAf\/wAAAAAAAAH\/\/wAAAAAAAABwYAAAYAYYjEAAAAAAAAAPZgAAAAA\/+AAAB\/\/gAAAAAAAAH\/8AAAAAAAAB8AAAAAAAAAAA+GAAAGAEGIxAAAAAAAAAB34AAAAAAAAAAAAAAAAAAAAAAB\/+AAAAAAAAAf+AAAAAAAAAAYxgAABwDBiMQAAAAAAAAAM+AAAAAeAAAAAAAAAAAAAAAAAf+AAAAAAAAAB\/+AAAAAAAAAEEYAAAOBwYjEAAAAAAAAAAAAAAAAB8AAAAAB\/gAAAAAAAAAAAAAAAAAAAAA\/8AAAAAAAABBmAAAB\/4GIxAAAAAAAAAAAAAAAAAD8AAAAB\/4AAAAAAAAAAAAAAAAAAAAAA\/AAAAAAAAAQNgAAAP4BiLQAAAAAAAABj4AAAAAAD4AAAAf+AAAAAAAAAAAAAAAAAAAAAAPwAAAAAAAAHB4BiLQAAAAAAAADj8AAAAAAAfAAAA7MAAAAAAAAAD\/AAAAAAAAAAD\/wAAAAAAAADg4BiJAAAAAAAAAHn+AAAAAAABAAAA5GAAAAAAAAAP\/gAAAAAAAAB\/+BiMQAAAAAAAAHHGAAAAAcAAAAAA5mAAAAAAAAAP\/wAAAAAAAAH\/AAAAAAAAAAMAAAAAHDAYjEAAAAAAAABhhgAAAAHgAAAAAPZgAAAAAAAAHg8AAAAAAAAB8AAAAAAAAAAA4AAAADw8GIxAAAAAAAAAYYYAAAAB8AAAAAB34AAAAAAAABwHAAAAAAAAAf\/\/AAAAAAAAABwAAAAwDBiMQAAAAAAAAGOGAAAAAfwAAAAAM+AAAAAAAAAcA4AAAAAAAAH\/\/wAAAAAAAAADgAAAYAwYjEAAAAAHgABjhgAAAAHfAAAAAAAAAAAAAAAAHAOAAAAAAAAB\/\/8AAAAAAAAAB+AAAGAGGIxAAAAADMAAfx4AAAABz8AAAAAAAAAAAAAAABwHAAAAAAAAAAAAAAAAAAAAAQ\/AAABgBBiMQAAAAAhAAD8cAAAAAcP\/AAABj4AAAAAAAAAfDwAAAAAAAAAAAAAAAAAAAAEYYAAAYAwYjEAAAAAIQAAeGAAAAAHB\/wAAA4\/AAAAAAAAAD\/8AAAAAAAAAAAAAAAAAAAABECAAADgcGIxAAAAACEAAAAAAAAABwD8AAAef4AAAAAAAAAf+AAAAAAAAAAAAAAAAAAAAARAgAAAf+BiMQAAAAAhAAAAAAAAAAcADAAAHHGAAAAAAAAAD+AAAAAAAAAAAAAAAAAAAAAEYIAAAD\/AYi0AAAAAP\/gAAAAAAAAHAAAAABhhgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAB+GAYiwAAAAAP\/gAAAAAAAAHAAAAABhhgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAONiGwAAAAAAAAAAAAAAAAAAAAAAGOGAAAAAAAAAGPhiLAAAAAAgAAAAAAAAAAAAAAAAGOGAAAAAAAAAOfwAAAAAAAAAAAAAAAAAAAAA\/GIsAAAAACAAAAAAAAAAAfgcAAAfx4AAAAAAAAA5\/AAAAAAAAAAPsAAAAAAAAAP\/Yi0AAAAAIAAAAAAAAAAD\/BwAAA\/HAAAAAAAAAHHcAAAAAAAAAD+4AAAAAAAABgGAYi0AAAAAIAAAAAAAAAAH\/hwAAAeGAAAAAAAAAHOOAAAAAAAAAD+8AAAAAAAABACAYi0AAAAAP\/gAAAAAAAAHjxwAAAAAAAAAAAAAAHOOAAAAAAAAAHucAAAAAAAABACAYi0AAAAAIAAAAAAAAAAHB5wAAAAAAAAAAAAAAHOMAAAAAAAAAHOMAAAAAAAABgCAYi0AAAAAIAAAAAAAAAAGA5wAAAAAAAAAAAAAAH+cAAAAAAAAAGOOAAAAAAAAB4eAYiwAAAAAIAAAAAAAAAAHAdwAAAAAAAAAAAAAAH88AAAAAAAAAHOMAAAAAAAAAf5iKwAAAAAgAAAAAAAAAAeB\/AAAAAAAAAAAAAAAPzwAAAAAAAAAc5wAAAAAAAAMYiwAAAAAAIAAAAAAAAAH4PwAAAAAAAAAAAAAAB44AAAAAAAAAD\/8AAAAAAAAA4BiLAAAAAAAgAAAAAAAAAPgfAAAAAAAAAAAAAAAAAAAAAAAAAAAP\/gAAAAAAAAAcGIsAAAAAACAAAAAAAAAAeAcAAAAAAAAAAAAAAAAAAAAAAAAAAAP8AAAAAAAAAAOYi0AAAAAAAAAAAAAAAAAAAAAAAB6AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAGAYhIAAAAAAAAAAAAAAAAAAAAAAAH7YiQAAAAAHwAAAAAAAAAAAAAAAAH7gAAAAAAAAAAAAAAAAAAAABwwYiQAAAPAf8AAAAAAAAAAAAAAAAOZgAAAAAAAAAAAAAAAAAAAADw4Yi0AAAPB\/+AAAAAAAAAAAAAAAAMZgAAAAAAAAAAAAAAAAAAAAHw8AAAAAAAAB\/+AYi0AAAPB\/\/AAAAAAAAAAAAAAAAOZgAAAAAAAAAAAAAAAAAAAAHAcAAAAAAAAA\/+AYisAAAPD\/\/gAAAAAAAAAAAAAAAHbgAAAAAAAAAAAAAAAAAAAAHAOAAAAAAAAAWIrAAADw+D4AAAAAAAAAAAAAAAB\/4AAAAAAAAAPsAAAAAAAAABwDgAAAAAAAAFiJAAAA8fAfAAAAAAAAAAAAAAAAP8AAAAAAAAAP7gAAAAAAAAAcBxiJAAAA8eAPAAAAAAAAAAAAAAAAAAAAAAAAAAAP7wAAAAAAAAAfDxiLAAAA8OAPAAAAAAAAAAAAAAAAAAAAAAAAAAAe5wAAAAAAAAAP\/wAAAAAAAAADmIsAAADw4A8AAAAAAAAAAAAAAAAAAAAAAAAAABzjAAAAAAAAAAf+AAAAAAAAAPfYi0AAAPDwHwAAAAAAAAAAAAAAB\/\/gAAAAAAAAGOOAAAAAAAAAA\/gAAAAAAAABnGAYi0AAAP\/4PgAAAAAAAAAAAAAAB\/\/gAAAAAAAAHOMAAAAAAAAAAAAAAAAAAAABCCAYi0AAAP\/4fgAAAAAAAAAAAAAAB\/\/gAAAAAAAAHOcAAAAAAAAAAAAAAAAAAAABCCAYi0AAAP\/4fAAAAAAAAAAAAAAAAHDAAAAAAAAAD\/8AAAAAAAAAA\/8AAAAAAAABACAYi0AAAAH4eAAAAAAAAAAAAAAAAGBgAAAAAAAAD\/4AAAAAAAAAD\/8AAAAAAAABwGAYiwAAAAAAcAAAAAAAAAAAAAAAAOBgAAAAAAAAA\/wAAAAAAAAAH\/8AAAAAAAAA4diJAAAAAAAAAAAAAAAAAAAAAAAA4GAAAAAAAAAAAAAAAAAAAAAf\/hiJAAAAB4AeAAAAAAAAAAAAAAAAceAAAAAAAAAAAAAAAAAAAAAcxxiJAAAAH+AeAAAAAAAAAAAAAAAAf+AAAAAAAAAABwAAAAAAAAAcwxiJAAAAf\/AeAAAAAAAAAAAAAAAAP4AAAAAAAAAABwAAAAAAAAAc4xiJAAAAf\/geAAAAAAAAAAAAAAAAAAAAAAAAAAAABwAAAAAAAAAcZ5iJAAAA\/\/weAAAAAAAAAAAAAAAAAAAAAAAAAAAABwAAAAAAAAAefxiJAAAA8D4eAAAAAAAAAAAAAAAAAAAAAAAAAAAABwAAAAAAAAAOfxiJAAAA8B4eAAAAAAAAAAAAAAAAH+AAAAAAAAAABwAAAAAAAAAGPhiGwAAA4A8eAAAAAAAAAAAAAAAAf+AAAAAAAAAABxiGwAAB4A+eAAAAAAAAAAAAAAAAf+AAAAAAAAAABxiJAAAA4AfeAAAAAAAAAAAAAAAA4AAAAAAAAAAABwAAAAAAAAAA4BiJAAAA8AP+AAAAAAAAAAAAAAAA4AAAAAAAAAH\/\/wAAAAAAAAAH\/BiJAAAA\/wH+AAAAAAAAAAAAAAAAYAAAAAAAAAH\/\/wAAAAAAAAAP\/hiJAAAAfwH+AAAAAAAAAAAAAAAAYAAAAAAAAAH\/\/wAAAAAAAAAP\/xiJAAAAfwD+AAAAAAAAAAAAAAAA\/+AAAAAAAAAAAAAAAAAAAAAeBxiJAAAAPwA+AAAAAAAAAAAAAAAA\/+AAAAAAAAAAAAAAAAAAAAAcA5iJAAAABwAOAAAAAAAAAAAAAAAA\/+AAAAAAAAAAAAAAAAAAAAAcA5iJAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAcAxiJAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAOBxiJQAAAAAAAAAAAAAAAAAAAAAAAH+AAAAAAAAAAAAAAAAAAAAAf\/\/gYiUAAAAAAAAAAAAAAAAAAAAAAAH\/gAAAAAAAAAAAAAAAAAAAAH\/\/4GIlAAAAAAAAAAAAAAAAAAAAAAAB\/4AAAAAAAAAAAAAAAAAAAAB\/\/+BiJQAAAAAAAAAAAAAAAAAAAAAAA7MAAAAAAAAAAAAAAAAAAAAAf\/\/gYhMAAAP\/\/\/gAAAAAAAAAAAAAAAORgGITAAAD\/\/\/4AAAAAAAAAAAAAAADmYBiJAAAA\/\/\/+AAAAAAAAAAAAAAAA9mAAAAAAAAAAAAAAAAAAAAAGPhiJAAAAP\/\/+AAAAAAAAAAAAAAAAd+AAAAAAAAAAAAAAAAAAAAAOfxiJAAAAHgAAAAAAAAAAAAAAAAAAM+AAAAAAAAAAAAAAAAAAAAAOfxiJAAAAHgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAcdxiJAAAADgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAc45iJAAAADgAAAAAAAAAAAAAAAAAA4AAAAAAAAAAAAAAAAAAAAAAc45iJAAAADgAAAAAAAAAAAAAAAAAAYAAAAAAAAAAAAAAAAAAAAAAc4xiJAAAADgAAAAAAAAAAAAAAAAAAMAAAAAAAAAAAAAAAAAAAAAAf5xiJAAAAAAAAAAAAAAAAAAAAAAAA\/+AAAAAAAAAAAAAAAAAAAAAfzxiJAAAAAAAAAAAAAAAAAAAAAAAA\/+AAAAAAAAAAAAAAAAAAAAAPzxiJAAAAA4AAAAAAAAAAAAAAAAAA\/+AAAAAAAAAAAAAAAAAAAAAHjhiBAAAAA4OYgQAAAPODmIkAAAD\/g4AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAHGIkAAAD\/\/4AAAAAAAAAAAAAAAACf4AAAAAAAAAAAAAAAAAAAAcOHGIkAAAA\/\/+AAAAAAAAAAAAAAAAOf4AAAAAAAAAAAAAAAAAAAAcOHGIkAAAAD\/\/8AAAAAAAAAAAAAAAOfwAAAAAAAAAAAAAAAAAAAAcOHGIkAAAADj\/8AAAAAAAAAAAAAAAcc4AAAAAAAAAAAAAAAAAAAAcOHGIkAAAADg\/8AAAAAAAAAAAAAAAYcYAAAAAAAAAAAAAAAAAAAAcOHGIkAAAD7g4MAAAAAAAAAAAAAAAYcYAAAAAAAAAAAAAAAAAAAAcOHGIkAAAD\/w4AAAAAAAAAAAAAAAAYAYAAAAAAAAAAAAAAAAAAAAcOHGIkAAAD\/\/4AAAAAAAAAAAAAAAAYAYAAAAAAAAAAAAAAAAAAAAcOHGIkAAAAf\/\/AAAAAAAAAAAAAAAAcA4AAAAAAAAAAAAAAAAAAAAcOHGIkAAAAD\/\/8AAAAAAAAAAAAAAAeB4AAAAAAAAAAAAAAAAAAAAf\/\/GIkAAAADh\/8AAAAAAAAAAAAAAAP\/wAAAAAAAAAAAAAAAAAAAAf\/\/GIkAAAADg78AAAAAAAAAAAAAAAH\/gAAAAAAAAAAAAAAAAAAAAf\/\/GISAAAADg4EAAAAAAAAAAAAAAAB+GIEAAAAAA4bKnJZMQAbDAAbKnJQMTE2MAAbKnJZMQAbDAQbKnJCGx0DBAAA';
                //}

                var ticketPrinter = new StarPrinter(LI.kiosk.devices.ticketPrinter, LI.kiosk.connector);

                ticketPrinter.pollPrint(data).then(
                    function(result) {
                        console.log('printResult: ' + result);

                        LI.kiosk.close();
                    },
                    function(error) {
                        console.error('printResult: ' + error);

                        LI.kiosk.handlePrintFailure(error, ticketPrinter);
                    }
                );
            }
        );
    },
    handlePrintFailure: function(error, printer) {
        LI.kiosk.connector.resetData(LI.kiosk.devices.ticketPrinter);
        LI.kiosk.utils.showTicketFailurePrompt(error, printer);
    },
    logPrintFailure: function(error, printer) {
        var data = {
            printer: printer.vendor + ' ' + printer.model,
            status: error.statuses.join(' | '),
            raw_status: error.raw_status,
            duplicate: error.duplicate,
            error: true
        };

        $.ajax({
            type: 'GET',
            url: LI.kiosk.urls.logPrintFailure.replace('-666', LI.kiosk.transaction.id),
            data: { directPrint: data },
            dataType: 'json',
            success: function(response) {
                if (LI.kiosk.debug) {
                    console.log(response);
                }
            },
            error: LI.kiosk.utils.error
        });
    },
    printEptReceipt: function() {

    },
    close: function() {
        LI.kiosk.utils.showFinalPrompt();
        LI.kiosk.printEptReceipt();
        LI.kiosk.cart.updateTransaction({
            transaction: {
                close: {
                    _csrf_token: LI.kiosk.CSRF,
                    id: LI.kiosk.transaction.id
                }
            }
        });
    },
    /********************* UTILS *************************/
    utils: {
        generateUUID: function() {
            var d = new Date().getTime();
            //Force letter as first character to avoid selector issues
            var uuid = 'Axxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
                var r = (d + Math.random() * 16) % 16 | 0;
                d = Math.floor(d / 16);
                return (c == 'x' ? r : (r & 0x7 | 0x8)).toString(16);
            });

            return uuid.toUpperCase();
        },
        error: function(error) {
            console.error(error);
        },
        showLoader: function() {
            $('#spinner')
                .addClass('is-active')
                .css('display', 'block')
            ;
        },
        hideLoader: function() {
            $('#spinner')
                .removeClass('is-active')
                .css('display', 'none')
            ;
        },
        flash: function(selector) {
            Waves.attach(selector);
            Waves.init();
            Waves.ripple(selector);
        },
        resetBackFab: function() {
            $('#back-fab').off('click').hide();
        },
        switchPanels: function(direction, callback) {
            LI.kiosk.utils.resetBackFab();

            $('.panel:visible').effect('slide', {
                'direction': direction,
                mode: 'hide',
                duration: 500,
                complete: callback
            });
        },
        showLocationPrompt: function() {
            LI.kiosk.dialogs.location.showModal();
            LI.kiosk.utils.setupCountryField();         
            LI.kiosk.utils.setupKeyPad($('#postcode-pad'), $('#postcode'));
            LI.kiosk.utils.addLocationDialogListeners();
        },
        setupKeyPad: function(element, input) {
            element.keypad({
                inputField: input,
                deleteButtonText: '<i class="material-icons">keyboard_backspace</i>',
                deleteButtonClass: 'mdl-cell--8-col',
                buttonTemplate: '<button class="key mdl-button mdl-js-button mdl-button--raised waves-effect mdl-cell--4-col"></button>'
            });
        },
        addLocationDialogListeners: function(dialog) {
            $(LI.kiosk.dialogs.location).on('close', function() {
                LI.kiosk.cart.updateTransaction({ 
                    transaction: {
                        _csrf_token: LI.kiosk.CSRF,
                        postalcode: $('#postcode').val(),
                        country: $('#countries').val()
                    }
                });

                LI.kiosk.checkout();
            });

            $('#countries').change(function() {
                $('#postcode').prop('disabled', true);
            });

            $('#postcode').change(function() {
                $('#countries').val(LI.kiosk.config.culture);
            });
        },
        setupCountryField: function() {
            if(LI.kiosk.countries.length == 0) {
                LI.kiosk.getCountries();
            }

            $.each(LI.kiosk.countries, function(key, country) {
                if(undefined !== country.Translation[LI.kiosk.config.userCulture]) {
                    $('<option>')
                        .addClass('country')
                        .prop('id', country.codeiso2.toLowerCase())
                        .val(country.codeiso2)
                        .html(country.Translation[LI.kiosk.config.userCulture].name)
                        .appendTo('#countries')
                    ;
                }
            });

            $('#' + LI.kiosk.config.userCulture).prop('selected', true);

            new MaterialSelectfield($('#country-field').get(0));
        },
        showPaymentPrompt: function() {
            LI.kiosk.utils.resetStatusDialog();

            $('#status-title').html(LI.kiosk.strings.payment_title);
            $('#status-ept').show();

            LI.kiosk.utils.showStatusDialog();
        },
        showPaymentFailurePrompt: function() {
            LI.kiosk.utils.resetStatusDialog();

            $('#status-title').text(LI.kiosk.strings.payment_failure);
            $('#status-actions').show();

            $(LI.kiosk.dialogs.status).on('close', function() {
                if(LI.kiosk.dialogs.status.returnValue == 'true') {
                    LI.kiosk.checkout();
                } else {
                    LI.kiosk.close();
                }
            });

            LI.kiosk.utils.showStatusDialog();
        },
        showPaymentSuccessPrompt: function() {
            LI.kiosk.utils.resetStatusDialog();

            $('#status-title').text(LI.kiosk.strings.payment_success_title);
            $('#status-details').text(LI.kiosk.strings.payment_success_details);
            
            LI.kiosk.utils.showStatusDialog();

            $('#spinner')
                .css({
                    position: 'initial',
                    margin: 'auto'
                })
                .appendTo($('#status-content'));

            LI.kiosk.utils.showLoader();
        },
        showFinalPrompt: function() {
            LI.kiosk.utils.resetStatusDialog();

            $('#status-title').text(LI.kiosk.strings.final_title);
            $('#status-details').text(LI.kiosk.strings.final_details);

            LI.kiosk.utils.showStatusDialog();
        },
        showHardwarePrompt: function(device) {
            LI.kiosk.utils.resetStatusDialog();

            $('#status-title').text(LI.kiosk.strings.hardware_title);
            $('#status-details').text(LI.kiosk.strings.hardware_details);
            $('#status-error').text(device + ' Error');
            $('#status-actions').hide();

            LI.kiosk.utils.showStatusDialog();
            
            LI.kiosk.reset();
        },
        showTicketFailurePrompt: function(error, printer) {
            LI.kiosk.utils.resetStatusDialog();

            error.duplicate = true;

            $('#status-title').text(LI.kiosk.strings.ticket_failure);
            $('#status-actions').show();

            $(LI.kiosk.dialogs.status).on('close', function() {
                if(LI.kiosk.dialogs.status.returnValue == 'true') {
                    LI.kiosk.print(true);
                } else {
                    LI.kiosk.close();
                }
            });

            LI.kiosk.logPrintFailure(error, printer);

            LI.kiosk.utils.showStatusDialog();
        },
        showStatusDialog: function() {
            if(!LI.kiosk.dialogs.status.open) {
                LI.kiosk.dialogs.status.showModal();
            }
        },
        resetStatusDialog: function() {
            LI.kiosk.utils.hideLoader();
            $('#status-actions, #status-ept').hide();
            $('#status-details, #status-title').text('');

            $(LI.kiosk.dialogs.status).off('close');

            if(LI.kiosk.dialogs.status.open) {
                LI.kiosk.dialogs.status.close();
            }
        },
        showAdminPrompt: function() {
            LI.kiosk.dialogs.admin.showModal();

            $('#execute-tasks').click(function() {
                LI.kiosk.admin.executeTaskList();
                if(LI.kiosk.dialogs.admin.open) {
                    LI.kiosk.dialogs.admin.close();
                }
            });
        },
        showPinPrompt: function() {
            LI.kiosk.dialogs.pin.showModal();       
            LI.kiosk.utils.setupKeyPad($('#pin-pad'), $('#pin-input'));
        },
        //Wait for all requests to finish even if some fail
        whenAlways: function() {
            var chain = $.extend([], arguments);

            return new $.Deferred(function(deferred) {
                var callback = function() {
                    if (chain.length == 0) {
                        deferred.resolve();

                        return;
                    }

                    var object = chain.shift();

                    $.when(object).always(callback);
                };

                callback();
            }).promise();
        }
    },
    admin: {
        clicks: 0,
        queue: {},
        init: function() {
            LI.kiosk.admin.getTaskList();

            // Trigger admin interface
            $('#admin-trigger').click(function(e) {
                if(++LI.kiosk.admin.clicks == 4) {
                    LI.kiosk.utils.showAdminPrompt();
                    LI.kiosk.admin.clicks = 0;
                }

                setTimeout(function() {
                    LI.kiosk.admin.clicks = 0;
                }, '700');
            });
        },
        // Execute admin task queue
        executeTaskList: function() {
            $.each(LI.kiosk.admin.queue, function(key, task) {
                if(task.type == 'pin') {
                    LI.kiosk.admin.executePinTask(task);

                    return false;
                }

                LI.kiosk.admin.executePrintTask(task);

                delete LI.kiosk.admin.queue[task.id];
            });
        },
        // Retrieve AdminTask list on server
        getTaskList: function() {
            return $.get(LI.kiosk.urls.getTaskList, function(data) {
                $.each(JSON.parse(data), function(key, task) {
                    LI.kiosk.admin.queue[task.id] = task;
                });
            });
        },
        executePrintTask: function(task) {
            task.receipt = Uint8Array.from(JSON.parse(task.receipt).data);

            LI.kiosk.printEptReceipt(task.receipt);

            LI.kiosk.admin.updateTask(task.id);
        },
        executePinTask: function(task) {
            if(!$(LI.kiosk.dialogs.pin).is(':visible')) {
                LI.kiosk.utils.showPinPrompt();
            }

            $(LI.kiosk.dialogs.pin).find('#pin-validate, button.key.submit').click(function(e) {
                e.preventDefault();

                LI.kiosk.admin.validatePin(task);

                LI.kiosk.admin.executeTaskList();
            });

            $(LI.kiosk.dialogs.pin).find('.close').click(function(e) {
                LI.kiosk.dialogs.pin.close();
                $('#pin-input').val('');
            });
        },
        validatePin: function(task) {
            if(task.pin == $('#pin-input').val()) {
                LI.kiosk.dialogs.pin.close();
                LI.kiosk.admin.updateTask(task.id);

                delete LI.kiosk.admin.queue[task.id];
            } else {
                $('#pin-error').show();
                $('#pin-input').val('');
                $('button.key').not('.input').click(function() {
                    $('#pin-error').hide();
                });
            }
        },
        // Flag task as done on server
        updateTask: function(taskId) {
            $.get(LI.kiosk.urls.updateTask + '?task=' + taskId, function(result) {
                console.log(result);
            });
        }
    }
}