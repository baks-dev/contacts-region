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

namespace BaksDev\Contacts\Region\UseCase\Admin\NewEdit\Call\Info;

use BaksDev\Contacts\Region\Type\Call\Email\ContactRegionEmail;
use BaksDev\Contacts\Region\Type\Call\Gps\ContactRegionGps;
use BaksDev\Users\Address\Type\Geocode\GeocodeAddressUid;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ContactsRegionCallInfoForm extends AbstractType
{
	
	public function buildForm(FormBuilderInterface $builder, array $options) : void
	{
		/** Адрес колл-центра */
		
		$builder->add('address', TextType::class, ['required' => false, 'attr' => ['data-address' => true]]);


        /** Координаты на карте */

        $builder->add('geocode', HiddenType::class, ['attr' => ['data-geocode' => 'true']]);

        $builder->get('geocode')->addModelTransformer(
            new CallbackTransformer(
                function($geocode) {
                    return $geocode instanceof GeocodeAddressUid ? $geocode->getValue() : $geocode;
                },
                function($geocode) {
                    return $geocode ? new GeocodeAddressUid($geocode) : null;
                }
            )
        );
		
		/** Контактный Email */
		
		$builder->add('email', EmailType::class, ['required' => false]);
		
		$builder->get('email')->addModelTransformer(
			new CallbackTransformer(
				function($email) {
					return $email instanceof ContactRegionEmail ? $email->getValue() : $email;
				},
				function($email) {
					return new ContactRegionEmail($email);
				}
			)
		);
		
		/** Режим работы: */
		
		$builder->add('working', TextType::class, ['required' => false]);
		
		/** GPS широта:*/

		$builder->add('latitude', TextType::class, ['required' => false, 'attr' => ['data-latitude' => 'true']]);

		$builder->get('latitude')->addModelTransformer(
			new CallbackTransformer(
				function($gps) {
					return $gps instanceof ContactRegionGps ? $gps->getValue() : $gps;
				},
				function($gps) {
					return new ContactRegionGps($gps);
				}
			)
		);

		/** GPS долгота:*/

		$builder->add('longitude', TextType::class, ['required' => false, 'attr' => ['data-longitude' => 'true']]);

		$builder->get('longitude')->addModelTransformer(
			new CallbackTransformer(
				function($gps) {
					return $gps instanceof ContactRegionGps ? $gps->getValue() : $gps;
				},
				function($gps) {
					return new ContactRegionGps($gps);
				}
			)
		);
	}
	
	
	public function configureOptions(OptionsResolver $resolver) : void
	{
		$resolver->setDefaults([
			'data_class' => ContactsRegionCallInfoDTO::class,
		]);
	}
	
}