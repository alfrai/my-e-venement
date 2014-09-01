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
*    Copyright (c) 2006-2014 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2014 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php
use Passbook\Pass\Field;
use Passbook\Pass\Image;
use Passbook\PassFactory;
use Passbook\Pass\Barcode;
use Passbook\Pass\Structure;
use Passbook\Type\EventTicket;

class liPassbook
{
  protected $ticket, $configuration, $factory, $appleDer, $pkpass, $context = NULL;
  const MIME_TYPE = 'application/vnd.apple.pkpass';
  
  public function __construct(Ticket $ticket, array $configuration = NULL)
  {
    if ( class_exists('sfContext') && sfContext::hasInstance() )
      $this->context = sfContext::getInstance();
    
    $this->ticket = $ticket;
    $this->setConfiguration($configuration ? $configuration : sfConfig::get('project_eticketting_passbook', array()));
    $this->createFactory();
  }
  public function setConfiguration(array $configuration)
  {
    if ( !isset($configuration['certification']) )
      throw new liOnlineSaleException('No configuration set for Passbook (certificate)');
    $this->configuration = $configuration;
    
    if ( !isset($this->configuration['certification']['apple_wwdr_cer_url']) )
      $this->configuration['certification']['apple_wwdr_cer_url'] = 'http://developer.apple.com/certificationauthority/AppleWWDRCA.cer';
    if ( !isset($this->configuration['design']) )
      $this->configuration['design'] = array();
    if ( !isset($this->configuration['design']['icon']) )
      $this->configuration['design']['icon'] = sfConfig::get('sf_web_dir').'/images/logo-evenement-big.png';
    
    return $this;
  }
  
  public function getTicket()
  {
    return $this->ticket;
  }
  public function __toString()
  {
    return file_get_contents($this->pkpass->getPathname());
  }
  public function getRealFilePath()
  {
    return $this->pkpass->getPathname();
  }
  public function getPkpassPath()
  {
    return sfConfig::get('sf_app_cache_dir').'/passbook-'.$this->ticket->Manifestation->Event->slug.'-'.$this->ticket->id.'.pkpass';
  }
  public function getMimeType()
  {
    return self::MIME_TYPE;
  }
  
