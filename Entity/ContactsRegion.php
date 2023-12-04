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

namespace BaksDev\Contacts\Region\Entity;

use BaksDev\Contacts\Region\Entity\Event\ContactsRegionEvent;
use BaksDev\Contacts\Region\Type\Event\ContactsRegionEventUid;
use BaksDev\Reference\Region\Type\Id\RegionUid;
use Doctrine\ORM\Mapping as ORM;

// ContactsRegion

#[ORM\Entity]
#[ORM\Table(name: 'contacts_region')]
class ContactsRegion
{
    public const TABLE = 'contacts_region';

    /** ID */
    #[ORM\Id]
    #[ORM\Column(type: RegionUid::TYPE)]
    private RegionUid $id;

    /** ID События */
    #[ORM\Column(type: ContactsRegionEventUid::TYPE, unique: true)]
    private ContactsRegionEventUid $event;

    public function __construct(RegionUid $region)
    {
        $this->id = $region;
    }

    public function __toString(): string
    {
        return (string) $this->id;
    }

    public function getId(): RegionUid
    {
        return $this->id;
    }


    public function getEvent(): ContactsRegionEventUid
    {
        return $this->event;
    }

    public function setEvent(ContactsRegionEventUid|ContactsRegionEvent $event): void
    {
        $this->event = $event instanceof ContactsRegionEvent ? $event->getId() : $event;
    }

    public function restore(RegionUid $id): void
    {
        $this->id = $id;
    }
}
