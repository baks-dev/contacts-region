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

namespace BaksDev\Contacts\Region\UseCase\Admin\NewEdit\Call\Info;

use BaksDev\Contacts\Region\Entity\Call\Info\ContactsRegionCallInfoInterface;
use BaksDev\Contacts\Region\Type\Call\Email\ContactRegionEmail;
use BaksDev\Contacts\Region\Type\Call\Gps\ContactRegionGps;
use BaksDev\Users\Address\Type\Geocode\GeocodeAddressUid;
use Symfony\Component\Validator\Constraints as Assert;

final class ContactsRegionCallInfoDTO implements ContactsRegionCallInfoInterface
{
    /** Адрес колл-центра */
    private ?string $address = null;

    /** Контактный Email */
    #[Assert\Email]
    private ?ContactRegionEmail $email = null;

    /** Режим работы: */
    private ?string $working = null;

    /** GPS широта:*/
    private ?ContactRegionGps $latitude = null;

    /** GPS долгота:*/
    private ?ContactRegionGps $longitude = null;

    /** Координаты на карте */
    private ?GeocodeAddressUid $geocode = null;

    /** Адрес колл-центра */
    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): void
    {
        $this->address = $address;
    }

    /** Контактный Email */
    public function getEmail(): ?ContactRegionEmail
    {
        return $this->email;
    }

    public function setEmail(?ContactRegionEmail $email): void
    {
        $this->email = $email;
    }

    /** Режим работы: */
    public function getWorking(): ?string
    {
        return $this->working;
    }

    public function setWorking(?string $working): void
    {
        $this->working = $working;
    }

    /** GPS широта:*/
    public function getLatitude(): ?ContactRegionGps
    {
        return $this->latitude;
    }

    public function setLatitude(?ContactRegionGps $latitude): void
    {
        $this->latitude = $latitude;
    }

    /** GPS долгота:*/
    
    public function getLongitude(): ?ContactRegionGps
    {
        return $this->longitude;
    }

    public function setLongitude(?ContactRegionGps $longitude): void
    {
        $this->longitude = $longitude;
    }


    /** Координаты на карте */

    public function getGeocode(): ?GeocodeAddressUid
    {
        return $this->geocode;
    }

    public function setGeocode(?GeocodeAddressUid $geocode): void
    {
        $this->geocode = $geocode;
    }
}
