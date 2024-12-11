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

namespace BaksDev\Contacts\Region\Repository\ContactCallByGeocode;

use BaksDev\Contacts\Region\Entity\Call\ContactsRegionCall;
use BaksDev\Contacts\Region\Entity\Call\Info\ContactsRegionCallInfo;
use BaksDev\Contacts\Region\Entity\ContactsRegion;
use BaksDev\Contacts\Region\Entity\Event\ContactsRegionEvent;
use BaksDev\Contacts\Region\Type\Call\Const\ContactsRegionCallConst;
use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Core\Type\Gps\GpsLatitude;
use BaksDev\Core\Type\Gps\GpsLongitude;

final readonly class ContactCallByGeocodeRepository implements ContactCallByGeocodeInterface
{
    public function __construct(private DBALQueryBuilder $DBALQueryBuilder) {}

    /**
     *
     * Метод возвращает геолокацию регионального контакта по его неизменяемому идентификатору (CONST)
     */
    public function fetchContactCallGeocodeByConst(ContactsRegionCallConst $const): ?array
    {
        $qb = $this->DBALQueryBuilder->createQueryBuilder(self::class);

        $qb
            ->from(ContactsRegionCall::class, 'call')
            ->where('call.const = :const')
            ->setParameter(
                'const',
                $const,
                ContactsRegionCallConst::TYPE
            );

        $qb->join('call',
            ContactsRegion::class,
            'region',
            'region.event = call.event'
        );


        $qb
            ->addSelect('call_info.latitude AS call_latitude')
            ->addSelect('call_info.longitude AS call_longitude')
            ->join('call',
                ContactsRegionCallInfo::class,
                'call_info',
                'call_info.call = call.id'
            );


        /* Кешируем результат DBAL */
        return $qb
            ->enableCache('contacts-region', 86400)
            ->fetchAssociative();

    }


    /**
     * Метод проверяет по геолокации, что имеется такой пункт самовывоза
     */
    public function existContactCallByGeocode(GpsLatitude $latitude, GpsLongitude $longitude): bool
    {
        $qbExist = $this->DBALQueryBuilder->createQueryBuilder(self::class);

        $qbExist
            ->from(ContactsRegionCallInfo::class, 'info');

        $qbExist
            ->where('info.latitude = :latitude')
            ->setParameter('latitude', $latitude, GpsLatitude::TYPE);

        $qbExist
            ->andWhere('info.longitude = :longitude')
            ->setParameter('longitude', $longitude, GpsLongitude::TYPE);

        $qbExist->join(
            'info',
            ContactsRegionCall::class,
            'call',
            'call.id = info.call AND call.pickup = true'
        );

        $qbExist->join(
            'call',
            ContactsRegionEvent::class,
            'event',
            'event.id = call.event'
        );

        $qbExist->join(
            'event',
            ContactsRegion::class,
            'region',
            'region.event = event.id'
        );

        return $qbExist->enableCache('contacts-region', 3600)->fetchExist();

    }
}
