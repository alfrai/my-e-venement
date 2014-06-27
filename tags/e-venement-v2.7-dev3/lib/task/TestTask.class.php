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
*    Copyright (c) 2006-2013 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2013 Ayoub HIDRI <ayoub.hidri AT gmail.com>
*    Copyright (c) 2006-2013 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php
class TestTask extends sfBaseTask{

  protected function configure() {
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application', 'rp'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environement', 'dev')
    ));
    $this->namespace = 'e-venement';
    $this->name = 'test';
    $this->briefDescription = 'A test task, for development purposes.';
    $this->detailedDescription = <<<EOF
      The [geo:geocode|INFO] This is a test task, for development purposes.:
      [./symfony e-venement:test --env=dev|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    $databaseManager = new sfDatabaseManager($this->configuration);
    
    $data = <<<EOF
BEGIN:VCALENDAR
VERSION:2.0
PRODID:Zimbra-Calendar-Provider
BEGIN:VEVENT
UID:696c51bc-4d0d-4683-8fd9-b755559b4499
SUMMARY:Absent (bout du monde)
ORGANIZER;CN=Baptiste SIMON - Netacces:mailto:baptiste@netacces.biz
DTSTART;VALUE=DATE:20130802
DTEND;VALUE=DATE:20130806
STATUS:CONFIRMED
CLASS:PUBLIC
X-MICROSOFT-CDO-ALLDAYEVENT:TRUE
TRANSP:OPAQUE
LAST-MODIFIED:20130401T153127Z
DTSTAMP:20130401T153127Z
SEQUENCE:0
END:VEVENT
END:VCALENDAR
EOF;
    $ical = Sabre\VObject\Reader::read($data);
    foreach ( $ical->VEVENT as $event );
      echo $event->TRANSP;
    echo "\n";
  }
}
