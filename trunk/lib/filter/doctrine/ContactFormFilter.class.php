<?php

/**
 * Contact filter form.
 *
 * @package    e-venement
 * @subpackage filter
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormFilterTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class ContactFormFilter extends BaseContactFormFilter
{
  protected $noTimestampableUnset = true;
  protected $showProfessionalData = true;
  protected $tickets_having_query = NULL; // Doctrine_Query

  /**
   * @see AddressableFormFilter
   */
  public function configure()
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers(array('I18N'));
    
    $this->tickets_having_query = Doctrine_Query::create()->from('Contact c')
      ->groupBy('c.id')
      ->select('c.id');
    
    $this->widgetSchema['groups_list']->setOption(
      'order_by',
      array('u.id IS NULL DESC, u.username, name','')
    );
    
    $this->widgetSchema['emails_list']->setOption('query',Doctrine::getTable('Email')
      ->createQuery()
      ->andWhere('sent')
    );
    
    // has postal address ?
    $this->widgetSchema   ['has_address'] = $this->widgetSchema   ['npai'];
    $this->validatorSchema['has_address'] = $this->validatorSchema['npai'];
    
    // has postal address ?
    $this->widgetSchema   ['has_category'] = $this->widgetSchema   ['npai'];
    $this->validatorSchema['has_category'] = $this->validatorSchema['npai'];
    
    // has email address ?
    $this->widgetSchema   ['has_email'] = $this->widgetSchema   ['npai'];
    $this->validatorSchema['has_email'] = $this->validatorSchema['npai'];
    
    // no newsletter ?
    $this->widgetSchema   ['email_newsletter'] = $this->widgetSchema   ['npai'];
    $this->validatorSchema['email_newsletter'] = $this->validatorSchema['npai'];
    
    // organism
    $this->widgetSchema   ['organism_id'] = new sfWidgetFormDoctrineJQueryAutocompleter(array(
      'model' => 'Organism',
      'url'   => url_for('organism/ajax'),
    ));
    $this->validatorSchema['organism_id'] = new sfValidatorInteger(array('required' => false));
    
    // organism category
    $this->widgetSchema   ['organism_category_id'] = new sfWidgetFormDoctrineChoice(array(
      'model'     => 'OrganismCategory',
      'order_by'  => array('name',''),
      'multiple'  => true,
    ));
    $this->validatorSchema['organism_category_id'] = new sfValidatorDoctrineChoice(array(
      'model'    => 'OrganismCategory',
      'required' => false,
      'multiple' => true,
    ));
    
    // professional type
    $this->widgetSchema   ['professional_type_id'] = new sfWidgetFormDoctrineChoice(array(
      'model'     => 'ProfessionalType',
      'multiple'  => true,
      'order_by'  => array('name',''),
    ));
    $this->validatorSchema['professional_type_id'] = new sfValidatorDoctrineChoice(array(
      'model'    => 'ProfessionalType',
      'required' => false,
      'multiple' => true,
    ));
    
    $this->widgetSchema   ['not_groups_list'] = $this->widgetSchema   ['groups_list'];
    $this->validatorSchema['not_groups_list'] = $this->validatorSchema['groups_list'];
    
    $years = sfContext::getInstance()->getConfiguration()->yob;
    $this->widgetSchema   ['YOB'] = new sfWidgetFormFilterDate(array(
      'from_date'=> new sfWidgetFormDate(array(
        'format' => '%year% %month% %day%',
        'years'  => $years,
      )),
      'to_date'   => new sfWidgetFormDate(array(
        'format' => '%year% %month% %day%',
        'years'  => $years,
      )),
      'with_empty'=> false,
      'template'  => '<span class="from_year">'.__('From %from_date%').'</span> <span class="to_year">'.__('to %to_date%').'</span>',
    ));
    $this->validatorSchema['YOB'] = new sfValidatorDateRange(array(
      'from_date' => new sfValidatorDate(array('required' => false,)),
      'to_date'   => new sfValidatorDate(array('required' => false,)),
      'required'  => false,
    ));
    
    // events
    $this->widgetSchema   ['events_list'] = new sfWidgetFormDoctrineChoice(array(
      'model' => 'Event',
      'order_by' => array('name','asc'),
      'multiple' => true,
    ));
    $this->validatorSchema['events_list'] = new sfValidatorDoctrineChoice(array(
      'required' => false,
      'model'    => 'Event',
      'multiple' => true,
    ));
    $this->widgetSchema   ['event_categories_list'] = new sfWidgetFormDoctrineChoice(array(
      'model' => 'EventCategory',
      'order_by' => array('name','asc'),
      'multiple' => true,
    ));
    $this->validatorSchema['event_categories_list'] = new sfValidatorDoctrineChoice(array(
      'required' => false,
      'model'    => 'EventCategory',
      'multiple' => true,
    ));
    $this->widgetSchema   ['meta_events_list'] = new sfWidgetFormDoctrineChoice(array(
      'model' => 'MetaEvent',
      'query' => Doctrine::getTable('MetaEvent')->createQuery('me')
        ->andWhereIn('me.id',array_keys(sfContext::getInstance()->getUser()->getMetaEventsCredentials())),
      'order_by' => array('name','asc'),
      'multiple' => true,
    ));
    $this->validatorSchema['meta_events_list'] = new sfValidatorDoctrineChoice(array(
      'required' => false,
      'model'    => 'MetaEvent',
      'query' => Doctrine::getTable('MetaEvent')->createQuery('me')
        ->andWhereIn('me.id',array_keys(sfContext::getInstance()->getUser()->getMetaEventsCredentials())),
      'multiple' => true,
    ));
    $this->widgetSchema   ['prices_list'] = new sfWidgetFormDoctrineChoice(array(
      'model' => 'Price',
      'query' => Doctrine::getTable('Price')->createQuery('p')
        ->leftJoin('p.Users u')
        ->andWhere('u.id = ?',sfContext::getInstance()->getUser()->getId()),
      'order_by' => array('name, description',''),
      'multiple' => true,
    ));
    $this->validatorSchema['prices_list'] = new sfValidatorDoctrineChoice(array(
      'required' => false,
      'model'    => 'Price',
      'query' => Doctrine::getTable('Price')->createQuery('p')
        ->leftJoin('p.Users u')
        ->andWhere('u.id = ?',sfContext::getInstance()->getUser()->getId()),
      'multiple' => true,
    ));
    
    $this->widgetSchema   ['event_archives'] = new sfWidgetFormChoice($opt = array(
      'choices' => $choices = $this->getEventArchivesChoices(),
      'multiple' => true,
    ));
    $this->validatorSchema['event_archives'] = new sfValidatorChoice(array_merge($opt,array(
      'required' => false,
    )));
    
    $this->widgetSchema   ['tickets_amount_min'] = new sfWidgetFormInput();
    $this->validatorSchema['tickets_amount_min'] = new sfValidatorInteger(array(
      'required' => false,
    ));
    $this->widgetSchema   ['tickets_amount_max'] = new sfWidgetFormInput();
    $this->validatorSchema['tickets_amount_max'] = new sfValidatorInteger(array(
      'required' => false,
    ));
    
    //cards
    $this->widgetSchema   ['member_cards'] = new sfWidgetFormDoctrineChoice(array(
      'model' => 'MemberCardType',
      'order_by' => array('name','asc'),
      'multiple' => true,
    ));
    $this->validatorSchema['member_cards'] = new sfValidatorDoctrineChoice(array(
      'required' => false,
      'multiple' => true,
      'model' => 'MemberCardType',
    ));
    $this->widgetSchema   ['member_cards_expire_at'] = new liWidgetFormDateText(array(
      'culture' => sfContext::getInstance()->getUser()->getCulture(),
    ));
    $this->validatorSchema['member_cards_expire_at'] = new sfValidatorDate(array(
      'required' => false,
    ));
    
    // flow control
    $this->widgetSchema   ['control_manifestation_id'] = new sfWidgetFormDoctrineChoice(array(
      'model' => 'Manifestation',
      'query' => $q = Doctrine::getTable('Manifestation')->createQuery('m')->select('m.*, e.*')->leftJoin('e.Checkpoints cp')->andWhere('cp.id IS NOT NULL'),
      'multiple'  => true,
    ));
    $this->validatorSchema['control_manifestation_id'] = new sfValidatorDoctrineChoice(array(
      'model' => 'Manifestation',
      'query' => $q,
      'required' => false,
      'multiple'  => true,
    ));
    $this->widgetSchema   ['control_checkpoint_id'] = new sfWidgetFormDoctrineChoice(array(
      'model' => 'Checkpoint',
      'multiple'  => true,
    ));
    $this->validatorSchema['control_checkpoint_id'] = new sfValidatorDoctrineChoice(array(
      'model' => 'Checkpoint',
      'required' => false,
      'multiple'  => true,
    ));
    $this->widgetSchema   ['control_created_at'] = new sfWidgetFormFilterDate(array(
      'from_date' => new liWidgetFormJQueryDateText(array('culture' => sfContext::getInstance()->getUser()->getCulture())),
      'to_date'   => new liWidgetFormJQueryDateText(array('culture' => sfContext::getInstance()->getUser()->getCulture())),
      'with_empty'=> false,
    ));
    $this->validatorSchema['control_created_at'] = new sfValidatorDateRange(array(
      'from_date'     => new sfValidatorDate(array(
        'required'    => false,
        'date_output' => 'Y-m-d',
        'with_time'   => false,
      )),
      'to_date'       => new sfValidatorDate(array(
        'required'    => false,
        'date_output' => 'Y-m-d',
        'with_time'   => false,
      )),
      'required' => false,
    ));
    
    $this->widgetSchema   ['region_id'] = new sfWidgetFormDoctrineChoice(array(
      'model' => 'GeoFrRegion',
      'order_by' => array('name',''),
      'add_empty' => true,
    ));
    $this->validatorSchema['region_id'] = new sfValidatorDoctrineChoice(array(
      'model' => 'GeoFrRegion',
      'required' => false,
    ));
    
    parent::configure();
  }
  
  public function getFields()
  {
    $fields = parent::getFields();
    $fields['postalcode']           = 'Postalcode';
    $fields['YOB']                  = 'YOB';
    $fields['not_contacts_list']    = 'NotContactsList';
    $fields['not_professionals_list'] = 'NotProfessionalsList';
    $fields['organism_id']          = 'OrganismId';
    $fields['organism_category_id'] = 'OrganismCategoryId';
    $fields['professional_type_id'] = 'ProfessionalTypeId';
    $fields['has_email']            = 'HasEmail';
    $fields['email_newsletter']     = 'EmailNewsletter';
    $fields['has_address']          = 'HasAddress';
    $fields['has_category']         = 'HasCategory';
    $fields['groups_list']          = 'GroupsList';
    $fields['not_groups_list']      = 'NotGroupsList';
    $fields['emails_list']          = 'EmailsList';
    $fields['events_list']          = 'EventsList';
    $fields['event_categories_list']= 'EventCategoriesList';
    $fields['meta_events_list']     = 'MetaEventsList';
    $fields['event_archives']       = 'EventArchives';
    $fields['prices_list']          = 'PricesList';
    $fields['member_cards']         = 'MemberCards';
    $fields['member_cards_expire_at'] = 'MemberCardsExpireAt';
    $fields['control_manifestation_id'] = 'ControlManifestationId';
    $fields['control_checkpoint_id'] = 'ControlCheckpointId';
    $fields['control_created_at']   = 'ControlCreatedAt';
    $fields['region']   = 'RegionId';
    
    // must be the last ones, because of a having() part which needs to be added lately
    $fields['tickets_amount_min']   = 'TicketsAmountMin';
    $fields['tickets_amount_max']   = 'TicketsAmountMax';
    
    return $fields;
  }
  
  public function addNotContactsListColumnQuery(Doctrine_Query $q, $field, $value)
  {
    $a = $q->getRootAlias();
    
    if ( $value )
    if ( count($value) > 0 )
      $q->andWhereNotIn("$a.id",$value);
    
    return $q;
  }
  public function addNotProfessionalsListColumnQuery(Doctrine_Query $q, $field, $value)
  {
    // remove completly a contact from a list if it's got only one "professional" and this one is selected for removal
    if ( $value )
    if ( count($value) > 0 )
      $q->andWhere('(TRUE')
        ->andWhereNotIn('p.id',$value)
        ->orWhere('p.id IS NULL')
        ->andWhere('TRUE)');
    
    return $q;
  }
  public function addRegionIdColumnQuery(Doctrine_Query $q, $field, $value)
  {
    $a = $q->getRootAlias();
    
    if ( intval($value) > 0 )
      $q->andWhere("SUBSTRING($a.postalcode,1,2) IN (SELECT REGEXP_REPLACE(dpt.num, '[a-zA-Z]', '0') FROM GeoFrDepartment dpt LEFT JOIN dpt.Region reg WHERE reg.id = ?)",$value)
        ->andWhere("LOWER($a.country) = ? OR TRIM($a.country) = ? OR $a.country IS NULL",array('france',''));
    
    return $q;
  }
  public function addEmailsListColumnQuery(Doctrine_Query $q, $field, $value)
  {
    $a = $q->getRootAlias();
    
    if ( is_array($value) )
      $q->leftJoin("$a.Emails ce")
        ->leftJoin("p.Emails pe")
        ->andWhere('(TRUE')
        ->andWhere('ce.sent = TRUE')
        ->andWhereIn('ce.id',$value)
        ->orWhereIn('pe.id',$value)
        ->andWhere('pe.sent = TRUE')
        ->andWhere('TRUE)');
    
    return $q;
  }
  
  // links to the ticketting system module
  public function addEventsListColumnQuery(Doctrine_Query $q, $field, $value)
  {
    $a = $q->getRootAlias();
    
    if ( is_array($value) )
    foreach ( array($q,$this->tickets_having_query) as $query )
    {
      if ( !$query->contains("LEFT JOIN $a.Transactions transac ON $a.id = transac.contact_id AND (p.id = transac.professional_id OR transac.professional_id IS NULL)") )
      $query->leftJoin("$a.Transactions transac ON $a.id = transac.contact_id AND (p.id = transac.professional_id OR transac.professional_id IS NULL)");
      
      if ( !$query->contains("LEFT JOIN transac.Tickets tck ON transac.id = tck.transaction_id AND (tck.printed_at IS NOT NULL OR tck.integrated_at IS NOT NULL) AND tck.id NOT IN (SELECT ttck.cancelling FROM ticket ttck WHERE ttck.cancelling IS NOT NULL)')") )
      $query->leftJoin('transac.Tickets tck ON transac.id = tck.transaction_id AND (tck.printed_at IS NOT NULL OR tck.integrated_at IS NOT NULL) AND tck.id NOT IN (SELECT ttck.cancelling FROM ticket ttck WHERE ttck.cancelling IS NOT NULL)');
      
      if ( !$query->contains("LEFT JOIN tck.Manifestation m") )
      $query->leftJoin('tck.Manifestation m');
      
      $query->andWhereIn('m.event_id',$value);
    }
    
    return $q;
  }
  public function addEventCategoriesListColumnQuery(Doctrine_Query $q, $field, $value)
  {
    $a = $q->getRootAlias();
    
    if ( is_array($value) )
    foreach ( array($q,$this->tickets_having_query) as $query )
    {
      if ( !$query->contains("LEFT JOIN $a.Transactions transac ON $a.id = transac.contact_id AND (p.id = transac.professional_id OR transac.professional_id IS NULL)") )
      $query->leftJoin("$a.Transactions transac ON $a.id = transac.contact_id AND (p.id = transac.professional_id OR transac.professional_id IS NULL)");
      
      if ( !$query->contains("LEFT JOIN transac.Tickets tck ON transac.id = tck.transaction_id AND (tck.printed_at IS NOT NULL OR tck.integrated_at IS NOT NULL) AND tck.id NOT IN (SELECT ttck.cancelling FROM ticket ttck WHERE ttck.cancelling IS NOT NULL)')") )
      $query->leftJoin('transac.Tickets tck ON transac.id = tck.transaction_id AND (tck.printed_at IS NOT NULL OR tck.integrated_at IS NOT NULL) AND tck.id NOT IN (SELECT ttck.cancelling FROM ticket ttck WHERE ttck.cancelling IS NOT NULL)');
      
      if ( !$query->contains("LEFT JOIN tck.Manifestation m") )
      $query->leftJoin('tck.Manifestation m');
      
      if ( !$query->contains("LEFT JOIN m.Event event") )
      $query->leftJoin('m.Event event');
      
      $query->andWhereIn('event.event_category_id',$value);
    }
    
    return $q;
  }
  public function addMetaEventsListColumnQuery(Doctrine_Query $q, $field, $value)
  {
    $a = $q->getRootAlias();
    
    if ( is_array($value) )
    foreach ( array($q,$this->tickets_having_query) as $query )
    {
      if ( !$query->contains("LEFT JOIN $a.Transactions transac ON $a.id = transac.contact_id AND (p.id = transac.professional_id OR transac.professional_id IS NULL)") )
      $query->leftJoin("$a.Transactions transac ON $a.id = transac.contact_id AND (p.id = transac.professional_id OR transac.professional_id IS NULL)");
      
      if ( !$query->contains("LEFT JOIN transac.Tickets tck ON transac.id = tck.transaction_id AND (tck.printed_at IS NOT NULL OR tck.integrated_at IS NOT NULL) AND tck.id NOT IN (SELECT ttck.cancelling FROM ticket ttck WHERE ttck.cancelling IS NOT NULL)") )
      $query->leftJoin('transac.Tickets tck ON transac.id = tck.transaction_id AND (tck.printed_at IS NOT NULL OR tck.integrated_at IS NOT NULL) AND tck.id NOT IN (SELECT ttck.cancelling FROM ticket ttck WHERE ttck.cancelling IS NOT NULL)');
      
      if ( !$query->contains("LEFT JOIN tck.Manifestation m") )
      $query->leftJoin('tck.Manifestation m');
      
      if ( !$query->contains("LEFT JOIN m.Event event") )
      $query->leftJoin('m.Event event');
      
      if ( !$query->contains("LEFT JOIN event.MetaEvent mev") )
      $query->leftJoin('event.MetaEvent mev');
      
      $query->andWhereIn('mev.id',$value);
    }
    
    return $q;
  }

  public function addPricesListColumnQuery(Doctrine_Query $q, $field, $value)
  {
    $a = $q->getRootAlias();
    
    if ( is_array($value) )
    foreach ( array($q,$this->tickets_having_query) as $query )
    {
      if ( !$query->contains("LEFT JOIN $a.Transactions transac ON $a.id = transac.contact_id AND (p.id = transac.professional_id OR transac.professional_id IS NULL)") )
      $query->leftJoin("$a.Transactions transac ON $a.id = transac.contact_id AND (p.id = transac.professional_id OR transac.professional_id IS NULL)");
      
      if ( !$query->contains("LEFT JOIN transac.Tickets tck ON transac.id = tck.transaction_id AND (tck.printed_at IS NOT NULL OR tck.integrated_at IS NOT NULL) AND tck.id NOT IN (SELECT ttck.cancelling FROM ticket ttck WHERE ttck.cancelling IS NOT NULL)") )
      $query->leftJoin('transac.Tickets tck ON transac.id = tck.transaction_id AND (tck.printed_at IS NOT NULL OR tck.integrated_at IS NOT NULL) AND tck.id NOT IN (SELECT ttck.cancelling FROM ticket ttck WHERE ttck.cancelling IS NOT NULL)');
      
      if ( !$query->contains("LEFT JOIN tck.Price price") )
      $query->leftJoin('tck.Price price');
      
      $query->andWhereIn('price.id',$value);
    }
    
    return $q;
  }

  public function addEventArchivesColumnQuery(Doctrine_Query $q, $field, $value)
  {
    $a = $q->getRootAlias();
    
    if ( is_array($value) )
    {
      if ( !$q->contains("LEFT JOIN $a.EventArchives ea") )
      $q->leftJoin("$a.EventArchives ea");
      
      $q->andWhereIn('ea.name',$value);
    }
    
    return $q;
  }

  // having queries
  public function addTicketsAmountMinColumnQuery(Doctrine_Query $q, $field, $value)
  {
    $a = $q->getRootAlias();
    
    if ( $value )
    {
      foreach ( array($q,$this->tickets_having_query) as $query )
      {
        if ( !$query->contains("LEFT JOIN $a.Transactions transac ON $a.id = transac.contact_id AND (p.id = transac.professional_id OR transac.professional_id IS NULL)") )
        $query->leftJoin("$a.Transactions transac ON $a.id = transac.contact_id AND (p.id = transac.professional_id OR transac.professional_id IS NULL)");
        
        if ( !$query->contains("LEFT JOIN transac.Tickets tck ON transac.id = tck.transaction_id AND (tck.printed_at IS NOT NULL OR tck.integrated_at IS NOT NULL) AND tck.id NOT IN (SELECT ttck.cancelling FROM ticket ttck WHERE ttck.cancelling IS NOT NULL)") )
        $query->leftJoin('transac.Tickets tck ON transac.id = tck.transaction_id AND (tck.printed_at IS NOT NULL OR tck.integrated_at IS NOT NULL) AND tck.id NOT IN (SELECT ttck.cancelling FROM ticket ttck WHERE ttck.cancelling IS NOT NULL)');
      }
      
      $this->tickets_having_query->having('sum(tck.value) >= ?',$value);
      foreach ( $this->tickets_having_query->fetchArray() as $c )
        $ids[] = $c['id'];
      
      $q->andWhereIn("$a.id",$ids);
    }
    
    return $q;
  }
  public function addTicketsAmountMaxColumnQuery(Doctrine_Query $q, $field, $value)
  {
    $a = $q->getRootAlias();
    
    if ( $value )
    {
      foreach ( array($q,$this->tickets_having_query) as $query )
      {
        if ( !$query->contains("LEFT JOIN $a.Transactions transac ON $a.id = transac.contact_id AND (p.id = transac.professional_id OR transac.professional_id IS NULL)") )
        $query->leftJoin("$a.Transactions transac ON $a.id = transac.contact_id AND (p.id = transac.professional_id OR transac.professional_id IS NULL)");
        
        if ( !$query->contains("LEFT JOIN transac.Tickets tck ON transac.id = tck.transaction_id AND (tck.printed_at IS NOT NULL OR tck.integrated_at IS NOT NULL) AND tck.id NOT IN (SELECT ttck.cancelling FROM ticket ttck WHERE ttck.cancelling IS NOT NULL)") )
        $query->leftJoin('transac.Tickets tck ON transac.id = tck.transaction_id AND (tck.printed_at IS NOT NULL OR tck.integrated_at IS NOT NULL) AND tck.id NOT IN (SELECT ttck.cancelling FROM ticket ttck WHERE ttck.cancelling IS NOT NULL)');
      }
      
      $this->tickets_having_query->having('sum(tck.value) < ?',$value);
      foreach ( $this->tickets_having_query->fetchArray() as $c )
        $ids[] = $c['id'];
      
      $q->andWhereIn("$a.id",$ids);
    }
    
    return $q;
  }

  public function addGroupsListColumnQuery(Doctrine_Query $q, $field, $value)
  {
    $a = $q->getRootAlias();
    
    if ( is_array($value) )
    {
      if ( !$q->contains("LEFT JOIN $a.Groups gc") )
        $q->leftJoin("$a.Groups gc");
      
      if ( !$q->contains("LEFT JOIN p.Groups gp") )
        $q->leftJoin("p.Groups gp");
      
      $q->andWhere('(TRUE')
        ->andWhereIn("gc.id",$value)
        ->orWhereIn("gp.id",$value)
        ->andWhere('TRUE)');
    }
    
    return $q;
  }
  public function addNotGroupsListColumnQuery(Doctrine_Query $q, $field, $value)
  {
    $a = $q->getRootAlias();
    
    if ( is_array($value) )
    {
      $q1 = new Doctrine_Query();
      $q1->select('tmp1.contact_id')
        ->from('GroupContact tmp1')
        ->andWhereIn('tmp1.group_id',$value);
      $q2 = new Doctrine_Query();
      $q2->select('tmp2.professional_id')
        ->from('GroupProfessional tmp2')
        ->andWhereIn('tmp2.group_id',$value);
      
      $q->andWhere("$a.id NOT IN ($q1)",$value) // hack for inserting $value
        ->andWhere("p.id IS NULL OR p.id NOT IN ($q2)",$value); // hack for inserting $value
    }
    
    return $q;
  }
  public function addHasCategoryColumnQuery(Doctrine_Query $q, $field, $value)
  {
    if ( $value === '' )
      return $q;
    
    $a = $q->getRootAlias();
    if ( $value )
      return $q->addWhere("$a.organism_category_id IS NOT NULL AND (o.organism_category_id IS NOT NULL OR o.id IS NULL)");
    else
      return $q->addWhere("$a.organism_category_id IS     NULL AND (o.organism_category_id IS     NULL)");
  }
  public function addHasAddressColumnQuery(Doctrine_Query $q, $field, $value)
  {
    if ( $value === '' )
      return $q;
    
    $a = $q->getRootAlias();
    if ( $value )
      return $q->addWhere("$a.postalcode IS NOT NULL AND $a.postalcode != '' AND $a.city IS NOT NULL AND $a.postalcode != ''");
    else
      return $q->addWhere("$a.postalcode IS     NULL OR $a.postalcode = '' OR $a.city IS     NULL OR $a.city = ''");
  }
  public function addHasEmailColumnQuery(Doctrine_Query $q, $field, $value)
  {
    if ( $value === '' )
      return $q;
    
    $a = $q->getRootAlias();
    if ( $value )
      return $q->addWhere("$a.email IS NOT NULL AND $a.email != '' OR p.contact_email IS NOT NULL AND p.contact_email != ''");
    else
      return $q->addWhere("($a.email IS     NULL OR $a.email = '') AND (p.contact_email IS NULL OR p.contact_email = '')");
  }
  public function addEmailNewsletterColumnQuery(Doctrine_Query $q, $field, $value)
  {
    if ( $value === '' )
      return $q;
    
    $a = $q->getRootAlias();
    if ( $value )
      return $q->addWhere("$a.email_no_newsletter = FALSE OR p.contact_email_no_newsletter = FALSE");
    else
      return $q->addWhere("$a.email_no_newsletter = TRUE AND p.contact_email_no_newsletter = TRUE");
  }
  public function addEmailColumnQuery(Doctrine_Query $q, $field, $values)
  {
    $a = $q->getRootAlias();
    if (is_array($values) && isset($values['is_empty']) && $values['is_empty'])
      $q->addWhere(sprintf('((%s.email IS NULL OR %1$s.email = ?) AND (p.contact_email IS NULL OR p.contact_email = ?))', $q->getRootAlias()), array('',''));
    else if (is_array($values) && isset($values['text']) && '' != $values['text'])
      $q->addWhere(sprintf('%s.email ILIKE ? OR p.contact_email ILIKE ?', $q->getRootAlias(), 'email'), array('%'.$values['text'].'%', '%'.$values['text'].'%'));
    return $q;
  }
  public function addProfessionalTypeIdColumnQuery(Doctrine_Query $q, $field, $value)
  {
    $a = $q->getRootAlias();
    if ( $value )
    {
      $this->setProfessionalData(true);
      $q->andWhereIn("pt.id",$value);
    }
    return $q;
  }
  public function addOrganismIdColumnQuery(Doctrine_Query $q, $field, $value)
  {
    $this->setProfessionalData(true);
    $a = $q->getRootAlias();
    if ( $value )
    {
      $this->setProfessionalData(true);
      $q->addWhere("o.id = ?",$value);
    }
    return $q;
  }
  public function addOrganismCategoryIdColumnQuery(Doctrine_Query $q, $field, $value)
  {
    $a = $q->getRootAlias();
    if ( $value )
    {
      $this->setProfessionalData(true);
      $q->andWhere('(TRUE')
        ->andWhereIn('o.organism_category_id',$value)
        ->orWhereIn("$a.organism_category_id",$value)
        ->andWhere('TRUE)');
    }
    return $q;
  }
  public function addYOBColumnQuery(Doctrine_Query $q, $field, $value)
  {
    if ( $value['from'] )
      $q->addWhere('y.year >= ?',date('Y',strtotime($value['from'])));
    if ( $value['to'] )
      $q->addWhere('y.year <= ?',date('Y',strtotime($value['to'])));
    
    return $q;
  }
  public function addPostalcodeColumnQuery(Doctrine_Query $q, $field, $value)
  {
    $c = $q->getRootAlias();
    if ( $value['text'] )
      $q->addWhere("$c.postalcode LIKE ? OR (o.id IS NOT NULL AND o.postalcode LIKE ?)",array($value['text'].'%',$value['text'].'%'));
    
    return $q;
  }
  
  // member cards
  public function addMemberCardsColumnQuery(Doctrine_Query $q, $field, $value)
  {
    $c = $q->getRootAlias();
    if ( count($value) > 0 )
    {
      if ( !$q->contains("LEFT JOIN $c.MemberCards mc") )
        $q->leftJoin("$c.MemberCards mc");
      $q->andWhereIn("mc.member_card_type_id",$value);
   }
    
    return $q;
  }
  public function addMemberCardsExpireAtColumnQuery(Doctrine_Query $q, $field, $value)
  {
    $c = $q->getRootAlias();
    if ( $value )
    {
      if ( !$q->contains("LEFT JOIN $c.MemberCards mc") )
        $q->leftJoin("$c.MemberCards mc");
      
      $q->andWhere("mc.expire_at > ?",date('Y-m-d',strtotime($value)));
    }
    
    return $q;
  }

  // checkpoints / flow management
  public function addControlManifestationIdColumnQuery(Doctrine_Query $q, $field, $values)
  {
    $a = $q->getRootAlias();
    
    if ( $values )
    {
      if ( !$q->contains("LEFT JOIN $a.Transactions transac") )
      $q->leftJoin("$a.Transactions transac");
      
      if ( !$query->contains("LEFT JOIN transac.Tickets tck ON transac.id = tck.transaction_id AND (tck.printed_at IS NOT NULL OR tck.integrated_at IS NOT NULL) AND tck.id NOT IN (SELECT ttck.cancelling FROM ticket ttck WHERE ttck.cancelling IS NOT NULL)')") )
      $query->leftJoin('transac.Tickets tck ON transac.id = tck.transaction_id AND (tck.printed_at IS NOT NULL OR tck.integrated_at IS NOT NULL) AND tck.id NOT IN (SELECT ttck.cancelling FROM ticket ttck WHERE ttck.cancelling IS NOT NULL)');
      
      if ( !$q->contains('LEFT JOIN tck.Controls ctrl') )
      $q->leftJoin('tck.Controls ctrl');
      
      $q->andWhere('ctrl.id IS NOT NULL')
        ->andWhereIn('tck.manifestation_id',$values);
    }
    
    return $q;
  }
  public function addControlCheckpointIdColumnQuery(Doctrine_Query $q, $field, $values)
  {
    $a = $q->getRootAlias();
    if ( $values )
    {
      if ( !$q->contains("LEFT JOIN $a.Transactions transac") )
      $q->leftJoin("$a.Transactions transac");
      
      if ( !$query->contains("LEFT JOIN transac.Tickets tck ON transac.id = tck.transaction_id AND (tck.printed_at IS NOT NULL OR tck.integrated_at IS NOT NULL) AND tck.id NOT IN (SELECT ttck.cancelling FROM ticket ttck WHERE ttck.cancelling IS NOT NULL)')") )
      $query->leftJoin('transac.Tickets tck ON transac.id = tck.transaction_id AND (tck.printed_at IS NOT NULL OR tck.integrated_at IS NOT NULL) AND tck.id NOT IN (SELECT ttck.cancelling FROM ticket ttck WHERE ttck.cancelling IS NOT NULL)');
      
      if ( !$q->contains('LEFT JOIN tck.Controls ctrl') )
      $q->leftJoin('tck.Controls ctrl');
      
      if ( !$q->contains('LEFT JOIN ctrl.Checkpoint check') )
      $q->leftJoin('ctrl.Checkpoint check');
      
      $q->andWhereIn('check.id',$values);
    }
    
    return $q;
  }
  public function addControlCreatedAtColumnQuery(Doctrine_Query $q, $field, $values)
  {
    $a = $q->getRootAlias();
    $fieldName = 'created_at';
      
    if (isset($values['is_empty']) && $values['is_empty'])
    {
      $q->addWhere(sprintf('%s.%s IS NULL', 'ctrl', $fieldName));
    }
    else
    {
      if ( !$q->contains("LEFT JOIN $a.Transactions transac") )
      $q->leftJoin("$a.Transactions transac");
      
      if ( !$query->contains("LEFT JOIN transac.Tickets tck ON transac.id = tck.transaction_id AND (tck.printed_at IS NOT NULL OR tck.integrated_at IS NOT NULL) AND tck.id NOT IN (SELECT ttck.cancelling FROM ticket ttck WHERE ttck.cancelling IS NOT NULL)')") )
      $query->leftJoin('transac.Tickets tck ON transac.id = tck.transaction_id AND (tck.printed_at IS NOT NULL OR tck.integrated_at IS NOT NULL) AND tck.id NOT IN (SELECT ttck.cancelling FROM ticket ttck WHERE ttck.cancelling IS NOT NULL)');
      
      if ( !$q->contains('LEFT JOIN tck.Controls ctrl') )
      $q->leftJoin('tck.Controls ctrl');
      
      if (null !== $values['from'] && null !== $values['to'])
      {
        $q->andWhere(sprintf('%s.%s >= ?', 'ctrl', $fieldName), $values['from'])
          ->andWhere(sprintf('%s.%s <= ?', 'ctrl', $fieldName), $values['to']);
      }
      else if (null !== $values['from'])
      {
        $q->andWhere(sprintf('%s.%s >= ?', 'ctrl', $fieldName), $values['from']);
      }
      else if (null !== $values['to'])
      {
        $q->andWhere(sprintf('%s.%s <= ?', 'ctrl', $fieldName), $values['to']);
      }
    }

    return $q;
  }

  protected function setProfessionalData($bool)
  {
    return $this->showProfessionalData = $bool;
  }
  public function showProfessionalData()
  {
    return $this->showProfessionalData;
  }
  public function buildQuery(array $values)
  {
    $this->setProfessionalData(false);
    
    // to limit execution time
    $q = parent::buildQuery($values);
    $a = $q->getRootAlias();
    $q->select("$a.*, p.*, o.*, pn.*, y.*, pt.*, oph.*, gc.*, gp.*, go.*");
    
    return $q;
  }
  
  protected function getEventArchivesChoices()
  {
    $names = Doctrine::getTable('ContactEventArchives')->createQuery('a')
      ->select('DISTINCT a.name')
      ->orderBy('name')
      ->fetchArray();
    
    $choices = array();
    foreach ( $names as $name )
      $choices[$name['name']] = $name['name'];
    
    return $choices;
  }
}
