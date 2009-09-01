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
  
  // verifying email
  $email = array('to' => $from);
  if ( $_POST['cci'] && $_POST['content'] )
  {
    $email = array(
      'to'     => $_POST['to'] ? $_POST['to'] : $from,
      'cci'     => $_POST['cci'],
      'subject' => $_POST['subject'],
      'content' => $_POST['content'],
    );
  }
  if ( !$_POST['subject'] && count($email) > 1 )
    $user->addAlert('Veuillez renseigner un sujet à votre email !');
  
  $sent = false;
  
  // envoi de l'email
  if ( $email && isset($_GET['send']) )
  {
    $headers =
      'From: '.$from."\r\n".
      'Bcc: '.$email['cci']."\r\n".
      'X-Mailer: e-venement/v1'."\r\n".
      'MIME-Version: 1.0'."\r\n".
      'Content-type: text/html; charset=UTF-8'."\r\n";
    
    $content =
      '<html><head><title>'.$email['subject'].'</title></head><body>'.
      $email['content'].
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
    );
    if ( $bd->addRecord('email',$data) )
      $sent = mail(
        $email['to'],
        $email['subject'],
        $content,
        $headers
      );
    else
      $user->addAlert("Impossible d'enregistrer votre email.");
    
    if ( $sent )
    {
      $user->addAlert("Votre courriel a bien été envoyé...");
      $email = array();
      $bd->updateRecordsSimple('email', array('id' => $bd->getLastSerial('email','id')), array('sent' => 't'));
    }
    else
      $user->addAlert("Impossible d'envoyer votre courriel... veuillez le vérifier à nouveau. Si le problème persiste, contacter votre administrateur.");
  }
  
  includeLib("headers");
?>
<h1><?php echo $title ?></h1>
<?php includeLib("tree-view"); ?>
<?php require('actions.php'); ?>
<div class="body">
<h2>e-Mailing</h2>

<?php if ( count($email) > 1 && $_POST['subject'] ): ?>
<form action="<?php echo htmlsecure($_SERVER['PHP_SELF']) ?>?send" method="post" class="email verify">
<p>
  <span>De: </span>
  <span class="from">
    <?php echo htmlsecure($from) ?>
    <input type="hidden" name="from" value="<?php echo htmlsecure($config['mail']['orgnom'].' <'.$config["mail"]["mailfrom"].'>') ?>" disabled="disabled" />
  </span>
</p>
<p>
  <span>À: </span>
  <span class="to">
    <?php echo htmlsecure($email['to']) ?>
    <input type="hidden" name="to" value="<?php echo htmlsecure($config['mail']['orgnom'].' <'.$config["mail"]["mailfrom"].'>') ?>" disabled="disabled" />
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
    <?php echo $email['content'] ?>
    <input type="hidden" name="content" value="<?php echo htmlsecure($email['content']) ?>" />
  </span>
</p>
<p>
  <span></span>
  <span class="submit"><input type="submit" name="valid" value="Envoyer" class="submit" /></span>
</p>
</form>
<?php endif; ?>

<form action="<?php echo htmlsecure($_SERVER['PHP_SELF']) ?>" method="post" class="email">
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
      extended_valid_elements : "hr[class|width|size|noshade],iframe[src|width|height|name|align]",
      paste_use_dialog : false,
      theme_advanced_resizing : true,
      theme_advanced_resize_horizontal : true,
      apply_source_formatting : true,
      force_br_newlines : true,
      force_p_newlines : false,
      relative_urls : false,
      content_css: 'styles/emailing.css',
    });
  });
</script>
<p>
  <span>De: </span>
  <span><input type="text" class="from" name="from" value="<?php echo htmlsecure($from) ?>" disabled="disabled" /></span>
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
      if ( $rec['email'] )
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
</div>
<?php
  if ( $request ) $request->free();
	$bd->free();
	includeLib("footer");
?>
