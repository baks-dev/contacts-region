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

use BaksDev\Contacts\Region\Entity as ContactsRegionEntity;
use BaksDev\Contacts\Region\Type\Call\Const\ContactsRegionCallConst;
use BaksDev\Core\Doctrine\ORMQueryBuilder;
use BaksDev\Core\Type\Locale\Locale;
use BaksDev\Users\Address\Entity\GeocodeAddress;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use Symfony\Contracts\Translation\TranslatorInterface;

final class WarehouseChoiceRepository implements WarehouseChoiceInterface
{

    private TranslatorInterface $translator;
    private ORMQueryBuilder $ORMQueryBuilder;

    public function __construct(ORMQueryBuilder $ORMQueryBuilder, TranslatorInterface $translator)
    {

        $this->translator = $translator;
        $this->ORMQueryBuilder = $ORMQueryBuilder;
    }

    /**
     * Возвращает список всех складов c постоянными неизменяемыми идентификаторами
     */
    public function fetchAllWarehouse(): ?array
    {
        $qb = $this->ORMQueryBuilder->createQueryBuilder(self::class);

        $select = sprintf('new %s(warehouse.const, trans.name, CONCAT(geocode.latitude, \',\', geocode.longitude))', ContactsRegionCallConst::class);

        $qb->select($select);

        $qb->from(ContactsRegionEntity\Call\ContactsRegionCall::class, 'warehouse');

        $qb->join(
            ContactsRegionEntity\Event\ContactsRegionEvent::class,
            'event',
            'WITH',
            'event.id = warehouse.event',
        );

        $qb->join(
            ContactsRegionEntity\ContactsRegion::class,
            'contacts',
            'WITH',
            'contacts.event = event.id',
        );

        $qb->leftJoin(
            ContactsRegionEntity\Call\Info\ContactsRegionCallInfo::class,
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
            ContactsRegionEntity\Call\Trans\ContactsRegionCallTrans::class,
            'trans',
            'WITH',
            'trans.call = warehouse.id AND trans.local = :local',
        );
        $qb->setParameter('local', new Locale($this->translator->getLocale()), Locale::TYPE);

        $qb->where('warehouse.stock = true');

        /* Кешируем результат ORM */
        return $qb->enableCache('contacts-region', 86400)->getResult();

    }

    /** Возвращает список складов ответственного лица */
    public function fetchWarehouseByProfile(UserProfileUid $profile): ?array
    {
        $qb = $this->ORMQueryBuilder->createQueryBuilder(self::class);

        $select = sprintf('new %s(warehouse.const, trans.name)', ContactsRegionCallConst::class);

        $qb->select($select);

        $qb->from(ContactsRegionEntity\Call\ContactsRegionCall::class, 'warehouse');

        $qb->join(
            ContactsRegionEntity\Event\ContactsRegionEvent::class,
            'event',
            'WITH',
            'event.id = warehouse.event',
        );

        $qb->join(
            ContactsRegionEntity\ContactsRegion::class,
            'contacts',
            'WITH',
            'contacts.event = event.id',
        );

        $qb->leftJoin(
            ContactsRegionEntity\Call\Trans\ContactsRegionCallTrans::class,
            'trans',
            'WITH',
            'trans.call = warehouse.id AND trans.local = :local',
        );

        $qb->where('warehouse.stock = true');
        $qb->andWhere('warehouse.profile = :profile');

        $qb->setParameter('local', new Locale($this->translator->getLocale()), Locale::TYPE);
        $qb->setParameter('profile', $profile, UserProfileUid::TYPE);

        /* Кешируем результат ORM */
        return $qb->enableCache('contacts-region', 86400)->getResult();
    }
}
