<?php
namespace MainBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

use MainBundle\Entity\Browser;
use MainBundle\Entity\UserEnvironment;
use MainBundle\Entity\Visitor;
use MainBundle\Form\Type\VisitorType;
//use MainBundle\Lib\GuestBookFormHelpers as Helpers;
require_once __DIR__ . "/../Lib/GuestBookFormHelpers.php";

class GuestBookController extends Controller {
    
    /**
     * Displays the GuestBook form.
     * 
     * @Route("/forms/guestbook/display", name="formGuestBookDisplay", 
     * methods={"GET"}))
     */
    public function guestBookDisplayAction(Request $request) {
        $formEntity = new Visitor();
        $formEntity->setName("n");
        $formEntity->setAddress("a");
        $formEntity->setEmail("e@h");
        $formEntity->setMessage("m");
        $form = $this->createForm(VisitorType::class, $formEntity);
        $form->handleRequest($request);

        return $this->render(
            "@Main/GuestBookForm/FormDisplay.html.twig", 
            array(
                "form" => $form->createView()
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
        $receivedVisitor = json_decode($request->getContent(), true);
        $ipAddress = getenv('HTTP_CLIENT_IP')?:
            getenv('HTTP_X_FORWARDED_FOR')?:
            getenv('HTTP_X_FORWARDED')?:
            getenv('HTTP_FORWARDED_FOR')?:
            getenv('HTTP_FORWARDED')?:
            getenv('REMOTE_ADDR');
        
        // Get these values from config parameters
        $secret = $this->getParameter("recaptcha_secret");
        $expectedHostName = $this->getParameter("host_name");
        $gRecaptchaResponse = $receivedVisitor["captcha"];
       
        $recaptcha = new \ReCaptcha\ReCaptcha($secret);
        $resp = $recaptcha->setExpectedHostname($expectedHostName)
                          ->verify($gRecaptchaResponse, $ipAddress);
        $recaptchaRes = "";
        if ($resp->isSuccess()) {
            // Verified!
            $recaptchaRes = "ok";
        } else {
            $errors = $resp->getErrorCodes();
            return new Reponse("Problem with captcha submission", 500);
        }
  
        $browserDetected = new \Sinergi\BrowserDetector\Browser();
        $platformDetected = new \Sinergi\BrowserDetector\Os();
        $receivedBrowser = new \MainBundle\Entity\Browser();
        $receivedBrowser->setName($browserDetected->getName());
        $receivedBrowser->setVersion($browserDetected->getVersion());
        
        $receivedUserEnvironment = new UserEnvironment();
        
        $receivedUserEnvironment->setIPAddress($ipAddress);
        $receivedUserEnvironment->setPlatform($platformDetected->getName());
        
        $result = addRelatedEntries($this->getDoctrine(), 
            $receivedVisitor, 
            $receivedUserEnvironment, 
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
    public function guestBookListAction(Request $request) {
        $visitors = $this->getDoctrine()
                ->getRepository('MainBundle:Visitor')
                ->findVisitorsJoin();
        
        return new JsonResponse($visitors);
    
    }
}