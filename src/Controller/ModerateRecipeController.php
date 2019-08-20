<?php
/**
 * Created with PHPStorm
 * Date: 31/7/2019
 * Time: 11:2
 * Author: S. Carpentier
 * Mail: sebastien.carpentier89@gmail.com
 */

namespace App\Controller;

use App\Controller\Interfaces\ModerateRecipeControllerInterface;
use App\DTO\ModerationDTO;
use App\Entity\Ingredient;
use App\Entity\IngredientQuantity;
use App\Entity\Recipe;
use App\Form\ModerationType;
use App\Utils\RecipeUtils;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

class ModerateRecipeController implements ModerateRecipeControllerInterface
{
    /**
     * @var ObjectManager
     */
    private $manager;

    /**
     * ModerateRecipeController constructor.
     * @param $manager
     */
    public function __construct(ObjectManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @Route("/moderate", name="moderation_page", methods={"GET", "POST"})
     *
     * @param Environment $twig
     * @return Response
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function __invoke(
        Request $request,
        Environment $twig,
        FormFactoryInterface $formFactory,
        ModerationDTO $moderationDTO):Response
    {
        $ingredients = [];
        $quantities = [];
        $measures = [];

        $recipeRepository = $this->manager
            ->getRepository(Recipe::class);
        $ingredientQuantityRepository = $this->manager
            ->getRepository(IngredientQuantity::class);
        $ingredientRepository = $this->manager
            ->getRepository(Ingredient::class);

        $recipe_array = $recipeRepository->getOneNonValidate();

        if (count($recipe_array) == 1)
        {
            $recipe = $recipe_array[0];
        } else {
            return new Response($twig->render('recipe/no_recipe_moderate.html.twig'));
        }


        $ingredientQuantity = $ingredientQuantityRepository->getAllItemsByRecipe($recipe->getId());
        foreach ($ingredientQuantity as $item)
        {
            $ingredient = $item
                ->getIngredient()
                ->getId();

            $quantity = $item->getQuantity();

            array_push($ingredients, $ingredientRepository->find($ingredient)->getName());
            array_push($measures, $ingredientRepository->find($ingredient)->getMesureUnit());
            array_push($quantities, $quantity);
        }

        /**
         * Handle the validation section
         */

        $form = $formFactory->create(ModerationType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $validation = $request->request->get('moderation');

            if ($validation['validate'] === '0')
            {
                $this->manager->remove($recipe);
                $this->manager->flush();
            }
            elseif ($validation['validate'] === '1')
            {
                $recipe->setValidation(true);
            }

            $this->manager->flush();
        }

        return new Response($twig->render('recipe/moderate.html.twig', [
            'preparation' => $recipe->getPreparation(),
            'recipe_name' => $recipe->getName(),
            'ingredients' => $ingredients,
            'quantities' => $quantities,
            'measures' => $measures,
            'ingredient_length' => count($ingredients),
            'image1' => RecipeUtils::getImageUri($recipe),
            'form' => $form->createView()
        ]));
    }
}