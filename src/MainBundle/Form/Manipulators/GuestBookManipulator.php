<?php
namespace MainBundle\Form\Manipulators;

use Doctrine\ORM\Tools\Pagination\Paginator as DoctrinePaginator;
use JasonGrimes\Paginator as SpecialPaginator;

use MainBundle\Entity\Visitor;
use MainBundle\Entity\Browser;

/**
 * Lays out methods to manipulate GuestBook form and results information.
 *
 */
class GuestBookManipulator implements IStandardManipulator {
    private $doctrineObj; 
    
    /**
     * Keeps a single instance of Doctrine manager.
     * @param type $doctrineObject
     */
    public function setDoctrineObject($doctrineObject) {
        if ($this->doctrineObj == null) {
            $this->doctrineObj = $doctrineObject;
        }
    }
    
    /**
     * Sets default field values on an entity.
     * @return Visitor
     */
    public function getDefaultEntity() {
        $formEntity = new Visitor();
        $formEntity->setName("name");
        $formEntity->setAddress("address");
        $formEntity->setEmail("email@test.com");
        $formEntity->setMessage("message");
        return $formEntity;
    }
    
    /**
     * Run sa paginated query and gets a representation of paginated results for
     * displaying in a web element.
     * @return SpecialPaginator
     */
    public function getPaginatorForDisplay() {
        $entityManager = $this->doctrineObj->getManager();
        // Paginating
        $dql = "SELECT count(v.id) FROM MainBundle:Visitor v";
        $totalItems = $entityManager->createQuery($dql)
            ->getSingleScalarResult();
        $itemsPerPage = 10;
        $currentPage = 1;
        $urlPattern = '/entries/get/(:num)';

        $specialPaginator = new SpecialPaginator($totalItems, $itemsPerPage, 
                $currentPage, $urlPattern);
        return $specialPaginator;
    }
    
    /**
     * Retrieves and stores information on the user's client environment.
     * @return array
     */
    public function getUserEnvironmentVariables() {
        $ipAddress = getenv('HTTP_CLIENT_IP')?:
           getenv('HTTP_X_FORWARDED_FOR')?:
           getenv('HTTP_X_FORWARDED')?:
           getenv('HTTP_FORWARDED_FOR')?:
           getenv('HTTP_FORWARDED')?:
           getenv('REMOTE_ADDR');
         
        // Use the php-browser-detector library to get info on the visitor
        $browserDetected = new \Sinergi\BrowserDetector\Browser();
        $platformDetected = new \Sinergi\BrowserDetector\Os();
        $receivedBrowser = new \MainBundle\Entity\Browser();
        $receivedBrowser->setName($browserDetected->getName());
        $receivedBrowser->setVersion($browserDetected->getVersion());
        
        $receivedUserEnvironment = new UserEnvironment();
        $receivedUserEnvironment->setIPAddress($ipAddress);
        $receivedUserEnvironment->setPlatform($platformDetected->getName());
        
        return array("receivedBrowser"=> $receivedBrowser,
            "receivedUserEnvironment"=> $receivedUserEnvironment, 
            );
    }
    
    /**
     * Fetches a simple set of all results.
     * @return array
     */
    public function getPlainResults() {
        $visitors = $this->doctrineObj->getManager()
            ->getRepository('MainBundle:Visitor')
            ->findVisitorsJoin();
        return $visitors;
    }
    
    /**
     * Fetches a set of paginated query results.
     * @param type $startMultiplier
     * @return array
     */
    public function getPaginatedResults($startMultiplier) {
        $entityManager = $this->doctrineObj->getManager();
        $paginationAmount = 10;
        $startIndex = ($startMultiplier - 1) * $paginationAmount;
        $dql = "SELECT v, u FROM MainBundle:Visitor v JOIN v.userEnvironment u";
        $query = $entityManager->createQuery($dql)
            ->setFirstResult($startIndex)
            ->setMaxResults($paginationAmount);
        $paginator = new DoctrinePaginator($query, $fetchJoinCollection = true);

        $rows = array();
        foreach ($paginator as $pageRes) {
            //Unpack
            $row = array(
                "Name"=> $pageRes->getName(),
                "Address"=> $pageRes->getEmail(),
                "Email"=> $pageRes->getAddress(),
                "Message"=> $pageRes->getMessage(),
                "IpAddress"=> $pageRes->getUserEnvironment()->getIPAddress(),
                "Platform"=> $pageRes->getUserEnvironment()->getPlatform()
            );
            $rows[] = $row;
        }
        return $rows;
    }
}
