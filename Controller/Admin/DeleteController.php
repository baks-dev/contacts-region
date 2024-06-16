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

use BaksDev\Contacts\Region\Entity\Call\ContactsRegionCall;
use BaksDev\Contacts\Region\Entity\ContactsRegion;
use BaksDev\Contacts\Region\UseCase\Admin\Delete\ContactsRegionDeleteDTO;
use BaksDev\Contacts\Region\UseCase\Admin\Delete\ContactsRegionDeleteForm;
use BaksDev\Contacts\Region\UseCase\Admin\Delete\ContactsRegionDeleteHandler;
use BaksDev\Core\Controller\AbstractController;
use BaksDev\Core\Listeners\Event\Security\RoleSecurity;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

#[AsController]
#[RoleSecurity('ROLE_CONTACTS_REGION_CALL_DELETE')]
final class DeleteController extends AbstractController
{
    #[Route('/admin/call/delete/{id}', name: 'admin.delete', methods: ['GET', 'POST'])]
    public function delete(
        Request $request,
        #[MapEntity] ContactsRegionCall $ContactsRegionCallEvent,
        ContactsRegionDeleteHandler $ContactsRegionCallDeleteHandler,
    ): Response {

        $ContactsRegionCallDeleteDTO = new ContactsRegionDeleteDTO();
        $ContactsRegionCallEvent->getDto($ContactsRegionCallDeleteDTO);

        $form = $this->createForm(ContactsRegionDeleteForm::class, $ContactsRegionCallDeleteDTO, [
            'action' => $this->generateUrl('contacts-region:admin.delete', ['id' => $ContactsRegionCallDeleteDTO->getId()]),
        ]);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid() && $form->has('contacts_call_delete'))
        {
            $this->refreshTokenForm($form);

            $ContactsRegionCall = $ContactsRegionCallDeleteHandler->handle($ContactsRegionCallDeleteDTO);

            if($ContactsRegionCall instanceof ContactsRegion)
            {
                $this->addFlash('admin.page.delete', 'admin.success.delete', 'admin.contacts.region');

                return $this->redirectToRoute('contacts-region:admin.index');
            }

            $this->addFlash(
                'admin.page.delete',
                'admin.danger.delete',
                'admin.contacts.region',
                $ContactsRegionCall
            );

            return $this->redirectToRoute('contacts-region:admin.index', status: 400);
        }

        return $this->render([
            'form' => $form->createView(),
            'name' => $ContactsRegionCallEvent->getNameByLocale($this->getLocale()), // название согласно локали
        ]);
    }
}
