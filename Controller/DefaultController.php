<?php

namespace Darkwood\GeneratorBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('DarkwoodGeneratorBundle:Default:index.html.twig', array('name' => $name));
    }
}