  public function createFactory()
  {
    $this->factory = new PassFactory(
      $this->configuration['certification']['identifier'],
      $this->configuration['certification']['team_id'],
      $this->configuration['certification']['organization'],
      $this->configuration['certification']['p12_cert_file'],
      trim(file_get_contents($this->configuration['certification']['p12_passwd_file'])),
      $this->getApplePemFilePath()
    );
    $this->factory->setOutputPath($this->getPkpassPath());
    $pass = $this->createEventTicket($this->ticket);
    
    $this->rmPkpass();
    $this->pkpass = $this->factory->package($pass);
    $this->writeFile($this->getPkpassPath());
    
    return $this;
  }
  protected function createEventTicket()
  {
    $pass = new EventTicket($this->ticket->id, (string)$this->ticket->Manifestation);
    
    // cosmetics
    foreach ( array(
      'setLogoText' => 'logo_text',
      'setBackgroundColor' => 'background_color',
    ) as $fct => $var )
    if ( isset($this->configuration['design'][$var]) )
      $pass->$fct($this->configuration['design'][$var]);
    $icon = new Image($this->configuration['design']['icon'], 'icon');
    $pass->addImage($icon);
    
    $structure = new Structure();
    
    // Organizers
    $client = sfConfig::get('project_about_client', array());
    $organizers = array();
    if ( isset($client['name']) && $client['name'] )
      $organizers[] = $client['name'];
    foreach ( $this->ticket->Manifestation->Organizers as $orga )
    if ( !in_array($orga->name, $organizers) )
      $organizers[] = $orga->name;
    
    // Participantss
    $participants = array();
    foreach ( $this->ticket->Manifestation->Participants as $part )
    if ( !in_array((string)$part, $participants) )
      $participants[] = (string)$part;
    
    // Editor
    $editor = sfConfig::get('project_about_firm', array());
    foreach ( array('name' => 'No editor', 'url' => 'http://www.e-venement.org/') as $field => $default )
    if (!( isset($editor[$field]) && $editor[$field] ))
      $editor[$field] = $default;
    
    // Legal notices
    $notices = sfConfig::get('app_tickets_mentions', array());
    
    $this->context->getConfiguration()->loadHelpers(array('Date'));
    $data = array(
      'event'     => array(
        'type'      => 'primary',
        'label'     => $this->__('Event'),
        'string'    => (string)$this->ticket->Manifestation->Event
      ),
      'location'  => array(
        'type'      => 'secondary',
        'label'     => $this->__('Location'),
        'string'    => (string)$this->ticket->Manifestation->Location
      ),
      'date'      => array(
        'type'      => 'secondary',
        'label'     => $this->__('Date & Time'),
        'string'    => format_datetime($this->ticket->Manifestation->happens_at)
      ),
      'ticket'    => array(
        'type'      => 'auxiliary',
        'label'     => $this->__('Ticket'),
        'string'    => $this->ticket.($this->ticket->seat_id ? $this->__('Seat').': '.$this->ticket->Seat : '')
      ),
      'organizers'=> array(
        'type'      => 'auxiliary',
        'label'     => $this->__('Organizers'),
        'string'    => implode(', ', $organizers)
      ),
      'participants'=> array(
        'type'      => 'auxiliary',
        'label'     => $this->__('Participants'),
        'string'    => implode(', ', $participants)
      ),
      'legal_notices' => array(
        'type'      => 'back',
        'string'    => "\n".implode("\n", $notices)
      ),
      'software'  => array(
        'type'      => 'back',
        'label'     => $this->__('Software', NULL, 'about'),
        'string'    => sfConfig::get('software_about_name')
      ),
      'editor'    => array(
        'type'      => 'back',
        'label'     => $this->__('Editor', NULL, 'about'),
        'string'    => $editor['name'].' '.$editor['url']
      ),
    );
    
    // Add conditional fields
    if ( $this->ticket->Manifestation->Location->city || $this->ticket->Manifestation->Location->address )
    {
      $data['address'] = array(
        'type'      => 'auxiliary',
        'label'     => $this->__('Address'),
        'string'    => $this->ticket->Manifestation->Location->address."\n".$this->ticket->Manifestation->Location->postalcode.' '.$this->ticket->Manifestation->Location->city."\n".$this->ticket->Manifestation->Location->country
      );
    }
    
    foreach ( $data as $name => $content )
    {
      $field = new Field($name, $content['string']);
      if ( isset($content['label']) )
        $field->setLabel($content['label']);
      switch ( $content['type'] ) {
      case 'primary':
        $structure->addPrimaryField($field);
        break;
      case 'secondary':
        $structure->addSecondaryField($field);
        break;
      case 'auxiliary':
        $structure->addAuxiliaryField($field);
        break;
      case 'back':
        $structure->addBackField($field);
        break;
      }
    }
    
    // Set pass structure
    $pass->setStructure($structure);
    
    // Add barcode
    $barcode = new Barcode(Barcode::TYPE_QR, $this->ticket->getBarcode());
    $pass->setBarcode($barcode);
    
    return $pass;
  }
  protected function getApplePemFilePath()
  {
    $path = sfConfig::get('sf_app_cache_dir').'/passbook-apple-wwdr.pem';
    
    // if a new file is needed
    if (!( file_exists($path) && filemtime($path) > strtotime('1 week ago') ))
    {
      $this->writeFile(
        $path,
        $this->convertDerToPem(file_get_contents($this->configuration['certification']['apple_wwdr_cer_url']))
      );
    }
    return $path;
  }
  
  protected function __($string, $params = array(), $catalog = 'messages')
  {
    if ( !is_array($params) )
      $params = array();
    if ( !$this->context )
      return str_replace(array_keys($params), array_values($params), $string);
    $this->context->getConfiguration()->loadHelpers(array('I18N'));
    return __($string, $params, $catalog);
  }
  public static function writeFile($path, $content = NULL)
  {
    if ( $content !== NULL )
      file_put_contents($path, $content);
    chmod($path, 0777);
  }
  protected function rmPkpass($path = NULL)
  {
    $path = $path ? $path : $this->getPkpassPath();
    if ( !is_dir($path) )
    {
      unlink($path);
      return $this;
    }
    
    $files = array_diff(scandir($path), array('.','..'));
    foreach ( $files as $file )
      $this->rmPkpass("$path/$file");
    rmdir($path);
    return $this;
  }
  protected function getContext()
  {
    return $this->context ? $this->context : false;
  }
  protected static function convertDerToPem($der)
  {
    return
      "-----BEGIN CERTIFICATE-----\n".
      chunk_split(base64_encode($der), 64, "\n").
      "-----END CERTIFICATE-----\n";
  }
}
