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

class HomeController extends AbstractController
{

    /**
     * @Route("/", name="home")
     */
    public function index()
    {

        return $this->render('home.html.twig', [

        ]);
    }

    /**
     * @Route("/savequestion/ajax", name="save_question")
     */
    public function saveQuestion(Request $request, QuestionRepository $questionRepository)
    {

        $data = json_decode($request->query->get('data'), true);
        $em = $this->getDoctrine()->getManager();
        $allQuestions = $questionRepository->findAll();

        $questionsCount = count($allQuestions);

        if($data && $data["response_code"] === 0){

            $message = null;

            foreach ($allQuestions as $question){
                if($question->getQuestion() === $data["results"][0]["question"]){
                    $message = 'question already exists in database';
                    $response = array(
                        "code" => 200,
                        "message" => $message,
                        "duplicate" => true,
                        "count" => $questionsCount
                    );
                    return new JsonResponse($response);
                }
            }

            $newQuestion = new Question();
            $newQuestion->setLanguage($data["results"][0]["langue"]);
            $newQuestion->setCategory($data["results"][0]["categorie"]);
            $newQuestion->setQuestion($data["results"][0]["question"]);
            $newQuestion->setCorrectAnswer($data["results"][0]["reponse_correcte"]);
            $newQuestion->setTheme($data["results"][0]["theme"]);
            $newQuestion->setDifficulty($data["results"][0]["difficulte"]);
            $newQuestion->setChoices($data["results"][0]["autres_choix"]);

            $em->persist($newQuestion);
            $em->flush();

            $message = 'saved question';
            $response = array(
                "code" => 200,
                "message" => $message,
                "duplicate" => false,
                "count" => $questionsCount + 1
            );
            return new JsonResponse($response);

        }else{
            $response = array(
                "code" => 400,
            );
            return new JsonResponse($response);
        }
    }
}