<?php
namespace MainBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\Tools\Pagination\Paginator as DoctrinePaginator;
use Psr\Log\LoggerInterface;

use JasonGrimes\Paginator as SpecialPaginator;
use MainBundle\Entity\Browser;
use MainBundle\Entity\UserEnvironment;
use MainBundle\Entity\Visitor;
use MainBundle\Form\Type\VisitorType;
use MainBundle\Form\Manipulators\GuestBookManipulator;
require_once __DIR__ . "/../Lib/GuestBookFormHelpers.php";
//require_once __DIR__ . "/../Form/Manipulators/GuestBookManipulator.php";

class GuestBookController extends Controller {
    private $manipulator;
    
    public function __construct() {
        $this->manipulator = new GuestBookManipulator();
    }
    
    /**
     * Displays the GuestBook form.
     * 
     * @Route("/forms/guestbook/display", name="formGuestBookDisplay", 
     * methods={"GET"}))
     */
    public function guestBookDisplayAction(Request $request) { 
        $this->manipulator->setDoctrineObject($this->getDoctrine());
        $form = $this->createForm(VisitorType::class, 
                $this->manipulator->getDefaultEntity());
        $form->handleRequest($request);

        return $this->render(
            "@Main/GuestBookForm/FormDisplay.html.twig", 
            array(
                "form" => $form->createView(),
                "paginator"=> $this->manipulator->getPaginatorForDisplay()
            )
        );
    }
    
    /**
     * Handles the GuestBook form submissions.
     * 
     * @Route("/forms/guestbook/submit", name="formGuestBookSubmit", 
     * methods={"POST"})
     */
    public function guestBookSubmitAction(Request $request) {
        $this->manipulator->setDoctrineObject($this->getDoctrine());
        // Parse the JSON request
        $receivedVisitor = json_decode($request->getContent(), true);
        $userEnv = $this->manipulator->getUserEnvironmentVariables();
        
        // Get these values from config parameters
        $secret = $this->getParameter("recaptcha_secret");
        $expectedHostName = $this->getParameter("host_name");
        $gRecaptchaResponse = $receivedVisitor["captcha"];
       
        // Verify the Google ReCaptcha field value
        $recaptcha = new \ReCaptcha\ReCaptcha($secret);
        $resp = $recaptcha->setExpectedHostname($expectedHostName)
            ->verify($gRecaptchaResponse, 
                    $userEnvs["receivedUserEnvironment"]->getIpAddress());
        if (! $resp->isSuccess()) {
            $errors = $resp->getErrorCodes();
            return new Reponse("Problem with captcha submission", 500);
        }
        
        $result = addRelatedEntries($this->getDoctrine(), 
            $userEnv["receivedVisitor"], 
            $userenv["receivedUserEnvironment"], 
            $receivedBrowser);
        
        return new Response($result->getId());
    }
    
    /**
     * Fetches stored entries from the database using a custom Doctrine
     * repo query.
     * 
     * @Route("/forms/guestbook/list", name="formGuestBookList", 
     * methods={"GET"})
     */
    public function guestBookListAction() {
        $this->manipulator->setDoctrineObject($this->getDoctrine());
        $visitors = $this->manipulator->getPlainResults();
        return new JsonResponse($visitors);
    }
    
    /**
     * Retrieves a set of paginated results.
     * 
     * @Route("/entries/get/{startMultiplier}", name="entriesGet", 
     * methods={"GET"}) 
     * @param Request $request
     */
    public function entriesPaginationResultGet($startMultiplier) {  
        $this->manipulator->setDoctrineObject($this->getDoctrine());
        $rows = $this->manipulator->getPaginatedResults($startMultiplier);
        return new JsonResponse($rows);
    }
}