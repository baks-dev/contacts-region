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

namespace BaksDev\Contacts\Region\Type\Call;

use App\Kernel;
use BaksDev\Core\Type\UidType\Uid;
use Symfony\Component\Uid\AbstractUid;

final class ContactsRegionCallUid extends Uid
{
    public const string TEST = '4743e769-7adf-75cf-97c8-281ac62ce691';

    public const string TYPE = 'contacts_region_call';

    private mixed $attr;

    private mixed $option;

    private ?int $counter;

    private mixed $name;

    public function __construct(
        AbstractUid|self|string|null $value = null,
        mixed $name = null,
        mixed $attr = null,
        mixed $option = null,
        mixed $counter = null,
    ) {
        parent::__construct($value);

        $this->name = $name;
        $this->attr = $attr;
        $this->option = $option;
        $this->counter = $counter;
    }

    public function getName(): mixed
    {
        return $this->name;
    }

    public function getAttr(): mixed
    {
        return $this->attr;
    }

    public function getOption(): mixed
    {
        return $this->option;
    }

    public function getCounter(): mixed
    {
        return $this->counter;
    }
}
