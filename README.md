# eveCustom

> an add-on to e-venement to customize tickets templates  

## Installation on e-venement v2.10.2
- add files from the list below
- create models via Doctrine: 
  - files:
    - /config/customTemplate-schema.yml
    - /config/link4custom-schema.yml
  - commands:  
    - ./symfony doctrine:create-model-tables
    - ./symfony doctrine:generate-admin <!-- please review the generation files -->

- pages can be accessed at:
  - /tck.php/ticket/customize
  - ./customizeMenu
  - ./customizeSave



### list of modified files (according to GIT + checked on fresh install)
<!--commented lines are ignored, will be removed, keeping now for tracking/bug/testing -->
<!--.gitignore-->
README.md
<!--apps/default/config/app.yml.template-->
apps/default/config/routing.yml  #add customTemplate routing
<!--#apps/default/modules/tckCustom/actions/actions.class.php
#apps/default/modules/tckCustom/config/generator.yml
#apps/default/modules/tckCustom/config/view.yml
#apps/default/modules/tckCustom/lib/tckCustomGeneratorConfiguration.class.php
#apps/default/modules/tckCustom/lib/tckCustomGeneratorHelper.class.php
#apps/event/config/app.yml.template
#apps/grp/config/app.yml.template
#apps/pos/config/app.yml.template
#apps/pub/config/app.yml.template
#apps/rp/config/app.yml.template
#apps/stats/config/app.yml.template
#apps/tck/config/app.yml.template-->
apps/tck/config/app.yml  <!--##add one line 'print:custom' as a parameter to call the template on printing-->
<!--#apps/tck/config/settings.yml
#apps/tck/modules/order/config/generator.yml    ## error ? check it !-->
apps/tck/modules/ticket/actions/actions.class.php
apps/tck/modules/ticket/actions/components.class.php
apps/tck/modules/ticket/actions/customPrint.php
apps/tck/modules/ticket/actions/customize.php
<!--#apps/tck/modules/ticket/actions/print.php  ##no changed needed for now
#apps/tck/modules/ticket/actions/testing.php  ##tests done ? :p-->
apps/tck/modules/ticket/config/ticketParam.json
apps/tck/modules/ticket/templates/_customTckMenu.php
<!--#apps/tck/modules/ticket/templates/_templateTicket_html.bak.html-->
apps/tck/modules/ticket/templates/_templateTicket_html.php
apps/tck/modules/ticket/templates/_ticket_html.php
apps/tck/modules/ticket/templates/customPrintSuccess.php
<!--#apps/tck/modules/ticket/templates/customPrintSuccess.php.bak-->
apps/tck/modules/ticket/templates/customizeMenuSuccess.php
apps/tck/modules/ticket/templates/customizeSuccess.php
apps/tck/modules/ticket/templates/printCustom.php
<!--#apps/tck/modules/ticket/templates/printDirect.php
#apps/tck/modules/ticket/templates/testingSuccess.php
#apps/templates/layout.php
#apps/ws/config/app.yml.template
#config/autoload.inc.php.template
#config/databases.yml.template
#config/doctrine/tckCustom-schema.yml ##changed to customTemplate and add link4custom-->
config/doctrine/customTemplate-schema.yml
config/doctrine/link4custom-schema.yml
<!--#config/project.yml.template
#helloworld.png
#
#all lib ignored
#
#web/css/customize/.sass-cache/709b28ed0f51796f1a3f77687ced7053dbad03c5/bootstrap.scssc
#web/css/customize/bootstrap-mod.css
#web/css/customize/bootstrap-mod.css.map
#web/css/customize/bootstrap-mod.min.css
#web/css/customize/bootstrap-theme.min.css
#web/css/customize/bootstrap.min.css
#web/css/customize/bootstrap.scss-->
web/css/customize/customize.css
<!--#web/css/print-tickets.default.css
#web/customize/tThandled.js
#web/customize/templateTicket.html
#web/js/customize/bootstrap.min.js
#web/js/customize/controller.js
#web/js/customize/custom2KeepTemp.js-->
web/js/customize/customFunc.js  <!--utils to create JSON and others-->
web/js/customize/fabric.js
web/js/customize/fabric.min.js
<!--#web/js/customize/handlebars-v4.0.10.js
#web/js/customize/handlebars.runtime-v4.0.10.js
#web/js/customize/interact.js
#web/js/customize/jquery-1.11.2.js
#web/js/customize/jquery-ui.drag.js
#web/js/customize/mustache.min.js
#web/js/customize/tThandled.js
#web/js/customize/w3.js
#web/tck_dev.php-->

## princip of working
![diagram](https://raw.githubusercontent.com/gwenaelCF/e-venement/v2.10/diagramFunc.jpg)


## using tips
- make sure fabricjs is on the server.
The two files in the list are two different version, both were tested.
Please refer to fabricjs.com for demo/docs and to get other versions
- to create JSON files from object, for templating and parameters, please refer to web/js/customize/customFunc.js


## TODO
- integration to e-venement menu
- integration with symfony1/e-venement/doctrine auto-generation
- translations
- list of templates in the menu page
- code and user testings (bugs-hunting)
- code review (doc, comments, etc)
- add more options for other kind of objects (card member, maps, etc)
- background image
- custom template with non-customizable spots
- and many other things...
