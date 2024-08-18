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

//use BaksDev\Contacts\Region\Entity;
use BaksDev\Contacts\Region\Entity\Call\ContactsRegionCall;
use BaksDev\Contacts\Region\Entity\ContactsRegion;
use BaksDev\Contacts\Region\Entity\Event\ContactsRegionEvent;
use BaksDev\Contacts\Region\Messenger\ContactRegionMessage;
use BaksDev\Core\Entity\AbstractHandler;
use BaksDev\Core\Messenger\MessageDispatchInterface;
use BaksDev\Files\Resources\Upload\Image\ImageUploadInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use DomainException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class ContactsRegionHandler extends AbstractHandler
{
    //    private EntityManagerInterface $entityManager;
    //
    //    private ValidatorInterface $validator;
    //
    //    private LoggerInterface $logger;
    //
    //    private ImageUploadInterface $imageUpload;
    //
    //    private MessageDispatchInterface $messageDispatch;
    //
    //    public function __construct(
    //        EntityManagerInterface $entityManager,
    //        ValidatorInterface $validator,
    //        LoggerInterface $logger,
    //        ImageUploadInterface $imageUpload,
    //        MessageDispatchInterface $messageDispatch,
    //    ) {
    //        $this->entityManager = $entityManager;
    //        $this->validator = $validator;
    //        $this->logger = $logger;
    //        $this->imageUpload = $imageUpload;
    //        $this->messageDispatch = $messageDispatch;
    //    }

    public function handle(ContactsRegionDTO $command): string|ContactsRegion
    {

        $Main = $this->entityManager
            ->getRepository(ContactsRegion::class)
            ->find($command->getRegion());

        /** Получаем событие */
        if($Main)
        {
            $ContactsRegionEvent =
                $this->entityManager
                    ->getRepository(ContactsRegionEvent::class)
                    ->find($Main?->getEvent());

            if($ContactsRegionEvent)
            {
                $ContactsRegionEvent->getDto($command);
            }

            $this->entityManager->clear();
        }



        $command->setId($Main?->getEvent());

        /** Валидация DTO  */
        $this->validatorCollection->add($command);

        $this->main = $Main ?: new ContactsRegion($command->getRegion());
        $this->event = new ContactsRegionEvent();

        try
        {
            $Main ? $this->preUpdate($command, true) : $this->prePersist($command);
        }
        catch(DomainException $errorUniqid)
        {
            return $errorUniqid->getMessage();
        }

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
            $this->entityManager->persist($ContactsRegionCall);
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
////
////            dd($this->entityManager->getUnitOfWork()->getIdentityMap());
//
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


        $this->entityManager->flush();

        /* Отправляем событие в шину  */
        $this->messageDispatch->dispatch(
            message: new ContactRegionMessage($this->main->getId(), $this->main->getEvent()),
            transport: 'contacts-region'
        );


        return $this->main;
    }


    public function _handle(ContactsRegionDTO $command): string|Entity\ContactsRegion
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

        $this->entityManager->clear();

        /** @var Entity\ContactsRegion $Main */
        $Main = $this->entityManager->getRepository(Entity\ContactsRegion::class)->findOneBy(
            ['id' => $command->getRegion()]
        );

        if($Main)
        {
            $EventRepo = $this->entityManager->getRepository(Entity\Event\ContactsRegionEvent::class)->find($Main->getEvent());

            if($EventRepo === null)
            {
                $uniqid = uniqid('', false);
                $errorsString = sprintf(
                    'Not found %s by id: %s',
                    Entity\Event\ContactsRegionEvent::class,
                    $Main->getEvent()
                );
                $this->logger->error($uniqid.': '.$errorsString);

                return $uniqid;
            }

            $EventRepo->setEntity($command);
            $EventRepo->setEntityManager($this->entityManager);
            $Event = $EventRepo->cloneEntity();
            //$this->entityManager->clear();

        }
        else
        {
            $Main = new Entity\ContactsRegion($command->getRegion());
            $this->entityManager->persist($Main);

            $Event = new Entity\Event\ContactsRegionEvent();
            $Event->setMain($Main);
        }

        //$this->entityManager->persist($Event);

        $ContactsRegionCallDTO = $command->getCall();
        $newContactsRegionCall = true;

        /** @var ContactsRegionCall $call */
        foreach($Event->getCall() as $call)
        {
            if($call->getConst()->equals($ContactsRegionCallDTO->getConst()))
            {
                $call->setEntity($ContactsRegionCallDTO);

                if($ContactsRegionCallDTO->getCover()->file !== null)
                {
                    $ContactsRegionCallCover = $ContactsRegionCallDTO->getCover()->getEntityUpload();
                    $this->imageUpload->upload($ContactsRegionCallDTO->getCover()->file, $ContactsRegionCallCover);
                }

                $newContactsRegionCall = false;
            }
        }

        /* Добавляем новый */
        if($newContactsRegionCall)
        {
            $ContactsRegionCall = new ContactsRegionCall($Event);
            $ContactsRegionCall->setEntity($ContactsRegionCallDTO);
            $this->entityManager->persist($ContactsRegionCall);
        }


        /* присваиваем событие корню */
        $Main->setEvent($Event);

        $this->entityManager->flush();

        /* Отправляем событие в шину  */
        $this->messageDispatch->dispatch(
            message: new ContactRegionMessage($Main->getId(), $Main->getEvent()),
            transport: 'contacts-region'
        );

        return $Main;
    }
}
