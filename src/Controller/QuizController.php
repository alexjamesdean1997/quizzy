<?php
// src/Controller/HomeController.php
namespace App\Controller;

use App\Entity\Question;
use App\Repository\QuestionRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class QuizController extends AbstractController
{

    /**
     * @Route("/quiz", name="quiz")
     */
    public function quiz()
    {

        return $this->render('quiz.html.twig', [

        ]);
    }
}