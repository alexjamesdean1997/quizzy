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
    public function quiz(QuestionRepository $questionRepository)
    {
        $allQuestions = $questionRepository->findAll();
        $categories = [];
        foreach ($allQuestions as $question){
            if (!in_array($question->getCategory(), $categories)) {
                $categories[] = $question->getCategory();
            }
        }

        return $this->render('quiz.html.twig', [
            "categories" => $categories
        ]);
    }

    /**
     * @Route("/quiz/{category}", name="quiz_difficulty")
     */
    public function difficulty($category)
    {

        return $this->render('difficulties.html.twig', [
            "category" => $category
        ]);
    }

    /**
     * @Route("/quiz/{category}/{difficulty}", name="quiz_category")
     */
    public function category(QuestionRepository $questionRepository, $category, $difficulty)
    {
        //dump($difficulty);die();
        if($difficulty === 'easy'){
            $difficulty = 'débutant';
        }elseif($difficulty === 'confirmed'){
            $difficulty = 'confirmé';
        }

        if($difficulty === 'débutant' || $difficulty === 'confirmé' || $difficulty === 'expert'){
            if($category == 'tech'){
                $internetQuestions = $questionRepository->findBy(
                    ['category' => 'internet','difficulty' => $difficulty]
                );
                $computerQuestions = $questionRepository->findBy(
                    ['category' => 'informatique','difficulty' => $difficulty]
                );
                $questions = array_merge($internetQuestions, $computerQuestions);
            }elseif($category == 'aleatoire') {
                $questions = $questionRepository->findBy(
                    ['difficulty' => $difficulty]
                );
            }else{
                $questions = $questionRepository->findBy(
                    ['category' => $category,'difficulty' => $difficulty]
                );
            }
        }else{
            if($category == 'tech'){
                $internetQuestions = $questionRepository->findBy(
                    ['category' => 'internet']
                );
                $computerQuestions = $questionRepository->findBy(
                    ['category' => 'informatique']
                );
                $questions = array_merge($internetQuestions, $computerQuestions);
            }elseif($category == 'aleatoire') {
                $questions = $questionRepository->findAll();
            }else{
                $questions = $questionRepository->findBy(
                    ['category' => $category]
                );
            }
        }

        $sessionQuestions = $this->randomize($questions);

        foreach($sessionQuestions as $sessionQuestion) {
            $choices = $sessionQuestion->getChoices();
            shuffle($choices);
            $sessionQuestion->setChoices($choices);
        }

        return $this->render('category.html.twig', [
            "category" => $category,
            "difficulty" => $difficulty,
            "questions" => $sessionQuestions
        ]);
    }

    public function randomize($questions){
        $rand_keys = array_rand($questions, 10);
        $sessionQuestions = [];

        foreach($rand_keys as $item) {
            $sessionQuestions[] = $questions[$item];
        }

        return $sessionQuestions;
    }


    /**
     * @Route("/getcorrectanswers/ajax", name="get_correct_answers")
     */
    public function getCorrectAnswers(Request $request, QuestionRepository $questionRepository)
    {

        $questionId = json_decode($request->query->get('data'), true);

        $answer = $questionRepository->find($questionId)->getCorrectAnswer();

        $response = array(
            "code" => 200,
            "answer" => $answer
        );
        return new JsonResponse($response);
    }
}