<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    /**
     * @Route("/home", name="home")
     */
    public function index()
    {
        return $this->render('home.html.twig',[
            'title' => 'REPURT',
            'description' => 'Welcome to our reporting platform'
        ]);
    }

    /**
     * @Route("/setting", name="setting")
     */
    public function setting()
    {
        return $this->render('main/setting.html.twig',[
            'title' => 'Setting'
        ]);
    }
}
