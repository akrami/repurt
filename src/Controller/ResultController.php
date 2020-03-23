<?php


namespace App\Controller;


use App\Entity\Result;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ResultController
 * @package App\Controller
 * @Route("/result")
 */
class ResultController extends AbstractController
{
    /**
     * @Route("/{id}", name="result_show", methods={"GET"})
     * @param Result $result
     * @return Response
     */
    public function show(Result $result): Response
    {
        return $this->render('result/show.html.twig', ['result' => $result]);
    }
}