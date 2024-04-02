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

namespace BaksDev\Contacts\Region\Repository\ContactCallRegionChoice;

use BaksDev\Core\Type\Locale\Locale;
use Doctrine\ORM\EntityManagerInterface;
use BaksDev\Reference\Region\Type\Id\RegionUid;
use BaksDev\Contacts\Region\Entity as ContactsEntity;
use Symfony\Contracts\Translation\TranslatorInterface;
use BaksDev\Contacts\Region\Type\Call\ContactsRegionCallUid;

final class ContactCallRegionChoiceRepository implements ContactCallRegionChoiceInterface
{
    private EntityManagerInterface $entityManager;

    private TranslatorInterface $translator;

    public function __construct(EntityManagerInterface $entityManager, TranslatorInterface $translator)
    {
        $this->entityManager = $entityManager;
        $this->translator = $translator;
    }

    public function fetchCallRegion(RegionUid $region): ?array
    {
        $qb = $this->entityManager->createQueryBuilder();

        $select = sprintf('new %s(call.id, trans.name, info.latitude, info.longitude)', ContactsRegionCallUid::class);

        $qb->select($select);

        $qb->from(ContactsEntity\ContactsRegion::class, 'region');

        $qb->join(
            ContactsEntity\Event\ContactsRegionEvent::class,
            'event',
            'WITH',
            'event.id = region.event'
        );

        $qb->leftJoin(
            ContactsEntity\Call\ContactsRegionCall::class,
            'call',
            'WITH',
            'call.event = event.id and call.pickup = true'
        );

        $qb->join(
            ContactsEntity\Call\Info\ContactsRegionCallInfo::class,
            'info',
            'WITH',
            'info.call = call.id'
        );

        $qb->leftJoin(
            ContactsEntity\Call\Trans\ContactsRegionCallTrans::class,
            'trans',
            'WITH',
            'trans.call = call.id AND trans.local = :local'
        );

        $qb->where('region.id = :region');
        $qb->setParameter('region', $region, RegionUid::TYPE);

        $qb->setParameter('local', new Locale($this->translator->getLocale()), Locale::TYPE);

        $qb->orderBy('call.sort');

        return $qb->getQuery()->getResult();
    }
}
