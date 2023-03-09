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

namespace BaksDev\Contacts\Region\Entity\Call\Info;

use BaksDev\Contacts\Region\Entity\Call\ContactsRegionCall;
use BaksDev\Users\User\Entity\User;
use BaksDev\Users\User\Type\Id\UserUid;
use BaksDev\Core\Entity\EntityEvent;
use BaksDev\Core\Entity\EntityState;
use BaksDev\Core\Type\Ip\IpAddress;
use BaksDev\Core\Type\Modify\ModifyAction;
use BaksDev\Core\Type\Modify\ModifyActionEnum;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;

/* Модификаторы событий ContactsRegionCallInfo */


#[ORM\Entity]
#[ORM\Table(name: 'contacts_region_call_info')]
class ContactsRegionCallInfo extends EntityEvent
{
	public const TABLE = 'contacts_region_call_info';
	
	/** ID события */
	#[ORM\Id]
	#[ORM\OneToOne(inversedBy: 'info', targetEntity: ContactsRegionCall::class)]
	#[ORM\JoinColumn(name: 'call', referencedColumnName: 'id')]
	private ContactsRegionCall $call;
	
	/** Адрес */
	#[ORM\Column(type: Types::STRING)]
	private ?string $address = null;
	
	/** Email */
	#[ORM\Column(type: Types::STRING)]
	private ?string $email = null;
	
	/** Режим работы: */
	#[ORM\Column(type: Types::STRING)]
	private ?string $working = null;

	/** GPS широта:*/
	#[ORM\Column(type: Types::STRING)]
	private ?string $latitude = null;
	
	/** GPS долгота:*/
	#[ORM\Column(type: Types::STRING)]
	private ?string $length = null;
	
	
	public function __construct(ContactsRegionCall $call)
	{
		$this->call = $call;
	}

	
	public function getDto($dto) : mixed
	{
		if($dto instanceof ContactsRegionCallInfoInterface)
		{
			return parent::getDto($dto);
		}
		
		throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
	}
	
	
	public function setEntity($dto) : mixed
	{
		if($dto instanceof ContactsRegionCallInfoInterface)
		{
			return parent::setEntity($dto);
		}
		
		throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
	}
	
}