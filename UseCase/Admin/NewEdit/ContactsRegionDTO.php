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

use BaksDev\Contacts\Region\Entity\Event\ContactsRegionEventInterface;
use BaksDev\Contacts\Region\Type\Event\ContactsRegionEventUid;
use BaksDev\Reference\Region\Type\Id\RegionUid;
use Symfony\Component\Validator\Constraints as Assert;

/** @see ContactsRegionEvent */
final class ContactsRegionDTO implements ContactsRegionEventInterface
{
    /** Идентификатор региона */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    private ?RegionUid $region = null;

    #[Assert\Uuid]
    private ?ContactsRegionEventUid $id = null;

    /** Колл-центр */
    private Call\ContactsRegionCallDTO $calls;

    /** Сортировка */
    private int $sort = 500;

    public function __construct()
    {
        $this->calls = new Call\ContactsRegionCallDTO();
    }
    
    /** Идентификатор региона */
    public function getRegion(): ?RegionUid
    {
        return $this->region;
    }

    public function setRegion(RegionUid $region): void
    {
        $this->region = $region;
    }

    /**
     * Id
     */


    public function setId(?ContactsRegionEventUid $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getEvent(): ?ContactsRegionEventUid
    {
        return $this->id;
    }


    /** Колл-центры */




    public function getCalls(): Call\ContactsRegionCallDTO
    {
        return $this->calls;
    }


    public function setCalls(Call\ContactsRegionCallDTO $calls): void
    {
        $this->calls = $calls;
    }


    /** Сортировка региона */
    public function getSort(): int
    {
        return $this->sort;
    }

    public function setSort(int $sort): void
    {
        $this->sort = $sort;
    }



}
