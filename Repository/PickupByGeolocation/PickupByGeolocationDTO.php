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

use BaksDev\Contacts\Region\Type\Call\ContactsRegionCallUid;
use BaksDev\Core\Type\Gps\GpsLatitude;
use BaksDev\Core\Type\Gps\GpsLongitude;

final readonly class PickupByGeolocationDTO
{
    /** Идентификатор */
    private ContactsRegionCallUid $id;

    /** GPS широта:*/
    private GpsLatitude $latitude;

    /** GPS долгота:*/
    private GpsLongitude $longitude;

    public function __construct(
        $id,
        $latitude,
        $longitude,
    )
    {
        $this->id = new ContactsRegionCallUid($id);
        $this->latitude = new GpsLatitude($latitude);
        $this->longitude = new GpsLongitude($longitude);
    }

    /**
     * Id
     */
    public function getId(): ContactsRegionCallUid
    {
        return $this->id;
    }

    /**
     * Latitude
     */
    public function getLatitude(): GpsLatitude
    {
        return $this->latitude;
    }

    /**
     * Longitude
     */
    public function getLongitude(): GpsLongitude
    {
        return $this->longitude;
    }


}