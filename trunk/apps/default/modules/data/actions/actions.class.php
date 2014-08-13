<?php

/**
 * data actions.
 *
 * @package    e-venement
 * @subpackage data
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class dataActions extends sfActions
{
 /**
  * Executes index action
  *
  * @param sfRequest $request A request object
  */
  public function executeIndex(sfWebRequest $request)
  {
    $this->buildDirectory();
  }
  
  public function executeFile(sfWebRequest $request)
  {
    $this->buildDirectory();
    $uri = $request->getParameter('uri','');
    
    $path = NULL;
    foreach ( $this->directory->ls() as $tmp => $name )
    if ( $name === $uri && is_file($tmp) )
    {
      $path = $tmp;
      break;
    }
    
    if ( !$path )
      throw new liFilesystemException('No such file ('.$uri.')');
    
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $path);
    
    $response = $this->getResponse();
    $response->clearHttpHeaders();
    $response->setContentType($mime);
    $response->setHttpHeader('Content-Disposition', 'attachment; filename="'.$uri.'"');
    $response->setHttpHeader('Content-Description', 'File Transfer');
    $response->setHttpHeader('Content-Transfer-Encoding', 'binary');
    $response->setHttpHeader('Content-Length', filesize($path));
    $response->setHttpHeader('Cache-Control', 'public, must-revalidate');
    $response->setHttpHeader('Pragma', 'public');
    $response->setContent(fread(fopen($path, 'r'), filesize($path)));
    $response->sendHttpHeaders();
    
    sfConfig::set('sf_web_debug', false);
    return sfView::NONE;
  }
  
  protected function buildDirectory()
  {
    $this->directory = new liDirectory(sfConfig::get('app_backup_directory', '/data/backup'));
    if ( sfConfig::has('app_backup_files_search') )
      $this->directory->restrictListedFiles(sfConfig::get('app_backup_files_search'));
    return $this->directory;
  }
}
