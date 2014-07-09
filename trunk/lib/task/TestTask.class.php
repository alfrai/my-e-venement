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
    $pdo = Doctrine_Manager::getInstance()->getCurrentConnection()->getDbh();
    
    $q = 'SELECT * FROM tmp';
    $stmt = $pdo->prepare($q);
    $stmt->execute();
    $rows = $stmt->fetchAll();
    
    $orgs = array();
    
    foreach ( $groups = array(
      'amis' => '1003 AMIS',
      'pro'  => '1001 PRO',
      'cdn'  => 'CDN',
      'scenenationale' => 'SCENE NATIONALE',
      'theatrenational' => 'THEATRE NATIONAL',
      'sceneconventionnee' => 'SCENE CONVENTIONNEE',
      'scenedediffusion' => 'SCENE DE DIFFUSION',
      'festival' => 'FESTIVAL',
      'comcom' => 'COM COM',
      'compagnieregionale' => 'COMPAGNIE REGIONALE',
      'danse' => 'DANSE',
      'international' => '1002 INTERNATIONAL',
    ) as $tmp => $grp )
    {
      if ( $group = Doctrine_Query::create()->from('Group g')
        ->andWhere('g.name = ?', $grp)
        ->fetchOne() );
      else
      {
        $group = new Group;
        $group->name = $grp;
        $group->save();
      }
      $groups[$tmp] = $group;
    }
    
    foreach ( $rows as $c )
    if ( !$c['nom'] && !$c['prenom'] )
    {
      echo 'ERROR: '.$c['num'];
      error_log('ERROR: '.$c['num']);
    }
    else
    {
      if ( !$c['nom'] )
      {
        $c['nom'] = $c['prenom'];
        $c['prenom'] = null;
      }
      echo $c['num'].' - '.$c['nom'].' '.$c['prenom'];
      echo "\n";
      
      $contact = new Contact;
      $contact->description = $c['num'];
      $contact->title       = $c['titre'];
      $contact->firstname   = $c['prenom'];
      $contact->name        = $c['nom'];
      $contact->address     = $c['adresse'];
      $contact->postalcode  = $c['codepostal'];
      $contact->city        = $c['ville'];
      $contact->country     = $c['pays'] ? $c['pays'] : 'France';
      $contact->email       = $c['couriel'];
      
      // phonenumbers
      if ( $c['telephonefixe'] )
      {
        $pn = new ContactPhonenumber;
        $pn->name = 'Fixe personnel';
        $pn->number = $c['telephonefixe'];
        $contact->Phonenumbers[] = $pn;
      }
      if ( $c['telephoneportable'] )
      {
        $pn = new ContactPhonenumber;
        $pn->name = 'Portable personnel';
        $pn->number = $c['telephoneportable'];
        $contact->Phonenumbers[] = $pn;
      }
      
      // professional
      if ( $c['organisme'] )
      {
        if ( !isset($orgs[$c['orgville'].'--'.$c['organisme']]) )
        {
          $o = new Organism;
          $o->name = $c['organisme'];
          $o->city = $c['orgville'];
          $o->address = $c['orgadresse'];
          $o->postalcode = $c['orgcp'];
          $o->country = $c['pays'] && $c['pays'] != 'FR' ? $c['pays'] : 'France';
          
          // professional phonenumbers
          if ( $c['numeroadmin'] )
          {
            $pn = new OrganismPhonenumber;
            $pn->number = $c['numeroadmin'];
            $pn->name = 'Standard';
            $o->Phonenumbers[] = $pn;
          }
          if ( $c['fixeprof'] && $c['portableprof'] )
          {
            $pn = new OrganismPhonenumber;
            $pn->number = $c['fixeprof'];
            $pn->name = 'Fixe professionnel '.$c['titre'].' '.$c['nom'].' '.$c['prenom'];
            $o->Phonenumbers[] = $pn;
          }
          if ( $c['fixeprof'] && !$c['portableprof'] )
          {
            $contact->Professionals[0]->contact_number = $c['fixeprof'];
          }
          if ( $c['portableprof'] )
            $contact->Professionals[0]->contact_number = $c['portableprof'];
          
          $o->url = $c['siteprof'];
          $o->email = $c['courrielorga'];
          $o->description = $c['num'];
          
          $o->save();
          $orgs[$o->city.'--'.$o->name] = $o;
        }
        $contact->Professionals[0]->Organism = $orgs[$c['orgville'].'--'.$c['organisme']];

        $contact->Professionals[0]->name = $c['fonction'];
        $contact->Professionals[0]->contact_email = $c['mailprof'];
      }
      
      // groups
      if ( $c['organisme'] )
        $obj = $contact->Professionals[0];
      else
        $obj = $contact;
      foreach ( $groups as $name => $group )
      if ( $c[$name] )
        $obj->Groups[] = $group;
      
      $contact->save();
    }
  }
}
