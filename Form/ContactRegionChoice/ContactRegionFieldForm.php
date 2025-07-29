<?php
/*
 *  Copyright 2023.  Baks.dev <admin@baks.dev>
 *
 *  Permission is hereby granted, free of charge, to any person obtaining a copy
 *  of this software and associated documentation files (the "Software"), to deal
 *  in the Software without restriction, including without limitation the rights
 *  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the Software is furnished
 *  to do so, subject to the following conditions:
 *
 *  The above copyright notice and this permission notice shall be included in all
 *  copies or substantial portions of the Software.
 *
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NON INFRINGEMENT. IN NO EVENT SHALL THE
 *  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 *  THE SOFTWARE.
 */

declare(strict_types=1);

namespace BaksDev\Contacts\Region\Form\ContactRegionChoice;

use BaksDev\Contacts\Region\Repository\ContactCallRegion\ContactCallRegionInterface;
use BaksDev\Contacts\Region\Repository\ContactCallRegionChoice\ContactCallRegionChoiceInterface;
use BaksDev\Contacts\Region\Repository\ContactRegionChoice\ContactRegionChoiceInterface;
use BaksDev\Reference\Region\Type\Id\RegionUid;
use BaksDev\Users\Profile\UserProfile\Repository\UserProfileByRegion\UserProfileByRegionInterface;
use BaksDev\Users\Profile\UserProfile\Repository\UserProfileByRegion\UserProfileByRegionResult;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ContactRegionFieldForm extends AbstractType
{
    private ?RegionUid $region = null;

    public function __construct(
        private readonly ContactRegionChoiceInterface $regionChoice,
        private readonly ContactCallRegionChoiceInterface $callChoice,
        private readonly ContactCallRegionInterface $callRegion,

        private readonly UserProfileByRegionInterface $UserProfileByRegionRepository,

    ) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer(new ContactRegionFieldTransformer());

        $builder->addEventListener(
            FormEvents::POST_SET_DATA,
            function(FormEvent $event): void {

                $form = $event->getForm();
                /** @var ContactRegionFieldDTO $data */
                $data = $form->getNormData();

                $profiles = $this->UserProfileByRegionRepository
                    ->onlyCurrentRegion()
                    ->findAll();

                /** Если не указан профиль пользователя - присваиваем по умолчанию первый элемент списка */
                if(false === ($data->getProfile() instanceof UserProfileUid))
                {
                    $current = $profiles->current()?->getId();
                    $data->setProfile($current);
                }

                $form
                    ->add('profile', ChoiceType::class, [
                        'choices' => $profiles,
                        'choice_value' => function(UserProfileByRegionResult|UserProfileUid|null $profile) {

                            if($profile instanceof UserProfileUid)
                            {
                                return $profile->getValue();
                            }

                            return $profile?->getId();
                        },
                        'choice_label' => function(UserProfileByRegionResult $profile) {
                            return $profile->getLocation();
                        },

                        'choice_attr' => function(UserProfileByRegionResult $profile) {
                            return [
                                'data-lati' => $profile->getLatitude(),
                                'data-longi' => $profile->getLongitude(),
                                'data-address' => 'true',
                            ];
                        },

                        'label' => false,
                        'expanded' => true,
                        'multiple' => false,
                        'required' => true,
                        'placeholder' => false,
                    ]);
            },
        );


    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ContactRegionFieldDTO::class,
            'validation_groups' => false,
        ]);
    }
}
