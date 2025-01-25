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
use BaksDev\Contacts\Region\Type\Call\ContactsRegionCallUid;
use BaksDev\Reference\Region\Type\Id\RegionUid;
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
    ) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer(new ContactRegionFieldTransformer($this->callRegion));

        /** Получаем список регионов */
        $regionChoice = $this->regionChoice->getRegionChoice();

        $builder
            ->add('region', ChoiceType::class, [
                'choices' => $regionChoice,
                'choice_value' => function(?RegionUid $region) {
                    return $region?->getValue();
                },
                'choice_label' => function(RegionUid $region) {
                    return $region->getOption();
                },
                'label' => false,
                'expanded' => false,
                'multiple' => false,
                'required' => true,
                'placeholder' => 'Выберите регион из списка...',
                'attr' => ['class' => 'change_region_field', 'data-select' => 'select2'],
            ]);


        $builder
            ->add('call', ChoiceType::class, [

                'label' => false,
                'expanded' => false,
                'multiple' => false,
                'required' => true,
                'placeholder' => 'Выберите регион из списка...',
                'attr' => ['data-address' => 'true', 'data-select' => 'select2'],
            ]);


        $builder->addEventListener(
            FormEvents::POST_SET_DATA,
            function(FormEvent $event): void {

                $form = $event->getForm();

                $data = $form->getNormData();

                $callChoice = $this->callChoice->fetchCallRegion($this->region);

                if($data->getCall() && $data->getRegion())
                {
                    $callChoice = $this->callChoice->fetchCallRegion($data->getRegion());
                }

                if($callChoice)
                {
                    $form
                        ->add('call', ChoiceType::class, [
                            'choices' => $callChoice,
                            'choice_value' => function(?ContactsRegionCallUid $call) {
                                return $call?->getValue();
                            },
                            'choice_label' => function(ContactsRegionCallUid $call) {
                                return $call->getName();
                            },

                            'choice_attr' => function(ContactsRegionCallUid $choice) {
                                return ['data-lati' => $choice->getAttr(), 'data-longi' => $choice->getOption()];
                            },

                            'attr' => ['data-address' => 'true', 'data-select' => 'select2'],
                            'label' => false,
                            'expanded' => false,
                            'multiple' => false,
                            'required' => true,
                            'placeholder' => 'Выберите пункт выдачи товаров'
                        ]);
                }

                if(!$data->getCall() && $data->getRegion())
                {
                    $form
                        ->add('call', ChoiceType::class, [

                            'label' => false,
                            'expanded' => false,
                            'multiple' => false,
                            'required' => true,
                            'placeholder' => 'Нет пунктов выдачи в указанном регионе',
                        ]);
                }

            }
        );


        $builder->get('region')->addEventListener(
            FormEvents::POST_SUBMIT,
            function(FormEvent $event): void {

                $region = $event->getForm()->getData();

                if($region)
                {
                    $form = $event->getForm()->getParent();

                    if(is_null($form))
                    {
                        return;
                    }

                    $this->region = $region;

                    $callChoice = $this->callChoice->fetchCallRegion($this->region);

                    if($callChoice)
                    {
                        $form
                            ->add('call', ChoiceType::class, [
                                'choices' => $callChoice,
                                'choice_value' => function(?ContactsRegionCallUid $call) {
                                    return $call?->getValue();
                                },
                                'choice_label' => function(ContactsRegionCallUid $call) {
                                    return $call->getName();
                                },

                                'choice_attr' => function(ContactsRegionCallUid $choice) {
                                    return ['data-lati' => $choice->getAttr(), 'data-longi' => $choice->getOption()];
                                },

                                'attr' => ['data-address' => 'true', 'data-select' => 'select2'],
                                'label' => false,
                                'expanded' => false,
                                'multiple' => false,
                                'required' => true,
                                'placeholder' => 'Выберите пункт выдачи товаров'
                            ]);
                    }
                    else
                    {
                        $form
                            ->add('call', ChoiceType::class, [

                                'label' => false,
                                'expanded' => false,
                                'multiple' => false,
                                'required' => true,
                                'placeholder' => 'Нет пунктов выдачи в указанном регионе',
                            ]);
                    }
                }
            }
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ContactRegionFieldDTO::class,
            'validation_groups' => false
        ]);
    }
}
