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

namespace BaksDev\Contacts\Region\Entity\Call;

use BaksDev\Contacts\Region\Entity\Call\Info\ContactsRegionCallInfo;
use BaksDev\Contacts\Region\Entity\Call\Phone\ContactsRegionCallPhone;
use BaksDev\Contacts\Region\Entity\Call\Trans\ContactsRegionCallTrans;
use BaksDev\Contacts\Region\Entity\Event\ContactsRegionEvent;
use BaksDev\Contacts\Region\Type\Call\ContactsRegionCallUid;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;
use BaksDev\Core\Entity\EntityEvent;
use BaksDev\Core\Entity\EntityState;
use BaksDev\Core\Type\Locale\Locale;
use InvalidArgumentException;

/* Перевод ContactsRegionCall */


#[ORM\Entity]
#[ORM\Table(name: 'contacts_region_call')]
#[ORM\Index(columns: ['name'])]
class ContactsRegionCall extends EntityEvent
{
	public const TABLE = 'contacts_region_call';
	
	/** ID */
	#[ORM\Id]
	#[ORM\Column(type: ContactsRegionCallUid::TYPE)]
	private ContactsRegionCallUid $id;
	
	/** Связь на событие */
	#[ORM\ManyToOne(targetEntity: ContactsRegionEvent::class, inversedBy: "call")]
	#[ORM\JoinColumn(name: 'event', referencedColumnName: "id")]
	private readonly ContactsRegionEvent $event;
	
	/** Перевод */
	#[ORM\OneToMany(mappedBy: 'event', targetEntity: ContactsRegionCallTrans::class, cascade: ['all'])]
	private Collection $translate;
	
	/** Контактные номера телефонов Колл-центра */
	#[ORM\OneToMany(mappedBy: 'event', targetEntity: ContactsRegionCallPhone::class, cascade: ['all'])]
	private Collection $phone;
	
	/** Контактная информация */
	#[ORM\OneToOne(mappedBy: 'call', targetEntity: ContactsRegionCallInfo::class, cascade: ['all'])]
	private ContactsRegionCallInfo $info;
	
	/** Пункт самовывоза (выводит в списки вынктов самовывоза) */
	#[ORM\Column(type: Types::BOOLEAN)]
	private bool $pickup = false;
	
	/** Сортировка */
	#[ORM\Column(type: Types::STRING)]
	private string $sort;
	
	
	
	public function __construct(ContactsRegionCall $event)
	{
		$this->id = new ContactsRegionCallUid();
		$this->event = $event;
	}
	
	
	public function __clone() : void
	{
		$this->id = new ContactsRegionCallUid();
	}
	
	
	public function getDto($dto) : mixed
	{
		if($dto instanceof ContactsRegionCallInterface)
		{
			return parent::getDto($dto);
		}
		
		throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
	}
	
	
	public function setEntity($dto) : mixed
	{
		
		if($dto instanceof ContactsRegionCallInterface)
		{
			return parent::setEntity($dto);
		}
		
		throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
	}
	

	
}