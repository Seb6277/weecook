<?php
/**
 * Created with PHPStorm
 * Date: 31/7/2019
 * Time: 11:2
 * Author: S. Carpentier
 * Mail: sebastien.carpentier89@gmail.com
 */

namespace App\Controller\Interfaces;

use App\Service\FileUploader;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Twig\Environment;

interface RecipeControllerInterface
{
    public function __construct(ObjectManager $manager,
                                TokenStorageInterface $tokenStorage,
                                Environment $twig,
                                FormFactoryInterface $formFactory);

    public function __invoke(Request $request, FileUploader $fileUploader):Response;
}