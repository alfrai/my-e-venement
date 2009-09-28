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
*    Copyright (c) 2006 Baptiste SIMON <baptiste.simon AT e-glop.net>
*
***********************************************************************************/
?>
<?php
  require("conf.inc.php");
  includeClass("bd/array");
  includeClass("bdRequest/group");
  includeJS('jquery');
  includeJS('tinymce/jquery.tinymce',null,'.js');
  
  $css = array($css,'styles/emailing.css');
  
	$bd	= new arrayBd (	$config["database"]["name"],
				$config["database"]["server"],
				$config["database"]["port"],
				$config["database"]["user"],
				$config["database"]["passwd"] );
  
  $from = $config['mail']['orgnom'].' <'.$config["mail"]["mailfrom"].'>';
  switch( $_POST['from'] ) {
  case 'private':
    $from = $user->getUserName().' <'.$user->getEmail().'>';
    break;
  case 'privshort':
    $from = $user->getEmail();
    break;
  }
  
  // verifying email
  if ( ($_POST['to'] || $_POST['cci']) && $_POST['content'] )
  {
    $email = array(
      'to'     => $_POST['to'] ? $_POST['to'] : $from,
      'cci'     => $_POST['cci'],
      'subject' => $_POST['subject'],
      'content' => $_POST['content'],
    );
  }
  else
  {
    $email = array('to' => $from);
    if ( $url = $_POST['url'] )
      $email['content'] = file_get_contents($url);
  }
  if ( !$_POST['subject'] && count($email) > 2 )
    $user->addAlert('Veuillez renseigner un sujet à votre email !');
  
  $sent = false;
  
  // envoi de l'email
  if ( $email && isset($_GET['send']) )
  {
    $headers =
      'From: '.$from."\r\n".
      'Bcc: '.$email['cci']."\r\n".
      'X-Mailer: e-venement/libre-informatique http://www.libre-informatique.fr/'."\r\n".
      'MIME-Version: 1.0'."\r\n".
      'Return-Path: '.$from."\r\n".
      'Errors-To: '.$from."\r\n".
      'Content-type: text/html; charset=UTF-8'."\r\n";
    
    // $headers .= 'Return-Receipt-To: '.$from."\r\n"; // accusé de réception
    
    $content =
      '<html><head><title></title>'.
      '<style type="text/css">p { margin: 0; padding: 0; }</style>'.
      '</head><body>'.
      $email['content'];
    if ( !isset($_POST['nosign']) )
    {
      $content .=
      "\r\n\r\n".
      "<p>-- <br/>".
      "\r\n".
      nl2br($from != $config['mail']['orgnom'].' <'.$config["mail"]["mailfrom"].'>' ? htmlsecure(strip_tags($from."\r\n")) : '').
      nl2br(htmlsecure($config['mail']['sign'])).
      "</p>\r\n".
      '<p class="legal">nb1: '."Si vous ne souhaitez plus recevoir d'email de notre part, contactez nous&nbsp;: ".'<a href="mailto:'.htmlsecure($from).'">'.htmlsecure($from).'</a>.</p>'.
      "\r\n".
      '<p class="html">nb2: '."Ce message s'affiche mieux dans sa version HTML...".'</p>';
    }
    $content .=
      '</body></html>';
    
    $data = array(
      'from'    => $from,
      'to'      => $email['to'],
      'subject' => $email['subject'],
      'bcc'     => $email['cci'],
      'content' => $email['content'],
      'full_c'  => $content,
      'full_h'  => $headers,
      'accountid' => $user->getId(),
      'max_recipient' => intval($config['mail']['max_recipient']),
    );
    
    if ( intval($config['mail']['max_recipient']) > 0 )
    {
      $cci = explode(',',$email['cci']);
      $email['cci'] = array();
      $buf = array();
      for ( $i = 1 ; $i <= count($cci) ; $i++ )
      {
        $buf[] = trim($cci[$i-1]);
        if ( $i % intval($config['mail']['max_recipient']) == 0 )
        {
          $email['cci'][] = implode(',',$buf);
          $buf = array();
        }
      }
      if ( ($i-1) % intval($config['mail']['max_recipient']) != 0 )
        $email['cci'][] = implode(',',$buf);
    }
    else
      $email['cci'] = array($email['cci']);
    
    if ( $bd->addRecord('email',$data) )
    {
      $sent = 0;
      foreach ( $email['cci'] as $cci )
      {
        $headers =
          'From: '.$from."\r\n".
          'Bcc: '.$cci."\r\n".
          'X-Mailer: e-venement/libre-informatique http://www.libre-informatique.fr/'."\r\n".
          'MIME-Version: 1.0'."\r\n".
          'Return-Path: '.$from."\r\n".
          'Errors-To: '.$from."\r\n".
          'Content-type: text/html; charset=UTF-8'."\r\n";
    
        $sent += mail(
          $email['to'],
          $email['subject'],
          $content,
          $headers,
          '-f '.($user->getEmail() ? $user->getEmail() : $config["mail"]["mailfrom"])
        );
      }
      $emailid = $bd->getLastSerial('email','id');
    }
    else
      $user->addAlert("Impossible d'enregistrer votre email.");
    
    if ( $sent > 0 )
    {
      $user->addAlert("Votre courriel a bien été envoyé (en ".$sent." temps)...");
      $email = array();
      $bd->updateRecordsSimple('email', array('id' => $emailid), array('sent' => 't'));
    }
    else
      $user->addAlert("Impossible d'envoyer votre courriel... veuillez le vérifier à nouveau. Si le problème persiste, contacter votre administrateur.");
    
    if ( is_array($email['cci']) )
    $email['cci'] = implode(',',$email['cci']);
  }
  
  includeLib("headers");
