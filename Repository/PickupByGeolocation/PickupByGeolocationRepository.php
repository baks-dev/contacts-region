<?php
/*
 *  Copyright 2024.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Contacts\Region\Repository\PickupByGeolocation;

use BaksDev\Contacts\Region\Entity\Call\ContactsRegionCall;
use BaksDev\Contacts\Region\Entity\Call\Info\ContactsRegionCallInfo;
use BaksDev\Contacts\Region\Entity\ContactsRegion;
use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Core\Type\Gps\GpsLatitude;
use BaksDev\Core\Type\Gps\GpsLongitude;
use InvalidArgumentException;


final class PickupByGeolocationRepository implements PickupByGeolocationInterface
{
    private GpsLatitude|false $latitude;

    private GpsLongitude|false $longitude;

    public function __construct(private readonly DBALQueryBuilder $DBALQueryBuilder) {}


    public function latitude(string|float|GpsLatitude $latitude): self
    {
        if(false === ($latitude instanceof GpsLatitude))
        {
            $latitude = new GpsLatitude($latitude);
        }

        $position = strpos($latitude->getValue(), '.');
        $latitude = substr($latitude->getValue(), 0, $position + 4); // +1 для точки и +3 для трех знаков

        $this->latitude = new GpsLatitude($latitude);

        return $this;
    }

    public function longitude(string|float|GpsLongitude $longitude): self
    {
        if(false === ($longitude instanceof GpsLongitude))
        {
            $longitude = new GpsLongitude($longitude);
        }

        $position = strpos($longitude->getValue(), '.');
        $longitude = substr($longitude->getValue(), 0, $position + 4); // +1 для точки и +3 для трех знаков

        $this->longitude = new GpsLongitude($longitude);

        return $this;
    }

    public function execute(): PickupByGeolocationDTO|false
    {
        $dbal = $this->DBALQueryBuilder->createQueryBuilder(self::class);

        if($this->latitude === false)
        {
            throw new InvalidArgumentException('Invalid Argument $latitude');
        }

        if($this->longitude === false)
        {
            throw new InvalidArgumentException('Invalid Argument $longitude');
        }

        $dbal
            ->from(ContactsRegionCallInfo::class, 'info');

        $dbal->where('info.latitude LIKE :latitude')
            ->setParameter('latitude', $this->latitude.'%');

        $dbal
            ->andWhere('info.longitude LIKE :longitude')
            ->setParameter('longitude', $this->longitude.'%');

        $dbal
            ->join(
                'info',
                ContactsRegionCall::class,
                'call',
                'call.id = info.call'
            );

        $dbal
            ->join(
                'call',
                ContactsRegion::class,
                'region',
                'region.event = call.event'
            );

        $dbal->select('call.id AS id');
        $dbal->addSelect('info.latitude AS latitude');
        $dbal->addSelect('info.longitude AS longitude');

        return $dbal
            ->enableCache('contacts-region', 3600)
            ->fetchHydrate(PickupByGeolocationDTO::class);
    }
}