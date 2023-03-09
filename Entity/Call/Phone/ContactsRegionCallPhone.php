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

namespace BaksDev\Contacts\Region\Entity\Call\Phone;

use BaksDev\Contacts\Region\Entity\Call\ContactsRegionCall;
use BaksDev\Contacts\Region\Type\Call\ContactsRegionCallUid;
use BaksDev\Contacts\Region\Type\Call\Phone\ContactsRegionCallPhoneUid;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;
use BaksDev\Core\Entity\EntityEvent;
use BaksDev\Core\Entity\EntityState;
use BaksDev\Core\Type\Locale\Locale;
use InvalidArgumentException;

/* Перевод ContactsRegionCallPhone */


#[ORM\Entity]
#[ORM\Table(name: 'contacts_region_call_phone')]
#[ORM\Index(columns: ['name'])]
class ContactsRegionCallPhone extends EntityEvent
{
	public const TABLE = 'contacts_region_call_phone';
	
	/** ID */
	#[ORM\Id]
	#[ORM\Column(type: ContactsRegionCallPhoneUid::TYPE)]
	private ContactsRegionCallPhoneUid $id;
	
	/** Связь на колл-центр */
	#[ORM\ManyToOne(targetEntity: ContactsRegionCall::class, inversedBy: "phone")]
	#[ORM\JoinColumn(name: 'event', referencedColumnName: "id")]
	private ContactsRegionCall $call;
	
	/** Название */
	#[ORM\Column(type: Types::STRING, length: 100)]
	private string $name;
	
	
	public function __construct(ContactsRegionCall $call)
	{
		$this->call = $call;
	}
	
	
	public function getDto($dto) : mixed
	{
		if($dto instanceof ContactsRegionCallPhoneInterface)
		{
			return parent::getDto($dto);
		}
		
		throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
	}
	
	
	public function setEntity($dto) : mixed
	{
		
		if($dto instanceof ContactsRegionCallPhoneInterface)
		{
			return parent::setEntity($dto);
		}
		
		throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
	}
	
	
	public function name(Locale $locale) : ?string
	{
		if($this->local->getValue() === $locale->getValue())
		{
			return $this->name;
		}
		
		return null;
	}
	
}