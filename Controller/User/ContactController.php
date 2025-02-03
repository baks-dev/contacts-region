<?php
/*
 *  Copyright 2022.  Baks.dev <admin@baks.dev>
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *   limitations under the License.
 *
 */

namespace BaksDev\Contacts\Region\Controller\User;

use BaksDev\Contacts\Region\Form\RegionFilter\RegionFilterDTO;
use BaksDev\Contacts\Region\Form\RegionFilter\RegionFilterForm;
use BaksDev\Contacts\Region\Repository\ContactCallByRegion\ContactCallByRegionInterface;
use BaksDev\Contacts\Region\Repository\ContactCallDetail\ContactCallDetailInterface;
use BaksDev\Contacts\Region\Repository\ContactRegionDefault\ContactRegionDefaultInterface;
use BaksDev\Core\Controller\AbstractController;
use BaksDev\Core\Form\Search\SearchDTO;
use BaksDev\Core\Form\Search\SearchForm;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
final class ContactController extends AbstractController
{
    /** Опт  */
    #[Route('/contact', name: 'user.contact')]
    public function index(
        Request $request,
        ContactCallDetailInterface $callDetail,
        ContactRegionDefaultInterface $defaultRegion,
        ContactCallByRegionInterface $callRegion,
    ): Response
    {

        $RegionFilterDTO = new RegionFilterDTO();
        $DefaultRegion = $defaultRegion->getDefaultCallRegion();
        $RegionFilterDTO->setRegion($DefaultRegion);

        // Форма
        $form = $this
            ->createForm(
                type: RegionFilterForm::class,
                data: $RegionFilterDTO,
                options: ['action' => 'contacts-region:user.contact']

            )
            ->handleRequest($request);

        $calls = $callRegion
            ->fetchContactCallByRegionAssociative($RegionFilterDTO->getRegion());

        // Поиск по всему сайту
        $allSearch = new SearchDTO($request);
        $allSearchForm = $this->createForm(
            type: SearchForm::class,
            data: $allSearch,
            options: ['action' => $this->generateUrl('contacts-region:admin.index')]
        );


        return $this->render([
            'regions' => $callDetail->fetchContactCallAllAssociative(),
            'calls' => $calls,
            'form' => $form->createView(),
            'all_search' => $allSearchForm->createView(),
        ]);
    }
}
