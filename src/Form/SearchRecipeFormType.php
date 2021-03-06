<?php
/**
 * Created with PHPStorm
 * Date: 6/8/2019
 * Time: 0:22
 * Author: S. Carpentier
 * Mail: sebastien.carpentier89@gmail.com
 */

namespace App\Form;


use App\DTO\SearchDTO;
use App\Entity\RecipeCategory;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class SearchRecipeFormType
 * @package App\Form
 */
class SearchRecipeFormType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('searchString', TextType::class, [
                'label' => 'Recherche :',
                'required' => false
            ])
            ->add('category', EntityType::class, [
                'class' => RecipeCategory::class,
                'choice_label' => 'category',
                'placeholder' => 'Selectionner',
                'required' => false,
                'label' => 'Catégorie (facultatif) :'
            ]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SearchDTO::class
        ]);
    }
}