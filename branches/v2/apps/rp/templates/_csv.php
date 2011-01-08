<?php
  $outstream = fopen($outstream, 'w');
  
  if ( !$options['noheader'] )
  {
    $line = array(
      __('Title'),
      __('Name'),
      __('Firstname'),
      __('Address'),
      __('Postalcode'),
      __('City'),
      __('Country'),
      __('Npai'),
      __('email'),
      __('Phonetype'),
      __('Phonenumber'),
      __('Keywords'),
      __('Category of organism'),
      __('Organism'),
      __('Department'),
      __('Professional phone'),
      __('Professional email'),
      __('Type of function'),
      __('Function'),
      __('Address'),
      __('Postalcode'),
      __('City'),
      __('Country'),
      __('Email'),
      __('URL'),
      __('Npai'),
      __('Description'),
    );
    
    fputcsv($outstream, $line, $delimiter, $enclosure);
    ob_flush();
  }
  
  while ( true )
  {
    $contacts = $pager->getResults();
    foreach ( $contacts as $contact )
    {
      $line = array(
        $contact->title,
        $contact->name,
        $contact->firstname,
        $contact->address,
        $contact->postalcode,
        $contact->city,
        $contact->country,
        $contact->npai,
        $contact->email,
        $contact->Phonenumbers[0]->name,
        $contact->Phonenumbers[1]->number,
        $contact->description,
      );
      
      if ( $options['ms'] )
      foreach ( $line as $key => $value )
        $line[$key] = @iconv($charset['db'], $charset['ms'], $value);
      
      // do we need to show this professional relation ?
      $show = true;
      if ( count($groups_list) > 0 )
      {
        $show = false;
        foreach ( $contact->Groups as $group )
        if ( in_array($group->id,$groups_list) )
        {
          $show = true;
          break;
        }
      }
      
      if ( !$options['pro_only'] && $show )
      {
        fputcsv($outstream, $line, $delimiter, $enclosure);
        ob_flush();
      }
      $i = count($line);
      
      if ( !$options['nopro'] )
      foreach ( $contact->Professionals as $professional )
      {
        $j = $i;
        $line[$j++] = $professional['Organism']['Category'];
        $line[$j++] = $professional['Organism'];
        $line[$j++] = $professional['department'];
        $line[$j++] = $professional['contact_number'];
        $line[$j++] = $professional['contact_email'];
        $line[$j++] = $professional['ProfessionalType'];
        $line[$j++] = $professional;
        $line[$j++] = $professional['Organism']['address'];
        $line[$j++] = $professional['Organism']['postalcode'];
        $line[$j++] = $professional['Organism']['city'];
        $line[$j++] = $professional['Organism']['country'];
        $line[$j++] = $professional['Organism']['email'];
        $line[$j++] = $professional['Organism']['url'];
        $line[$j++] = $professional['Organism']['description'];
        $line[$j++] = $professional['Organism']['npai'];

        if ( $options['ms'] )
        foreach ( $line as $key => $value )
          $line[$key] = @iconv($charset['db'], $charset['ms'], $value);
        
        fputcsv($outstream, $line, $delimiter, $enclosure);
        ob_flush();
      }
    }
    
    if ( $pager->getPage() + 1 > $pager->getLastPage() )
      break;
    $pager->setPage($pager->getPage() + 1);
    $pager->init();
  }
  
  fclose($outstream);
