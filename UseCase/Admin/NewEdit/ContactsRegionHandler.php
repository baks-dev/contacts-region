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

namespace BaksDev\Contacts\Region\UseCase\Admin\NewEdit;


use BaksDev\Contacts\Region\Entity\Call\ContactsRegionCall;
use BaksDev\Contacts\Region\Entity\ContactsRegion;
use BaksDev\Contacts\Region\Entity\Event\ContactsRegionEvent;
use BaksDev\Contacts\Region\Messenger\ContactRegionMessage;
use BaksDev\Core\Entity\AbstractHandler;

final class ContactsRegionHandler extends AbstractHandler
{
    public function handle(ContactsRegionDTO $command): string|ContactsRegion
    {
        $Main = $this
            ->getRepository(ContactsRegion::class)
            ->find($command->getRegion());

        $ContactsRegionEvent = new ContactsRegionEvent();


        /** Получаем событие */
        if($Main instanceof ContactsRegion)
        {
            $ContactsRegionEvent =
                $this
                    ->getRepository(ContactsRegionEvent::class)
                    ->find($Main->getEvent());

            if($ContactsRegionEvent)
            {
                $ContactsRegionEvent->getDto($command);
            }

            $this->clear();
        }

        $command->setId($Main?->getEvent());


        $this
            ->setCommand($command)
            ->preEventPersistOrUpdate(
                $Main ?: new ContactsRegion($command->getRegion()),
                $ContactsRegionEvent
            );

        $ContactsRegionCallDTO = $command->getCalls();

        $filter = $this->event->getCall()->filter(
            function(ContactsRegionCall $element) use ($ContactsRegionCallDTO) {
                return $element->getConst()->equals($ContactsRegionCallDTO->getConst());
            }
        );


        /* Добавляем новый */
        if($filter->isEmpty())
        {
            $ContactsRegionCall = new ContactsRegionCall($this->event);
            $ContactsRegionCall->setEntity($ContactsRegionCallDTO);
            $this->persist($ContactsRegionCall);
        }

        // Обновляем существующий
//        else
//        {
//
//
//            //$this->entityManager->clear();
//
//            //$ContactsRegionCall = $this->entityManager->getRepository(ContactsRegionCall::class)->findOneBy(['const' => $filter->current()->getConst()]);
//
//            //dd($ContactsRegionCall);
//
//            //$ContactsRegionCall->setEntity($ContactsRegionCallDTO);
//            //$this->entityManager->flush();
//
//            //dd($ContactsRegionCall->setEntity($ContactsRegionCallDTO));
//
//            /** @var ContactsRegionCall $ContactsRegionCall */
////            $ContactsRegionCall = $filter->current();
////            $ContactsRegionCall->setEntity($ContactsRegionCallDTO);
//
//            //dd($this->event);
//
//
//        }

        /** Валидация всех объектов */
        if($this->validatorCollection->isInvalid())
        {
            return $this->validatorCollection->getErrorUniqid();
        }


        $this->flush();

        /* Отправляем событие в шину  */
        $this->messageDispatch->dispatch(
            message: new ContactRegionMessage($this->main->getId(), $this->main->getEvent()),
            transport: 'contacts-region'
        );


        return $this->main;
    }
}
