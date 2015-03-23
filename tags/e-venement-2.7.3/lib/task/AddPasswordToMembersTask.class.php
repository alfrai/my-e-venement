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
*    Copyright (c) 2006-2011 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2011 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php
class AddPasswordToMembersTask extends sfBaseTask
{
  protected function configure() {
    $this->addArguments(array(
      new sfCommandArgument('email', sfCommandArgument::REQUIRED, 'The email to take as a template')
    ));
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application', 'rp'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environement', 'dev'),
    ));
    $this->namespace = 'e-venement';
    $this->name = 'add-password-to-members';
    $this->briefDescription = 'Add password to members';
    $this->detailedDescription = <<<EOF
      The [apmc:add-password-to-members|INFO] Add password to contacts who's got member cards and
      no password yet, and send it by email:
      [./symfony e-venement:add-password-to-members 135 --application=rp --env=prod|INFO]
      Note: In the template's body and subject (email), put contact's fields
            to display between '%%' (eg. [%%password%%|INFO])
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    sfContext::createInstance($this->configuration);
    $databaseManager = new sfDatabaseManager($this->configuration);
    
    $email_tmpl = Doctrine::getTable('Email')->createQuery('e')
      ->andWhere('e.id = ?',$arguments['email'])
      ->fetchOne();
    $this->logSection('email','Found and kept back, with subject: "'.$email_tmpl->field_subject.'"');
    
    $user = Doctrine::getTable('sfGuardUser')->createQuery('u')
      ->andWhere('u.is_super_admin = TRUE')
      ->orderBy('u.id ASC')
      ->fetchOne();
    $this->logSection('user','Found with username: "'.$user->username.'"');
    
    $contacts = Doctrine::getTable('Contact')->createQuery('c')
      ->leftJoin('c.MemberCards mc')
      ->andWhere('c.email IS NOT NULL AND c.email != ?','')
      ->andWhere('c.password IS NULL OR c.password = ?','')
      ->andWhere('mc.id IS NOT NULL')
      ->execute();
    
    foreach ( $contacts as $contact )
    {
      // password and database
      $contact->password = $this->generateStrongPassword(rand(6,9),false,'lud');
      
      try
      {
        // email To:
        $email = $email_tmpl->copy(true);
        $email->Contacts->clear();
        $email->Organisms->clear();
        $email->Professionals->clear();
        $email->Contacts[] = $contact;
        $email->sf_guard_user_id = $user->id;
        $email->User = $user;
        
        // email content
        foreach ( $contact->toArray() as $key => $value )
        if ( is_string($value) )
        {
          $email->field_subject = str_replace('%%'.$key.'%%',$value,$email->field_subject);
          $email->content = str_replace('%%'.$key.'%%',$value,$email->content);
          $email->content_text = str_replace('%%'.$key.'%%',$value,$email->content_text);
        }
        
        // email direct sending
        $email->not_a_test = true;
        $email->save();
      }
      catch ( Swift_SwiftException $e )
      {
        $this->logSection('Bad addr.',"Not sending any email to contact (with address ".$contact->email.")",NULL,'ERROR');
        $contact->save();
      }
      
      // log
      $this->logSection('New pass', $contact.' with email '.$contact->email.': '.$contact->password);
    }
    
    $this->logSection('Done', $contacts->count().' password(s) created and sent by email.');
  }
  
  protected static function generateStrongPassword($length = 9, $add_dashes = false, $available_sets = 'luds')
  {
    $sets = array();
    if(strpos($available_sets, 'l') !== false)
      $sets[] = 'abcdefghjkmnpqrstuvwxyz';
    if(strpos($available_sets, 'u') !== false)
      $sets[] = 'ABCDEFGHJKMNPQRSTUVWXYZ';
    if(strpos($available_sets, 'd') !== false)
      $sets[] = '23456789';
    if(strpos($available_sets, 's') !== false)
      $sets[] = '!@#$%&*?';
    
    $all = '';
    $password = '';
    foreach($sets as $set)
    {
      $password .= $set[array_rand(str_split($set))];
      $all .= $set;
    }
    
    $all = str_split($all);
    for($i = 0; $i < $length - count($sets); $i++)
      $password .= $all[array_rand($all)];
    
    $password = str_shuffle($password);
    
    if(!$add_dashes)
      return $password;
    
    $dash_len = floor(sqrt($length));
    $dash_str = '';
    while(strlen($password) > $dash_len)
    {
      $dash_str .= substr($password, 0, $dash_len) . '-';
      $password = substr($password, $dash_len);
    }
    $dash_str .= $password;
    return $dash_str;
  }
}

