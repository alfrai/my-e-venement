<?php
/**********************************************************************************
*
*	    This file is part of beta-libs.
*
*    beta-libs is free software; you can redistribute it and/or modify
*    it under the terms of the GNU Lesser General Public License as published by
*    the Free Software Foundation; either version 2 of the License.
*
*    beta-libs is distributed in the hope that it will be useful,
*    but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*    GNU Lesser General Public License for more details.
*
*    You should have received a copy of the GNU Lesser General Public License
*    along with beta-libs; if not, write to the Free Software
*    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*
*    Copyright (c) 2006 Baptiste SIMON <baptiste.simon AT e-glop.net>
*
***********************************************************************************/
?>
<?php
	includeClass("bdRequest");
	includeClass("reservations");
	
	class reservations
	{
		var $_bd;
		var $_user;
		var $_transac;
		var $_personne;	// personne.id
		var $_fctorgid;	// organisme_personne.id
		var $_preresa = array();	// tableau donnant : array(manifid => array("nb" => nb, "tarif" => tarif, "reduc" => reduc));
		var $_resa = array();		// tableau donnant : array(manifid => array("nb" => nb, "tarif" => tarif, "reduc" => reduc));
		var $_postresa = array();	// tableau donnant : array(manifid => array("nb" => nb, "tarif" => tarif, "reduc" => reduc));
		
		function reservations($bd, $user, $numtransac, $personne = NULL, $fctorgid = NULL)
		{
			if ( !is_object($bd) )
				return false;
			if ( !$bd->isValid()
			  || !is_object($user)
			  || is_null($numtransac)
			  || !( intval($fctorgid)."" == $fctorgid."" || is_null($fctorgid) ) )
				return false;
			
			$this->_bd		= $bd;
			$this->_user		= $user;
			$this->_transac		= $numtransac;
			$this->_personne	= $personne;
			$this->_fctorgid	= $fctorgid;
			
			if ( is_null($this->_personne) )
				$this->autoSetIds();
		}
		
		// met à jour la transaction avec les données de la personne concernée
		function updateTransaction()
		{
			$arr = array();
			$arr["personneid"]	= $this->_personne;
			$arr["fctorgid"]	= is_null($this->_fctorgid) ? NULL : intval($this->_fctorgid);
			if ( !$this->_bd->updateRecordsSimple("transaction",array("id" => $this->_transac),$arr) )
			{
				$this->_user->addAlert("Impossible de mettre à jour la transaction");
				return false;
			}
		}
		
		// $manif   : id de la manifestation concernée
		// $resa    : le code des billets commandés sous forme de tableau, tel que donné par preg_tarif()
		function _addxxxReservation($manif,$resa,$table,$doit = true)
		{
			global $config;
			
			$arr = array();
			$arr["accountid"]	= $this->_user->getId();
			$arr["manifid"]		= intval($manif);
			$arr["reduc"]		= intval($resa["reduc"]);
			$arr["annul"]		= intval($resa["nb"]) < 0 ? 't' : 'f';
			$arr["transaction"]	= $this->_transac;
			
			$query = " SELECT addpreresa(".	$arr["accountid"].",".$arr["transaction"].",".$arr["manifid"].",".
							$arr["reduc"].",'".$arr["annul"]."'::boolean,'".pg_escape_string($resa["tarif"])."',".
							$resa["nb"].") AS ok";
			$request = new bdRequest($this->_bd,$query);
			$ok = $request->getRecord('ok') == 't' ? true : false;
			$request->free();
			
			$this->_preresa[intval($manif)][$name = "nb"]		= $resa[$name];
			$this->_preresa[intval($manif)][$name = "tarif"]	= $resa[$name];
			$this->_preresa[intval($manif)][$name = "reduc"]	= $resa[$name];
			
			return $ok;
		}
		
		// $manif   : id de la manifestation concernée
		// $resa    : le code des billets commandés sous forme de tableau, tel que donné par preg_tarif()
		function addPreReservation($manif,$resa,$doit = true)
		{
			if ( $resa != preg_tarif(NULL) )
				$ok = $this->_addxxxReservation($manif,$resa,"reservation_pre",$doit);
			else	$ok = true;
			
			if ( !$ok ) $this->_user->addAlert("Impossible de rajouter la pré-réservation ".$resa["full"]);
			return $ok;
		}
		
		function autoSetIds()
		{
			$query = "SELECT personneid, fctorgid
				  FROM transaction
				  WHERE id = '".$this->_transac."'";
			$request = new bdRequest($this->_bd,$query);
			
			$this->_personne = $request->getRecord("personneid");
			$this->_fctorgid = $request->getRecord("fctorgid");
			
			$request->free();
		}
		
		function getPreReservations()
		{
			$arr = array();
			$query	= " SELECT *, (SELECT plnum FROM manifestation WHERE manifestation.id = manifid) AS plnum
				    FROM tickets2print_bytransac(".$this->_transac.")
				    ORDER BY nb, tarif, reduc";
			$request = new bdRequest($this->_bd,$query);
			while ( $rec = $request->getRecordNext() )
			{
				$tmp = $rec;
				unset($tmp["manifid"]);
				$tmp[$name = "canceled"]= $tmp[$name] == 't'	? true : false;
				$tmp[$name = "printed"] = $tmp[$name] == 't'	? true : false;
				$tmp[$name = "reduc"]	= $tmp[$name] < 10	? $tmp[$name]."0" : $tmp[$name]."";
				if ( $tmp["plnum"] == 't' )
				$tmp[$name = "other"]	= "plnum-";
				$tmp["full"] = $tmp["nb"].$tmp["tarif"].$tmp["reduc"];
				$arr[intval($rec["manifid"])][] = $tmp;
			}
			$request->free();
			
			return $arr;
		}
		
		function getReservations()
		{
			$arr = array();
			$query  = " SELECT *
				    FROM tickets2print_bytransac(".$this->_transac.")
				    WHERE printed  = true
				      AND canceled = false";
			$request = new bdRequest($this->_bd,$query);
			while ( $rec = $request->getRecordNext() )
			{
				$tmp = array();
				$tmp["nb"]	= intval($rec["nb"]);
				$tmp["tarif"]	= $rec["tarif"];
				$tmp["reduc"]	= intval($rec["reduc"]) < 10 ? intval($rec["reduc"])."0" : intval($rec["reduc"])."";
				$tmp["full"]	= $tmp["nb"].$tmp["tarif"].$tmp["reduc"];
				$tmp["canceled"]= false;
				$tmp["printed"]	= true;
				$arr[intval($rec["manifid"])][] = $tmp;
			}
			$request->free();
			
			return $arr;
		}
		
		function getPersonneId()
		{ return $this->_personne; }
	}
?>
