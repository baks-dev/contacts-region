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

namespace BaksDev\Contacts\Region\UseCase\Admin\Delete;

use BaksDev\Contacts\Region\Entity\Call\ContactsRegionCallInterface;
use BaksDev\Contacts\Region\Type\Call\Const\ContactsRegionCallConst;
use BaksDev\Contacts\Region\Type\Call\ContactsRegionCallUid;
use Symfony\Component\Validator\Constraints as Assert;

final class ContactsRegionDeleteDTO implements ContactsRegionCallInterface
{
    /** Идентификатор колл-центра */
    #[Assert\NotBlank]
    private ContactsRegionCallUid $id;

    /** Постоянный неизменяемый идентификатор */
    #[Assert\NotBlank]
    private readonly ContactsRegionCallConst $const;

    /** Идентификатор колл-центра */
    public function getId(): ContactsRegionCallUid
    {
        return $this->id;
    }

    public function setId(ContactsRegionCallUid $id): void
    {
        $this->id = $id;
    }


    /** Постоянный неизменяемый идентификатор */
    public function getConst(): ContactsRegionCallConst
    {
        return $this->const;
    }

    public function setConst(ContactsRegionCallConst $const): void
    {
        $this->const = $const;
    }
}
