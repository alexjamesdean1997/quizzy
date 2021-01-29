<?php
// src/Controller/HomeController.php
namespace App\Controller;

use App\Entity\Question;
use App\Repository\AnswerRepository;
use App\Repository\QuestionRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Security;

class DashboardController extends AbstractController
{

    /**
     * @var Security
     */
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    /**
     * @Route("/dashboard", name="dashboard")
     */
    public function index(QuestionRepository $questionRepository, AnswerRepository $answerRepository)
    {
        $user = $this->security->getUser();

        $answers = $answerRepository->findBy(
            ['user' => $user]
        );

        $questions_answered = count($answers);
        $success_count = 0;

        foreach ($answers as $answer){
            if($answer->getSuccess()){
                $success_count = $success_count + 1;
            }
        }

        $success_rate = ($success_count / $questions_answered) * 100;

        $stats = [];
        $stats['success-rate'] = round($success_rate, 2);
        $stats['rank'] = 34;
        $stats['score'] = $success_count;

        return $this->render('dashboard.html.twig', [
            'stats' => $stats
        ]);
    }
}