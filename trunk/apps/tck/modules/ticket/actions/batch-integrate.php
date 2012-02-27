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
  $this->getContext()->getConfiguration()->loadHelpers('I18N');
  
  // get back the manifestation
  $mid = $request->getParameter('manifestation_id');
  $q = Doctrine::getTable('Manifestation')->createQuery('m')
    ->where('id = ?',$mid);
  $this->manifestation = $q->fetchOne();
  $this->form = new TicketsIntegrationForm($this->manifestation);
  
  $files = $request->getFiles('integrate');
  if ( count($files) > 0 )
  {
    $this->form->bind($integrate = $request->getParameter('integrate'),$request->getFiles('integrate'));
    if ( $this->form->isValid() )
    {
      $price_default_id = Doctrine::getTable('Price')->createQuery('p')
        ->andWhere('p.name = ?',sfConfig::get('app_tickets_foreign_price'))
        ->fetchOne()->id;
      
      $fp = fopen($files['file']['tmp_name'],'r');
      
      // get back the base transaction
      $transaction_ref = Doctrine::getTable('Transaction')->createQuery()
        ->andWhere('id = ?',$integrate['transaction_ref_id'])
        ->andWhere('tck.printed = false AND tck.integrated = false')
        ->andWhere('tck.price_id = ?',$price_default_id)
        ->andWhere('tck.manifestation_id = ?',$this->manifestation->id)
        ->orderBy('id ASC')
        ->fetchOne();
      $transaction = new Transaction();
      
      switch ( $integrate['filetype'] ) {
      case 'fb':
        require(dirname(__FILE__).'/batch-integrate-'.$integrate['filetype'].'.php');
        break;
      case 'tkn':
        require(dirname(__FILE__).'/batch-integrate-'.$integrate['filetype'].'.php');
        break;
      default:
        $this->getUser()->setFlash('error',__("You've chosen an unimplemented feature."));
        $this->redirect('ticket/batchIntegrate?manifestation_id='.$this->manifestation->id);
        require(dirname(__FILE__).'/batch-integrate-default.php');
        break;
      }
      
      // integrating normalized content
      foreach ( $tickets as $ticket )
      {
        // if the line references a named contact
        if ( $ticket['name'] && $ticket['firstname'] )
        {
          $charset = sfContext::getInstance()->getConfiguration()->charset;
          $search = array(implode('* ',explode(' ',$ticket['name'])).'*',implode('* ',explode(' ',$ticket['firstname'])).'*');
          $search = strtolower(iconv($charset['db'],$charset['ascii'],implode(' ',$search)));
          $q = Doctrine::getTable('Contact')->createQuery('c');
          if ( $ticket['postalcode'] )
            $q->andWhere('c.postalcode = ?',$ticket['postalcode']);
          $contacts = Doctrine::getTable('Contact')->search($search,$q)->execute();
          
          if ( $contacts->count() == 0 )
          {
            $transaction = new Transaction();
            $transaction->Contact = new Contact();
            $transaction->Contact->name = $ticket['name'];
            $transaction->Contact->firstname = $ticket['firstname'];
            $transaction->Contact->postalcode = $ticket['postalcode'];
            if ( !$ticket['city'] )
            {
              $postalcode = Doctrine::getTable('Postalcode')->createQuery()->andWhere('postalcode = ?',$ticket['postalcode'])->fetchOne();
              $ticket['city'] = $postalcode->city;
            }
            $transaction->Contact->city = $ticket['city'];
            $transaction->Contact->country = $ticket['country'];
          }
          else
          {
            // keep the last transaction if contact is the same, or create a new one if not
            if ( $transaction->isNew()
              || !(!is_null($transaction->contact_id) && $transaction->Contact->id == $contacts[0]->id) )
            {
              $transaction = new Transaction();
              $transaction->Contact = $contacts[0];
            }
            
            // adding a keyword
            $transaction->Contact->description =
              implode(' ',array($transaction->Contact->description,'integration-'.$ticket['type']));
          }
        }
        else // if ( !($ticket['name'] && $ticket['firstname'] )
        {
          if ( $transaction->Contact instanceof Contact )
          {
            $transaction = new Transaction();
            $transaction->Contact = NULL; // hack
          }
        }
        
        // if it's not a cancellation
        if ( !$ticket['cancel'] )
        {
          $tck = new Ticket();
          $tck->manifestation_id = $this->manifestation->id;
          $tck->price_name = $ticket['price_name'];
          $tck->price_id = $ticket['price_id'];
          $tck->value = $ticket['value'];
          $tck->integrated = true;
          $tck->id = $ticket['id'];
          $tck->gauge_id = $integrate['gauges_list'];
          $tck->created_at = date('Y-m-d H:i:s',strtotime(isset($ticket['created_at']) ? $ticket['created_at'] : NULL));
          
          $transaction->Tickets[] = $tck;
          if ( $integrate['transaction_ref_id'] && $transaction_ref !== false && $transaction_ref->Tickets->count() > 0 )
          {
            $transaction_ref->Tickets[$transaction_ref->Tickets->count()-1]->delete();
            unset($transaction_ref->Tickets[$transaction_ref->Tickets->count()-1]);
          }
          else
            $notices['no-more-refs'] = __("You've integrated more tickets than you've got in your base transaction.");
        }
        else
          $this->getUser()->setFlash('error',__('Tried to integrate a cancellation ticket without any referenced id. This kind of cancellation ticket has to be integrated manually.'));
        
        $transaction->save();
      }

      fclose($fp);
      sfContext::getInstance()->getConfiguration()->loadHelpers(array('Url','I18N'));
      $this->getUser()->setFlash('notice',__("File importated with the last transaction's id %%tid%%, with %%nbtck%% ticket(s).",array('%%tid%%' => $transaction->id, '%%nbtck%%' => count($tickets))).'<br/>'.implode(' ',$notices));
      $this->redirect(url_for('ticket/batchIntegrate?manifestation_id='.$this->manifestation->id));
    }
    else
    {
      $this->getUser()->setFlash('error','Error in the form validation');
    }
  }