?>
<h1><?php echo $title ?></h1>
<?php includeLib("tree-view"); ?>
<?php require('actions.php'); ?>
<div class="body">
<h2>e-Mailing</h2>
<script type="text/javascript">
  $(document).ready(function(){
    <?php if ( $sent ): ?>
    window.location = '<?php echo htmlsecure($_SERVER['PHP_SELF']) ?>';
    <?php endif; ?> 
    $('textarea.tinymce').tinymce({
      script_url: '<?php echo htmlsecure($config['website']['root']) ?>libs/tinymce/tiny_mce.js',
      mode : "none",
      language: "fr",
      theme : "advanced",
      plugins : "table,advhr,advimage,advlink,media,paste,fullscreen,noneditable,contextmenu,inlinepopups",
      theme_advanced_buttons1_add_before : "newdocument,separator",
      theme_advanced_buttons1_add : "fontselect,fontsizeselect",
      theme_advanced_buttons2_add : "separator,forecolor,backcolor,liststyle",
      theme_advanced_buttons2_add_before: "cut,copy,paste,pastetext,pasteword,separator,",
      theme_advanced_buttons3_add_before : "tablecontrols,separator",
      theme_advanced_buttons3_add : "media,advhr,separator,fullscreen",
      theme_advanced_toolbar_location : "top",
      theme_advanced_toolbar_align : "left",
      extended_valid_elements : "hr[class|width|size|noshade],iframe[src|width|height|name|align],style",
      paste_use_dialog : false,
      theme_advanced_statusbar_location : "bottom",
      theme_advanced_resizing : true,
      theme_advanced_resize_horizontal : false,
      apply_source_formatting : true,
      force_br_newlines : true,
      force_p_newlines : false,
      relative_urls : false,
      content_css: '<?php echo htmlsecure($config['website']['root']) ?>styles/emailing.css',
    });
    $('textarea.view').tinymce({
      script_url: '<?php echo htmlsecure($config['website']['root']) ?>libs/tinymce/tiny_mce.js',
      mode : "none",
      language: "fr",
      content_css: '<?php echo htmlsecure($config['website']['root']) ?>styles/emailing.css',
      readonly: 1,
      theme : "advanced",
    });
  });
</script>

<?php if ( count($email) > 1 && $_POST['subject'] ): ?>
<form action="<?php echo htmlsecure($_SERVER['PHP_SELF']) ?>?send" method="post" class="email verify">
<p>
  <span>De: </span>
  <span class="from">
    <?php echo htmlsecure($from) ?>
    <input type="hidden" name="from" value="<?php echo htmlsecure($_POST['from']) ?>" />
  </span>
