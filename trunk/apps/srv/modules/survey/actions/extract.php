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
*    Copyright (c) 2006-2015 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2015 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php
  $survey = $this->form->getObject();
  
  $qre = new Questionnaire((string)$this->survey, $this->survey->description, 1);
  $firm = sfConfig::get('project_about_firm');
  $qre->addInvestigator(new Investigator(
    array('lastName'=> '', 'firstName' => ''),
    $firm['name'],
    null, null, null, null,
    $firm['url']
  ));
  $client = sfConfig::get('project_about_client');
  $qre->addDataCollector(new DataCollector(
    array('lastName'=> $this->getUser()->getGuardUser()->last_name, 'firstName' => $this->getUser()->getGuardUser()->first_name),
    $client['name'],
    null, null, null,
    $this->getUser()->getGuardUser()->email_address, null,
    $this->getUser()->getId()
  ));
  
  //$qre->addQuestionnaireInfo(new QuestionnaireInfo('before', 'This is class for your, my lord!', 'self'));
  
  $section = new Section();
  foreach ( $survey->Queries as $query )
  {
    $question = new Question((string)$query);
    
    switch ( $query->stats ) {
    case 'choices':
      foreach ( $query->Options as $opt )
        $question->addFixedResponse((string)$opt);
      break;
    case 'free':
      if ( stripos($query->type, 'date') === false )
        $question->addFreeResponse('longtext', 999999, null, $query->id);
      else
        $question->addFreeResponse('date', 255, null, $query->id);
      break;
    case 'numbers':
      $question->addFreeResponse('integer', 31, $query->id);
      break;
    }
    
    $section->addQuestion($question);
  }
  
  $qre->addSection($section);
  $this->survey = $qre;
