<?php
// src/Controller/HomeController.php
namespace App\Controller;

use App\Entity\Answer;
use App\Entity\Question;
use App\Entity\User;
use App\Repository\AnswerRepository;
use App\Repository\QuestionRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManager;
use Faker\Factory;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Security;

class DashboardController extends AbstractController
{

    /**
     * @var Security
     */
    private $security;
    private $passwordEncoder;
    private $faker;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder, Security $security)
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->security = $security;
    }

    /**
     * @Route("/dashboard", name="dashboard")
     */
    public function index(AnswerRepository $answerRepository, UserRepository $userRepository)
    {
        $user = $this->security->getUser();
        $currentUserId = $user->getId();

        $answers = $answerRepository->findBy(
            ['user' => $user]
        );

        $questions_answered = count($answers);
        $score = 0;
        $success_count = 0;

        foreach ($answers as $answer){

            if($answer->getSuccess()){
                $success_count = $success_count + 1;
                $difficulty = $answer->getQuestion()->getDifficulty();

                if($difficulty === 'débutant'){
                    $score = $score + 1;
                }elseif ($difficulty === 'confirmé'){
                    $score = $score + 2;
                }elseif ($difficulty === 'expert'){
                    $score = $score + 3;
                }
            }
        }

        if($questions_answered){
            $success_rate = ($success_count / $questions_answered) * 100;
        }else{
            $success_rate = 0;
        }

        $allUsers = $userRepository->findAll();
        $user_scores = [];

        foreach ($allUsers as $aUser){
            $answers = $aUser->getAnswers();
            $user_score = 0;

            foreach ($answers as $answer){
                if($answer->getSuccess()){
                    $difficulty = $answer->getQuestion()->getDifficulty();

                    if($difficulty === 'débutant'){
                        $user_score = $user_score + 1;
                    }elseif ($difficulty === 'confirmé'){
                        $user_score = $user_score + 2;
                    }elseif ($difficulty === 'expert'){
                        $user_score = $user_score + 3;
                    }
                }
            }

            $userId = $aUser->getId();
            $user_scores[$userId] = $user_score;
        }

        arsort($user_scores);

        $stringUserId = strval($currentUserId);

        $rank = array_search($stringUserId,array_keys($user_scores)) + 1;

        $stats = [];
        $stats['success-rate'] = round($success_rate, 2);
        $stats['rank'] = $rank;
        $stats['score'] = $score;

        return $this->render('dashboard.html.twig', [
            'stats' => $stats
        ]);
    }

    /**
     * @Route("/loadanswers", name="load_answers")
     */
    /*public function answerfixtures(UserRepository $userRepository, QuestionRepository $questionRepository)
    {

        $em = $this->getDoctrine()->getManager();
        $users = $userRepository->findAll();

        foreach ($users as $user){
            $answers_count = rand(5,10);

            for ($i = 1; $i <= $answers_count; $i++) {
                $answer = new Answer();
                $questionId = rand(867,6233);
                $question = $questionRepository->find($questionId);
                $answer->setUser($user);
                $answer->setQuestion($question);
                $answer->setCreatedAt(new \DateTime());
                $success = rand(0,1);
                $answer->setSuccess($success);
                $em->persist($answer);
                $em->flush();
            }
        }
        return $this->render('loadanswers.html.twig', [

        ]);
    }*/

    /**
     * @Route("/loadusers", name="load_users")
     */
    /*public function usersfixtures()
    {
        $this->faker = Factory::create();
        $em = $this->getDoctrine()->getManager();

        for ($i = 1; $i <= 20; $i++) {
            $user = new User();
            $firstname = strtolower($this->faker->firstName);
            $lastname = strtolower($this->faker->lastName);
            $user->setFirstName($firstname);
            $user->setLastName($lastname);
            $user->setEmail($firstname.'.'.$lastname.'@gmail.com');
            $user->setRoles(['ROLE_USER']);
            $user->setAvatar(rand(0,6));
            $user->setPassword($this->passwordEncoder->encodePassword($user, 'admin123'));
            $em->persist($user);
            $em->flush();
        }

        return $this->render('loadusers.html.twig', [

        ]);
    }*/
}