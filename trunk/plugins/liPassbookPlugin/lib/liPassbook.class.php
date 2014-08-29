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
      $this->configuration['design']['icon'] = sfConfig::get('sf_web_dir').'/images/logo-evenement.png';
    
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
    
    // Add primary field
    $primary = new Field('event', (string)$this->ticket->Manifestation->Event);
    $primary->setLabel('Event');
    $structure->addPrimaryField($primary);
    
    // Add secondary field
    $secondary = new Field('location', (string)$this->ticket->Manifestation->Location);
    $secondary->setLabel('Location');
    $structure->addSecondaryField($secondary);
    
    // Add auxiliary field
    $this->context->getConfiguration()->loadHelpers(array('Date'));
    $auxiliary = new Field('datetime', format_datetime($this->ticket->Manifestation->happens_at));
    $auxiliary->setLabel('Date & Time');
    $structure->addAuxiliaryField($auxiliary);
    
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
  
  protected function __($string)
  {
    if ( !$this->context )
      return $string;
    $this->context->getConfiguration()->loadHelpers(array('I18N'));
    return __($string);
  }
  protected function writeFile($path, $content = NULL)
  {
    if ( $content !== NULL )
      file_put_contents($path, $content);
    chmod($path, 0777);
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
