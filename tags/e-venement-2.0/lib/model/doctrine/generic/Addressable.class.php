<?php

/**
 * Addressable
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    e-venement
 * @subpackage model
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @author     Ayoub HIDRI <ayoub.hidri AT gmail.com>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class Addressable extends PluginAddressable
{
  public function isGeolocalized()
  {
    return $this->latitude && $this->longitude;
  }
  public function updateGeolocalization()
  {
    $geoLocAddress   = $this->getGmapLocalization();
    
    $this->latitude  = $geoLocAddress->getLat();
    $this->longitude = $geoLocAddress->getLng();
    return $this;
  }
  
  public function save(Doctrine_Connection $conn = null)
  {
    if ( sfConfig::get('app_google_maps_api_keys') )
    try {
      $this->updateGeolocalization();
    }
    catch ( sfFactoryException $e )
    { }
    parent::save($conn);
  }
  
  protected function getGmapLocalization()
  {
    if ( !sfConfig::has('app_google_maps_api_keys') )
      throw new sfFactoryException("Geolocalization is not enabled in your configuration");
    
    $address = array(
      'address' => $this->getAddress(),
      'postal_code' => $this->getPostalcode(),
      'city' => $this->getCity(),
      'country' => $this->getCountry() ? $this->getCountry() : sfConfig::get('app_google_maps_default_country'), // to change by a param in app.yml
    );
    $address = implode("\n", $address);
    $gmap = new GMap();
    $geoLocAddress = $gmap->geocode($address);
    
    if ( is_null($geoLocAddress) )
      throw new sfFactoryException("It was impossible to geolocalize \"%%contact%%\"");
    
    return $geoLocAddress;
  }
  
  public function getJSSlug()
  {
    return str_replace('-','_',$this->slug);
  }
  public function getGmapString()
  {
    return
      '<a href="'.url_for($this->module.'/show?id='.$this->id).'">'.
        $this.
      '</a>';
    /*
      $this->address.'<br/>'.
      $this->postalcode.' '.$this->city.'<br/>'.
      $this->country;
    */
  }

  public static function getGmapFromQuery(Doctrine_Query $query, sfWebRequest $request)
  {
    $display = sfConfig::get('app_google_maps_display');
    $query
      ->limit(intval($display['max']))
      ->offset(intval($request->getParameter('offset')));
    
    if ( $display['notices'] )
    {
      sfContext::getInstance()->getConfiguration()->loadHelpers('I18N');
      sfContext::getInstance()->getUser()->setFlash('notice',
        __('Your map is only displaying the %%max%% first records...',array('%%max%%' => $display['max'])));
    }
    
    $gMap = new GMap();
    foreach ($query->execute() as $addressable)
      $gMap = self::getGmapFromObject($addressable,$gMap);
    
    $gMap->centerAndZoomOnMarkers();
    return $gMap;
  }
  
  public static function getGmapFromObject(Addressable $addressable, $gmap = NULL)
  {
    if ( !($gmap instanceof GMap) )
      $gmap = new GMap();
    
    try
    {
      if ( !$addressable->isGeolocalized() )
        $addressable
          ->updateGeolocalization()
          ->save();
      $marker = new GMapMarker($addressable->getLatitude(), $addressable->getLongitude(),array(),'_'.$addressable->getJSSlug().'_marker');
      $marker->addHtmlInfoWindow(new GMapInfoWindow(
        $addressable->getGmapString(),array(),'_'.$addressable->getJSSlug().'_info'
      ));
      $gmap->addMarker($marker);
    }
    catch ( sfException $e ) { }
    
    $gmap->centerAndZoomOnMarkers();
    return $gmap;
  }
}
