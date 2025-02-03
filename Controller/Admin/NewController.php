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

namespace BaksDev\Contacts\Region\Controller\Admin;

use BaksDev\Contacts\Region\Entity\ContactsRegion;
use BaksDev\Contacts\Region\UseCase\Admin\NewEdit\ContactsRegionDTO;
use BaksDev\Contacts\Region\UseCase\Admin\NewEdit\ContactsRegionForm;
use BaksDev\Contacts\Region\UseCase\Admin\NewEdit\ContactsRegionHandler;
use BaksDev\Core\Controller\AbstractController;
use BaksDev\Core\Listeners\Event\Security\RoleSecurity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[RoleSecurity('ROLE_CONTACTS_REGION_NEW')]
final class NewController extends AbstractController
{
    #[Route('/admin/call/new', name: 'admin.newedit.new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        ContactsRegionHandler $contactsRegionHandler,
    ): Response
    {

        $ContactsRegionDTO = new ContactsRegionDTO();

        // Форма
        $form = $this
            ->createForm(
                type: ContactsRegionForm::class,
                data: $ContactsRegionDTO,
                options: ['action' => $this->generateUrl('contacts-region:admin.newedit.new')]
            )
            ->handleRequest($request);

        if($form->isSubmitted() && $form->isValid() && $form->has('contacts_region'))
        {
            $this->refreshTokenForm($form);

            $ContactsRegion = $contactsRegionHandler->handle($ContactsRegionDTO);

            if($ContactsRegion instanceof ContactsRegion)
            {
                $this->addFlash('admin.page.new', 'admin.success.new', 'admin.contacts.region');

                return $this->redirectToRoute('contacts-region:admin.index');
            }

            $this->addFlash('admin.page.new', 'admin.danger.new', 'admin.contacts.region', $ContactsRegion);

            return $this->redirectToReferer();
        }

        return $this->render(['form' => $form->createView()]);
    }
}
