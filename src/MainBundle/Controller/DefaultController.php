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

class DefaultController extends Controller {

    /**
     * 
     * @Route("/index", name="home", 
     * methods={"GET"}))
     */
    public function indexAction() {
        return $this->render("@Main/Default/index.html.twig"
        );
    }

}
