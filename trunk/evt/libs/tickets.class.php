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
*    Copyright (c) 2006-2007 Baptiste SIMON <baptiste.simon AT e-glop.net>
*
***********************************************************************************/
?>
<?php
class Tickets
{
  var $content, $group, $vertical;
  var $count = 0;
  
  function Tickets($group = false, $vertical = false)
  {
    $this->group = $group;
    $this->vertical = $vertical;
    $this->content = "";
  }
  
  function _close()
  {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr-FR">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <title>e-venement : impression de tickets</title>
</head>
<?php if ( $config['ticket']['let_open_after_print'] || $config['print']['hard'] ): ?>
<body>
<?php else: ?>
<body onload="javascript: close();">
<?php endif; ?>
</body>
</html>
<?php
  }
  
  function _headers()
  {
    global $config;
    
    $r = '
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr-FR">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <title>e-venement : impression de tickets</title>
  <link rel="stylesheet" media="all" type="text/css" href="'.$config["website"]["base"].'evt/styles/tickets.default.css" />
  <link rel="stylesheet" media="all" type="text/css" href="'.$config["website"]["base"].'evt/perso/tickets.css" />
    ';
    
    if ( $config['ticket']['controlleft'] )
      $r .= '
  <link rel="stylesheet" media="all" type="text/css" href="'.$config["website"]["base"].'evt/styles/tickets.controlleft.css" />
      ';
    
    $r .= '
</head>
    ';
    
    if ( $config['print']['hard'] )
      $r .= '
<body>
      ';
    else if ( $config['ticket']['let_open_after_print'] )
      $r .= '
<body onload="javascript: print();">
      ';
    else
      $r .= '
<body onload="javascript: print(); close();">
      ';
    
    return $r;
	}
	
