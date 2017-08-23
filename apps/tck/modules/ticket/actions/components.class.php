<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of components
 *
 * @author gwenael
 */
class ticketComponents extends sfComponents {
    public function executeCustomTckMenu(){
        $menu = array();
        //TODO more generic arrays
        $mandatory = array();
        $ticket = array();
        $manifestation = array();
        $gauge = array();
        $transaction = array();
        $others = array();
        $try = $this->json;
        //retrieve params and create array for menu according to status
        foreach ($try as $key => $value) {
           if (!$value['optional']){
               $mandatory[]=$key;
           }else if($value['displayed']){
               $subKeys = explode('.',$key);
               if (count($subKeys)==1){
                   //ticket has no enter key
                   $ticket[] = $key;
               }else{
                    switch(explode('.',$key)[0]){
                        case 'Manifestation':
                            $manifestation[] = $key;
                            break;
                        case 'Transaction':
                            $transaction[] = $key;
                            break;
                        case 'Gauge':
                            $gauge[] = $key;
                            break;
                        default:
                            $others[] = $key;
                            break;
                    }
               }
           }
        }
        
        
        $menu['mandatory']=$mandatory;
        $menu['ticket']=$ticket;
        $menu['manifestation']=$manifestation;
        $menu['gauge']=$gauge;
        $menu['transaction']=$transaction;
        $menu['others']=$others;
        
        $this->menu=$menu;
        
    }
}
