# TotalCRM MicrosoftGraphBundle

## Installation

### Add MicrosoftGraphBundle to your project

The recommended way to install the bundle is through Composer.

```bash
$ composer require 'totalcrm/microsoft-graph-bundle'
```

Symfony 3: add MicrosoftGraphBundle in AppKernel.php registerBundles()
```php
$bundles = [
    ...,
    New TotalCRM\MicrosoftGraph\MicrosoftGraphBundle(),
];
```
Symfony 4 and up: add MicrosoftGraphBundle in bundles.php
```php
return [
    ...,
    TotalCRM\MicrosoftGraph\MicrosoftGraphBundle::class => ['all' => true],
];
```

## Configuration 
You have to configure your api.

Symfony 3: add to config.yml
 
Symfony 4 and up: create config/packages/microsoft_graph.yaml
``` yml
microsoft_graph:
    client_id: "%env(MS_GRAPH_CLIENT_ID)%"
    client_secret: "%env(MS_GRAPH_CLIENT_SECRET)%"
    tenant_id: "%env(MS_GRAPH_TENANT_ID)%"
    contact_folder: "%env(MS_GRAPH_CONTACT_FOLDER)%"
    redirect_uri: "app_dashboard"
    home_page: "app_dashboard"
    prefer_time_zone: "%env(MS_GRAPH_TIMEZONE_UTC)%" 
    version: "1.0"
    scopes:  # see more details https://developer.microsoft.com/en-us/graph/docs/authorization/permission_scopes
        - openid
        - offline_access
        - Contacts.Read
        - Contacts.ReadWrite
        - Contacts.ReadWrite.Shared
        - Calendars.Read
        - Calendars.Read.Shared
        - Calendars.ReadWrite
        - Tasks.ReadWrite
        ...
```

# Get token from Office 365 | API Graph
``` php
    /** @var MicrosoftGraphClient $graphClient */
    $graphClient = $container->get('microsoft_graph.client');

    try {
        /* if you have a refresh token then  the token will refresh */
        $graphClient->getNewToken();
    } catch(\Exception $ex) {
        /* return url by Authorization */

        $url = $graphClient->redirect();

        /*
        Follow the link $url and login in to Microsoft Office 365 Service
        After successful authorization, you should be redirected to the redirect_uri page with the code parameter, which you need to save
        See set token Office 365 auth and cached
        */
    }
```
# Set token Office 365 auth and cached
``` php
    /** @var MicrosoftGraphClient $graphClient */
    $graphClient = $container->get('microsoft_graph.client');
    $authorizationCode = "0.AQUAIIWUa9rYQEKaSsxrxOyxTP-AiocQKThAr3_TKz.......";

    try {
        $this->graphClient->setAuthorizationCode($authorizationCode);
    } catch (\Exception $exception) {
        if ($exception->getMessage() === 'invalid_grant') {
            /*
            OAuth2 Authorization code was already redeemed
            Please retry with a new valid code or use an existing refresh token
            */
        } else {
            /*
            Authorization code save error
            Please retry with a new valid code or use an existing refresh token
            */
        }
    }

```


# Example get contacts in folder
``` php
    /** @var MicrosoftGraphContactManager $contactManager */
    $contactManager = $container->get('microsoft_graph.contact_manager');
    
    //Get Contacts by Folder
    /** @var Microsoft\Graph\Model\Contact[] $folders */
    $folders = $contactManager->getContactFolders();
    dump($contacts);

    foreach ($folders as $folder) {
        /** @var Microsoft\Graph\Model\Contact[] $contacts */
        $contacts = $contactManager->getContacts($folder->getId());
        dump($contacts);
    }

    //Get All Contacts 
    /** @var Microsoft\Graph\Model\Contact[] $contacts */
    $contacts = $contactManager->getContacts();
    dump($contacts);
```

# Example get contact by id
``` php
    $id = '...';
    $contact = $contactManager->getContact($id);
    dump($contacts);
```
 # Create an contact
``` php

    /** @var Microsoft\Graph\Model\PhysicalAddress $businessAddress */
    $businessAddress = new Model\PhysicalAddress();
    $businessAddress
        ->setPostalCode('PostalCode')
        ->setCity('City')
        ->setState('State')
        ->setStreet('Street')
        ->setCountryOrRegion('Country')
    ;

    /** @var Microsoft\Graph\Model\EmailAddress $emailAddress */
    $emailAddress = new EmailAddress();
    $emailAddress
        ->setName('DisplayName')
        ->setAddress('email@gmail.com')
    ;
    
    /** @var Microsoft\Graph\Model\Contact $newContact */
    $newContact = new Model\Contact();
    $newContact
        ->setNickName('NickName')
        ->setDisplayName('DisplayName')
        ->setMiddleName('MiddleName')
        ->setGivenName('GivenName')
        ->setBusinessAddress($businessAddress)
        ->setEmailAddresses($emailAddress)
        ...
    ;

    $contact = $contactManager->addContact($newContact);
    dump($contact);
```

# Example get events from outlook calendar
``` php
// Get calendar service 
    $calendarManager = $this->get('microsoft_graph.calendar');
            
//Get a collection of Microsoft\Graph\Model\Event
    $startTime = new DateTime("first day of this month");
    $endTime = new DateTime("first day of next month");
    
    $events = $calendarManager->getEvents($startTime,$endTime);

//Get a  Microsoft\Graph\Model\Event
    $id='...';
    $event = $calendarManager->getEvent($id);
     
```
 # Create an event
``` php
//  create Microsoft\Graph\Model\Event and set properties
    $newEvent = new Microsoft\Graph\Model\Event();              
    $start = $calendar->getDateTimeTimeZone(new \DateTime('Now next minute'));
    $end = $calendar->getDateTimeTimeZone(new \DateTime('Now next hour'));
    
    $newEvent->setSubject('Controller Test Token');
    $newEvent->setStart($start);
    $newEvent->setEnd( $end);     

    $event= $calendarManager->addEvent( $newEvent);
    
    dump($event);
```
 # Update an event
``` php
    $id = '...';
    $updateEvent = new Microsoft\Graph\Model\Event(); 
    $updateEvent->setId($id);
    $updateEvent->setSubject("I Forgot The Eggs!");
    $event = $calendarManager->updateEvent($updateEvent);
``` 
 # Delete an event
``` php
    $id='...';
    $response = $calendar->deleteEvent($id);
    dump($response->getStatus()==204 ? "Event deleted" : $response);
```
