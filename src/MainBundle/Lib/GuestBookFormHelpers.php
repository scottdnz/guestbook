<?php

use MainBundle\Entity\Browser;
use MainBundle\Entity\UserEnvironment;
use MainBundle\Entity\Visitor;

/*
 * A helper method that goes through the logic of inserting related entities
 * to do with GuestBook entries to the database with Doctrine.
 */
function addRelatedEntries($doctrineManager, $visitor, $userEnvironment, 
    $browser) {
    $entityManager = $doctrineManager->getManager();
        
    // Check if browser exists
    $browserToStore = $doctrineManager
        ->getRepository('MainBundle:Browser')
        ->findOneBy(array(
            "name" => $browser->getName(),
            "version" => $browser->getVersion()
            )
        );
    
    // If it doesn't exist, add a new browser record
    if (! $browserToStore) {
        $browserToStore = new Browser();
        $browserToStore->setName($browser->getName());
        $browserToStore->setVersion($browser->getVersion());
        $entityManager->persist($browserToStore);
    }
    
    // Store a UserEnvironment with the related browser
    $userEnvToStore = new UserEnvironment();
    $userEnvToStore->setIPAddress($userEnvironment->getIPAddress());
    $userEnvToStore->setPlatform($userEnvironment->getPlatform());
    $userEnvToStore->setBrowser($browserToStore);
    $entityManager->persist($userEnvToStore);
    
    // Store a Visitor with the related userEnvironment
    $visitorToStore = new Visitor();
    $visitorToStore->setName($visitor["name"]);
    $visitorToStore->setAddress($visitor["address"]);
    $visitorToStore->setEmail($visitor["email"]);
    $visitorToStore->setMessage($visitor["message"]);
    $visitorToStore->setCaptcha($visitor["captcha"]);
    $visitorToStore->setUserEnvironment($userEnvToStore);
    $entityManager->persist($visitorToStore);

    $entityManager->flush();
    return $visitorToStore;
}

