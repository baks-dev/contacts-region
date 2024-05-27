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

namespace BaksDev\Contacts\Region\Repository\ContactRegionDefault;

use BaksDev\Contacts\Region\Entity as ContactsRegionEntity;
use BaksDev\Core\Doctrine\ORMQueryBuilder;
use BaksDev\Reference\Region\Type\Id\RegionUid;

final class ContactRegionDefault implements ContactRegionDefaultInterface
{

    private ORMQueryBuilder $ORMQueryBuilder;

    public function __construct(ORMQueryBuilder $ORMQueryBuilder)
    {
        $this->ORMQueryBuilder = $ORMQueryBuilder;
    }


    public function getDefaultCallRegion(): ?RegionUid
    {
        $qb = $this->ORMQueryBuilder->createQueryBuilder(self::class);

        $select = sprintf('new %s(contact.id)', RegionUid::class);
        $qb->select($select);

        $qb->from(ContactsRegionEntity\ContactsRegion::class, 'contact');

        $qb->join(ContactsRegionEntity\Event\ContactsRegionEvent::class,
            'contact_event',
            'WITH',
            'contact_event.id = contact.event'
        );

        $qb->join(ContactsRegionEntity\Call\ContactsRegionCall::class,
            'contact_call',
            'WITH',
            'contact_call.event = contact_event.id AND contact_call.active = true'
        );

        $qb->orderBy('contact_event.sort');

        $qb->setMaxResults(1);

        /* Кешируем результат ORM */
        return $qb->enableCache('contacts-region', 86400)->getOneOrNullResult();

    }

}