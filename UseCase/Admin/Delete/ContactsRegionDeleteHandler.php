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

namespace BaksDev\Contacts\Region\UseCase\Admin\Delete;

use BaksDev\Contacts\Region\Entity;
use BaksDev\Contacts\Region\Messenger\ContactRegionMessage;
use BaksDev\Core\Messenger\MessageDispatchInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final readonly class ContactsRegionDeleteHandler
{
    public function __construct(
        #[Target('contactsRegionLogger')] private LoggerInterface $logger,
        private EntityManagerInterface $entityManager,
        private ValidatorInterface $validator,
        private MessageDispatchInterface $messageDispatch
    ) {}


    public function handle(
        ContactsRegionDeleteDTO $command,
    ): string|Entity\ContactsRegion
    {

        /* Валидация DTO */
        $errors = $this->validator->validate($command);

        if(count($errors) > 0)
        {
            /** Ошибка валидации */
            $uniqid = uniqid('', false);
            $this->logger->error(sprintf('%s: %s', $uniqid, $errors), [self::class.':'.__LINE__]);

            return $uniqid;
        }


        /* Обязательно передается идентификатор события */
        if($command->getId() === null)
        {
            $uniqid = uniqid('', false);
            $errorsString = sprintf(
                'Not found event id in class: %s',
                $command::class
            );
            $this->logger->error($uniqid.': '.$errorsString);

            return $uniqid;
        }


        /** Получаем колл-центр */
        $Call = $this->entityManager->getRepository(Entity\Call\ContactsRegionCall::class)->find($command->getId());


        if($Call === null)
        {
            $uniqid = uniqid('', false);
            $errorsString = sprintf(
                'Not found %s by id: %s',
                Entity\Call\ContactsRegionCall::class,
                $command->getId()
            );
            $this->logger->error($uniqid.': '.$errorsString);

            return $uniqid;
        }


        /** Получаем корень агрегата */
        $Main = $this->entityManager->getRepository(Entity\ContactsRegion::class)
            ->findOneBy(['event' => $Call->getEvent()]);

        if(empty($Main))
        {
            $uniqid = uniqid('', false);
            $errorsString = sprintf(
                'Not found %s by event: %s',
                Entity\ContactsRegion::class,
                $Call->getEvent()
            );
            $this->logger->error($uniqid.': '.$errorsString);

            return $uniqid;
        }

        /* Удаляем колл-центр */
        $this->entityManager->remove($Call);

        $this->entityManager->flush();

        /* Отправляем событие в шину  */
        $this->messageDispatch->dispatch(
            message: new ContactRegionMessage($Main->getId(), $Main->getEvent()),
            transport: 'contacts-region'
        );


        return $Main;

    }

}