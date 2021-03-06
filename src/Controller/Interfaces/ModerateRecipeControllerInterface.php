<?php
/**
 * Created with PHPStorm
 * Date: 31/7/2019
 * Time: 11:2
 * Author: S. Carpentier
 * Mail: sebastien.carpentier89@gmail.com
 */

namespace App\Controller\Interfaces;


use App\DTO\Interfaces\ModerationDTOInterface;
use App\DTO\ModerationDTO;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

interface ModerateRecipeControllerInterface
{
    public function __construct(ObjectManager $manager,
                                Environment $twig,
                                FormFactoryInterface $formFactory,
                                ModerationDTOInterface $moderationDTO);

    public function __invoke(Request $request):Response;
}