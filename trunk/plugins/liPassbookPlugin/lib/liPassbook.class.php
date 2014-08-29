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
  protected $ticket, $configuration, $factory, $appleDer, $pkpass;
  
  public function __construct(Ticket $ticket, array $configuration)
  {
    $this->ticket = $ticket;
    $this->setConfiguration($configuration);
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
  
  public function __toString()
  {
    return (string)$this->pkpass;
  }
  public function getPkpassPath()
  {
    return sfConfig::get('sf_module_cache_dir').'/passbook-'.$this->transaction->id.'.pkpass';
  }
  
  public function createFactory()
  {
    $this->factory = new PassFactory(
      $passbook['certification']['identifier'],
      $passbook['certification']['team_id'],
      $passbook['certification']['organization'],
      $passbook['certification']['p12_cert_file'],
      trim(file_get_contents($passbook['certification']['p12_passwd_file'])),
      $this->getApplePem()
    );
    $this->factory->setOutputPath($this->getPkpassPath());
    $pass = $this->createEventTicket($this->ticket);
    
    $this->pkpass = $this->factory->package($passes);
    
    return $this;
  }
  protected function createEventTicket()
  {
    $pass = EventTicket($this->ticket->id, $this->ticket->Manifestation);
    
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
    $primary = new Field('event', $this->ticket->Manifestation->Event);
    $primary->setLabel('Event');
    $structure->addPrimaryField($primary);
    
    // Add secondary field
    $secondary = new Field('location', $this->ticket->Manifestation->Location);
    $secondary->setLabel('Location');
    $structure->addSecondaryField($secondary);
    
    // Add auxiliary field
    $auxiliary = new Field('datetime', format_datetime($this->ticket->Manifestation->happens_at));
    $auxiliary->setLabel('Date & Time');
    $structure->addAuxiliaryField($auxiliary);
    
    $pass->setStructure($structure);
    
    return $pass;
  }
  protected function getApplePem()
  {
    if ( !$this->appleDer )
      $this->appleDer = file_get_contents($passbook['certification']['apple_wwdr_cer_url']);
    return $this->convertDerToPem($this->appleDer);
  }
  
  protected function __($string)
  {
    if ( !sfContext::hasInstance() )
      return $string;
    sfContext::getInstance()->getConfiguration()->loadHelpers(array('I18N'));
    return __($string);
  }
  protected static function convertDerToPem($der)
  {
    return
      "-----BEGIN CERTIFICATE-----\n".
      chunk_split(base64_encode($der), 64, "\n").
      "-----END CERTIFICATE-----\n";
  }
}