</p>
<p>
  <span>À: </span>
  <span class="to">
    <?php echo htmlsecure($email['to']) ?>
    <input type="hidden" name="to" value="<?php echo htmlsecure($email['to'] ? $email['to'] : $config['mail']['orgnom'].' <'.$config["mail"]["mailfrom"].'>') ?>" />
  </span>
</p>
<p>
  <span>Copie cachée:</span>
  <span class="cci">
    <?php echo htmlsecure($email['cci']) ?>
    <input type="hidden" name="cci" value="<?php echo htmlsecure($email['cci']) ?>" />
  </span>
</p>
<p>
  <span>Sujet:</span>
  <span class="subject">
    <?php echo htmlsecure($email['subject']) ?>
    <input type="hidden" name="subject" value="<?php echo htmlsecure($email['subject']) ?>" />
  </span>
</p>
<p>
  <span>Texte:</span>
  <span class="content">
    <textarea name="content-view" disabled="disabled" class="view"><?php echo $email['content'] ?></textarea>
    <input type="hidden" name="content" value="<?php echo htmlsecure($email['content']) ?>" />
  </span>
</p>
<p>
  <span></span>
  <span class="submit"><input type="submit" name="valid" value="Envoyer" class="submit" /> <input type="checkbox" name="nosign" value="true" /> Retirer les mentions légales</span>
</p>
</form>
<?php endif; ?>
<style type="text/css">.mceStatusbar div span { display: inline; }</style>
<form action="<?php echo htmlsecure($_SERVER['PHP_SELF']) ?>" method="post" class="email">
<p>
  <span>De: </span>
  <span>
    <select name="from" class="from">
      <option value="default"><?php echo htmlsecure($config['mail']['orgnom'].' <'.$config["mail"]["mailfrom"].'>') ?></option>
      <?php if ( $user->getEmail() ): ?>
      <option value="private" <?php if ( $_POST['from'] == 'private' ) echo 'selected="selected"'; ?>><?php echo htmlsecure($user->getUserName().' <'.$user->getEmail().'>') ?></option>
      <option value="privshort" <?php if ( $_POST['from'] == 'privshort' ) echo 'selected="selected"'; ?>><?php echo htmlsecure($user->getEmail()) ?></option>
      <?php endif; ?>
    </select>
  </span>
</p>
<p>
  <span>À: </span>
  <span><input type="text" class="to" name="to" value="<?php echo htmlsecure($email['to']) ?>" /></span>
</p>
<p>
  <span>Copie cachée:</span>
  <span><textarea class="cci" name="cci"><?php
  if ( $email['cci'] )
    echo htmlsecure($email['cci']);
  else if ( ($grpid = $_GET['grpid']) > 0 )
  {
    $request = new groupBdRequest($bd,$grpid,$user);
    while ( $rec = $request->getRecordNext() )
    {
      if ( intval($rec['orgid']) > 0 && ($rec['proemail'] || $rec['orgemail']) )
      echo htmlsecure($rec['prenom'].' '.$rec['nom'].' <'.($rec['proemail'] ? $rec['proemail'] : $rec['orgemail']).'>, ');
      elseif ( $rec['email'] )
      echo htmlsecure($rec['prenom'].' '.$rec['nom'].' <'.$rec['email'].'>, ');
    }
  }
  ?></textarea></span>
</p>
<p>
  <span>Sujet:</span>
  <span><input type="text" class="subject" name="subject" value="<?php echo htmlsecure($email['subject']) ?>" /></span>
</p>
<p>
  <span>Texte:</span>
  <span><textarea name="content" class="content tinymce"><?php echo htmlsecure($email['content']) ?></textarea></span>
</p>
<p>
  <span></span>
  <span><input type="submit" name="verif" value="Vérifier" class="submit" /></span>
</p>
</form>
<form class="url" action="<?php echo htmlsecure($_SERVER['PHP_SELF']) ?>" method="post" onsubmit="javascript: return confirm('Vous allez perdre l\'email en cours, êtes vous sûr ?')">
  <p>
    URL à charger comme modèle :
    <input type="text" name="url" value="<?php echo htmlsecure($url ? $url : '') ?>"/>
    <input type="submit" name="charger" value="charger" />
  </p>
</form>
</div>
<?php
  if ( $request ) $request->free();
	$bd->free();
	includeLib("footer");
?>