	function addToContent($bill)
	{
		global $config;
	  $this->count++;
		
		$dates = array('ltl' => array(), 'big' => array());
		$date = '';
		$last = strtotime($bill['date'][0]);
		if ( is_array($bill['date']) && count($bill['date']) > 1 )
		foreach ( $bill['date'] as $key => $time )
		{
		  $time = strtotime($time);
		  if ( date('m',$last) != date('m',$time) )
  		  $dates['big'][count($dates['big'])-1] = date('d.m',$last);
  		$dates["big"][]  = date($key == count($bill['date'])-1 ? 'd.m.y' : 'd',$time);
      $dates["ltl"][]  = date($key == count($bill['date'])-1 ? 'd.m'   : 'd',$time);
      $last = $time;
    }
    else
    {
      if ( !is_array($bill['date']) )
        $bill['date'][0] = $bill['date'];
      
      $time = strtotime($bill['date'][0]);
  		$dates["big"][0]  = strtolower($config["dates"]["DOTW"][date("w",$time)]).date(" j ",$time);
    	$dates["big"][0] .= strtolower($config["dates"]["MOTY"][intval(date("n",$time))-1]);
	    $dates["big"][0] .= date(" Y / H\hi",$time);
	    
	    $dates["ltl"][0]  = date("j ",$time);
	    $dates["ltl"][0] .= strtolower($config["dates"]["moty"][intval(date("n",$time))-1]);
	    $dates["ltl"][0] .= date(" Y / H\hi",$time);
	  }
    $date = array('ltl' => implode(',',$dates['ltl']), 'big' => implode(', ',$dates['big']));
		$this->content .= '
<div class="page">
	<div class="ticket">
	  <div class="logo"><img src="../perso/logo-100x100.jpg" alt="" /></div>
		<div class="left">';
                	$this->content .= '
                	<p class="manifid">#'.htmlsecure($bill["manifid"]).'<span class="tariftop">'.htmlsecure($bill["prix"]).'</span></p>
                	<p class="info '.(isset($bill["depot"]) ? 'depot' : '').' '.(isset($bill["info"]) ? htmlsecure($bill["info"]) : '').'">';
			if ( isset($bill["info"]) ) $this->content .= htmlsecure($bill["info"]);
			if ( isset($bill["depot"]) ) $this->content .= htmlsecure($bill["depot"]);
                	$this->content .= '</p>
                	<p class="metaevt">'.htmlsecure($bill["metaevt"]).'</p>
                	<p class="dateheure">'.htmlsecure($date["big"]).'</p>
                	<p class="lieuprix"><span class="lieu">'.htmlsecure($bill["sitenom"]).'</span> / <span class="prix">'.($bill["prix"] ? htmlsecure($bill["prix"]).'<span class="eur">€</span>' : htmlsecure($bill['tarif'])).'</span></p>
                	<p class="titre">'.htmlsecure(strlen($buf = $bill["evtnom"]) > 30 ? substr($buf,0,30).'...' : $buf).'</p>
                	<p class="desc">'.htmlsecure($bill['desc']).'</p>
                	<p class="cie">'.htmlsecure(strlen($buf = $bill["createurs"]) > 40 ? substr($buf,0,40).'...' : $buf).'</p>
                	<p class="org">'.($bill["org"] ? 'Org: ' : '').htmlsecure(strlen($bill["org"]) > 60 ? substr($bill["org"],0,60)." ..." : $bill["org"]).'</p>
                	<p class="placement">'.htmlsecure($bill["plnum"] ? "Place n°".$bill["plnum"] : "Placement libre ".($this->group ? ' - x'.$bill["nbgroup"] : '')).'</p>
                	<p class="operation"><span class="date">'.htmlsecure(date("d/m/Y H:i")).'</span> / <span class="num">#'.htmlsecure($bill["num"]).'</span>-<span class="operateur">'.htmlsecure($bill["operateur"]).'</span></p>
                	<p class="mentions">';
      if ( $config['ticket']['more-mentions'] )
      $this->content .= $config['ticket']['controlleft'] && $config['ticket']['more-mentions']
        ? '<span>À conserver</span><span class="more-mentions"> - '.htmlsecure($config['ticket']['more-mentions'])
        : '<span class="more-mentions">'.htmlsecure($config['ticket']['more-mentions']).' - </span><span>À conserver</span>';
      else $this->content .= '<span>À conserver</span>';
      $this->content .= '
                  </p>
                </div>
                <div class="right">';
                	$this->content .= '
                	<p class="manifid">#'.htmlsecure($bill["manifid"]).'<span class="tariftop">'.htmlsecure($bill["prix"]).'</span></p>';
			if ( isset($bill["info"]) ) $this->content .= '<p class="info '.htmlsecure($bill["info"]).'">'.htmlsecure($bill["info"]).'</p>';
			if ( isset($bill["depot"]) ) $this->content .= '<p class="depot"></p>';
                	$this->content .= '
                	<p class="metaevt">'.htmlsecure($bill["metaevt"]).'</p>
                	<p class="dateheure">'.htmlsecure($date["ltl"]).'</p>
                	<p class="lieuprix"><span class="lieu">'.htmlsecure(strlen($buf = $bill["sitenom"]) > 15 ? substr($buf,0,12).'...' : $buf).'</span> / <span class="prix">'.htmlsecure($bill["prix"]).'<span class="eur">€</span></span></p>
                	<p class="titre">'.htmlsecure(strlen($buf = $bill["evtnom"]) > 18 ? substr($buf,0,15).'...' : $buf).'</p>
                	<p class="cie">'.htmlsecure(strlen($buf = $bill["createurs"]) > 20 ? substr($buf,0,17).'...' : $buf).'</p>
                	<p class="org">'.htmlsecure($bill["orga"][0]).'</p>
                	<p class="placement">'.htmlsecure($bill["plnum"] ? "Place n°".$bill["plnum"] : "Placement libre ".($this->group ? ' - x'.$bill["nbgroup"] : '')).'</p>
                	<p class="operation"><span class="date">'.htmlsecure(date("d/m/Y H:i")).'</span> / <span class="num">#'.htmlsecure($bill["num"]).'</span>-<span class="operateur">'.htmlsecure($bill["operateur"]).'</span></p>
                	<p class="mentions">Contrôle</p>
                </div>
        </div>
</div>';
	}
	
	function _footers()
	{
    return '</body></html>';
  }
  
  function printAll()
  {
    if ( $this->content )
    {
      echo $this->_headers();
      echo $this->content;
      echo $this->_footers();
    }
    else
    {
      $this->_close();
    }
  }
  function getTicketsHTML()
  {
    return  $this->_headers().
            $this->content.
            $this->_footers();
  }
  
  function countTickets()
  {
    return $this->count;
  }
}
?>
