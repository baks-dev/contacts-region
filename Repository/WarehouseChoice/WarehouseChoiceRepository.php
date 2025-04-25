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

namespace BaksDev\Contacts\Region\Repository\WarehouseChoice;

use BaksDev\Contacts\Region\Entity\Call\ContactsRegionCall;
use BaksDev\Contacts\Region\Entity\Call\Info\ContactsRegionCallInfo;
use BaksDev\Contacts\Region\Entity\Call\Trans\ContactsRegionCallTrans;
use BaksDev\Contacts\Region\Entity\ContactsRegion;
use BaksDev\Contacts\Region\Entity\Event\ContactsRegionEvent;
use BaksDev\Contacts\Region\Type\Call\Const\ContactsRegionCallConst;
use BaksDev\Core\Doctrine\ORMQueryBuilder;
use BaksDev\Users\Address\Entity\GeocodeAddress;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;

final class WarehouseChoiceRepository implements WarehouseChoiceInterface
{

    public function __construct(private readonly ORMQueryBuilder $ORMQueryBuilder) {}

    /**
     * Возвращает список всех складов c постоянными неизменяемыми идентификаторами
     */
    public function fetchAllWarehouse(): ?array
    {
        $qb = $this->ORMQueryBuilder
            ->createQueryBuilder(self::class)
            ->bindLocal();

        $select = sprintf('new %s(warehouse.const, trans.name, CONCAT(geocode.latitude, \',\', geocode.longitude))', ContactsRegionCallConst::class);

        $qb->select($select);

        $qb->from(ContactsRegionCall::class, 'warehouse');

        $qb->join(
            ContactsRegionEvent::class,
            'event',
            'WITH',
            'event.id = warehouse.event',
        );

        $qb->join(
            ContactsRegion::class,
            'contacts',
            'WITH',
            'contacts.event = event.id',
        );

        $qb->leftJoin(
            ContactsRegionCallInfo::class,
            'info',
            'WITH',
            'info.call = warehouse.id',
        );

        $qb->leftJoin(
            GeocodeAddress::class,
            'geocode',
            'WITH',
            'geocode.id = info.geocode',
        );


        $qb->leftJoin(
            ContactsRegionCallTrans::class,
            'trans',
            'WITH',
            'trans.call = warehouse.id AND trans.local = :local',
        );

        $qb->where('warehouse.stock = true');

        /* Кешируем результат ORM */
        return $qb->enableCache('contacts-region', '1 day')->getResult();

    }

    /** Возвращает список складов ответственного лица */
    public function fetchWarehouseByProfile(UserProfileUid $profile): ?array
    {
        $qb = $this->ORMQueryBuilder
            ->createQueryBuilder(self::class)
            ->bindLocal();

        $select = sprintf('new %s(warehouse.const, trans.name)', ContactsRegionCallConst::class);

        $qb->select($select);

        $qb->from(ContactsRegionCall::class, 'warehouse');

        $qb->join(
            ContactsRegionEvent::class,
            'event',
            'WITH',
            'event.id = warehouse.event',
        );

        $qb->join(
            ContactsRegion::class,
            'contacts',
            'WITH',
            'contacts.event = event.id',
        );

        $qb->leftJoin(
            ContactsRegionCallTrans::class,
            'trans',
            'WITH',
            'trans.call = warehouse.id AND trans.local = :local',
        );

        $qb->where('warehouse.stock = true');

        $qb
            ->andWhere('warehouse.profile = :profile')
            ->setParameter(
                key: 'profile',
                value: $profile,
                type: UserProfileUid::TYPE
            );

        /* Кешируем результат ORM */
        return $qb->enableCache('contacts-region', '1 day')->getResult();
    }
}
