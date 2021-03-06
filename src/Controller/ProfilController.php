<?php
/**
 * Created with PHPStorm
 * Date: 31/7/2019
 * Time: 11:2
 * Author: S. Carpentier
 * Mail: sebastien.carpentier89@gmail.com
 */

namespace App\Controller;

use App\Controller\Interfaces\ProfilControllerInterface;
use App\Entity\Favorite;
use App\Entity\Recipe;
use App\Form\EditMailType;
use App\Form\EditPasswordType;
use App\Utils\RecipeUtils;
use App\Utils\UserUtils;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Twig\Environment;

/**
 * Class ProfilController
 * @package App\Controller
 */
class ProfilController implements ProfilControllerInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $manager;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    /**
     * @var Environment
     */
    private $twig;

    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * ProfilController constructor.
     * @param EntityManagerInterface $manager
     * @param TokenStorageInterface $tokenStorage
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param Environment $twig
     * @param FormFactoryInterface $formFactory
     */
    public function __construct(EntityManagerInterface $manager,
                                TokenStorageInterface $tokenStorage,
                                UserPasswordEncoderInterface $passwordEncoder,
                                Environment $twig,
                                FormFactoryInterface $formFactory)
    {
        $this->manager = $manager;
        $this->tokenStorage = $tokenStorage;
        $this->passwordEncoder = $passwordEncoder;
        $this->twig = $twig;
        $this->formFactory = $formFactory;
    }

    /**
     * @Route("/profil", name="profil", methods={"GET", "POST"})
     *
     * @param Request $request
     * @return Response
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function __invoke(Request $request):Response
    {
        $user = $this->getUser();

        $authoredRecipeImage = [];
        $authoredRecipeList = $this->manager
            ->getRepository(Recipe::class)
            ->getRecipeByAuthor($user);

        $userFavoritesRecipe = [];
        $userFavoritesImages = [];
        $userFavorites = $this->manager
            ->getRepository(Favorite::class)
            ->getUserFavorites($user);

        foreach ($authoredRecipeList as $item)
        {
            $image = RecipeUtils::getImageUri($item);
            array_push($authoredRecipeImage, $image);
        }

        foreach ($userFavorites as $favorite)
        {
            array_push($userFavoritesRecipe, $favorite->getRecipe());
            $favoriteImage = RecipeUtils::getImageUri($favorite->getRecipe());
            array_push($userFavoritesImages, $favoriteImage);
        }

        $mailForm = $this->formFactory->create(EditMailType::class);
        $passwordForm = $this->formFactory->create(EditPasswordType::class);

        $mailForm->handleRequest($request);
        $passwordForm->handleRequest($request);

        if ($mailForm->isSubmitted() && $mailForm->isValid())
        {
            $updateUserDTO = $mailForm->getData();
            $user->setEmail((string)$updateUserDTO->email);

            $this->manager->flush();
            $request->getSession()->getFlashBag()->add('info', 'Email changer avec succé !');
        }

        if ($passwordForm->isSubmitted() && $passwordForm->isValid())
        {
            $updateUserDTO = $passwordForm->getData();
            if (UserUtils::checkPassword((string)$updateUserDTO->password, (string)$updateUserDTO->retypePassword))
            {
                $user->setPassword($this->passwordEncoder->encodePassword($this->getUser(), (string)$updateUserDTO->password));
                $this->manager->flush();
                $request->getSession()->getFlashBag()->add('info', 'Mot de passe changer avec succé !');
                return new RedirectResponse('/', 302);
            } else {
                $request->getSession()->getFlashBag()->add('error', 'Les mot de passe doivent correspondre !');
            }
        }

        return new Response($this->twig->render('profil/profil.html.twig', [
            'contribution_count' => count($authoredRecipeList),
            'authored_recipe_list' => $authoredRecipeList,
            'authored_recipe_image' => $authoredRecipeImage,
            'favorite_recipe_list' => $userFavoritesRecipe,
            'favorite_recipe_image_list' => $userFavoritesImages,
            'mail_form' => $mailForm->createView(),
            'password_form' => $passwordForm->createView()
        ]));
    }

    /**
     * @return object|string
     */
    private function getUser()
    {
        return $this->tokenStorage->getToken()->getUser();
    }
}
