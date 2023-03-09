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

namespace BaksDev\Contacts\Region\Entity\Event;

use App\System\Type\Locale\Locale;
use BaksDev\Contacts\Region\Entity\Call\ContactsRegionCall;
use BaksDev\Contacts\Region\Entity\ContactsRegion;
use BaksDev\Contacts\Region\Entity\Modify\ContactsRegionModify;
use BaksDev\Contacts\Region\Entity\Trans\ContactsRegionTrans;
use BaksDev\Contacts\Region\Type\Event\ContactsRegionEventUid;
use BaksDev\Contacts\Region\Type\Id\ContactsRegionUid;
use BaksDev\Core\Type\Modify\ModifyAction;
use BaksDev\Core\Type\Modify\ModifyActionEnum;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;
use BaksDev\Core\Entity\EntityEvent;
use BaksDev\Core\Entity\EntityState;

use InvalidArgumentException;

/* ContactsRegionEvent */


#[ORM\Entity]
#[ORM\Table(name: 'contacts_region_event')]
class ContactsRegionEvent extends EntityEvent
{
	public const TABLE = 'contacts_region_event';
	
	/** ID */
	#[ORM\Id]
	#[ORM\Column(type: ContactsRegionEventUid::TYPE)]
	private ContactsRegionEventUid $id;
	
	/** ID ContactsRegion */
	#[ORM\Column(type: ContactsRegionUid::TYPE, nullable: false)]
	private ?ContactsRegionUid $main = null;
	
	/** One To One */
	//#[ORM\OneToOne(mappedBy: 'event', targetEntity: ContactsRegionLogo::class, cascade: ['all'])]
	//private ?ContactsRegionOne $one = null;
	
	/** Модификатор */
	#[ORM\OneToOne(mappedBy: 'event', targetEntity: ContactsRegionModify::class, cascade: ['all'])]
	private ContactsRegionModify $modify;
	
	/** Перевод */
	#[ORM\OneToMany(mappedBy: 'event', targetEntity: ContactsRegionTrans::class, cascade: ['all'])]
	private Collection $translate;
	
	/** Колл-центр */
	#[ORM\OneToMany(mappedBy: 'event', targetEntity: ContactsRegionCall::class, cascade: ['all'])]
	private Collection $call;
	
	public function __toString() : string
	{
		return (string) $this->id;
	}
	
	
	public function __construct()
	{
		$this->id = new ContactsRegionEventUid();
		$this->modify = new ContactsRegionModify($this);
		
	}
	
	
	
	
	public function __clone()
	{
		$this->id = new ContactsRegionEventUid();
	}
	
	
	public function getId() : ContactsRegionEventUid
	{
		return $this->id;
	}
	
	
	public function setMain(ContactsRegionUid|ContactsRegion $main) : void
	{
		$this->main = $main instanceof ContactsRegion ? $main->getId() : $main;
	}
	
	
	public function getMain() : ?ContactsRegionUid
	{
		return $this->main;
	}
	
	
	public function getDto($dto) : mixed
	{
		if($dto instanceof ContactsRegionEventInterface)
		{
			return parent::getDto($dto);
		}
		
		throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
	}
	
	
	public function setEntity($dto) : mixed
	{
		if($dto instanceof ContactsRegionEventInterface)
		{
			return parent::setEntity($dto);
		}
		
		throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
	}
	
	
	public function isModifyActionEquals(ModifyActionEnum $action) : bool
	{
		return $this->modify->equals($action);
	}
	
	//	public function getUploadClass() : ContactsRegionImage
	//	{
	//		return $this->image ?: $this->image = new ContactsRegionImage($this);
	//	}
	
	//	public function getNameByLocale(Locale $locale) : ?string
	//	{
	//		$name = null;
	//		
	//		/** @var ContactsRegionTrans $trans */
	//		foreach($this->translate as $trans)
	//		{
	//			if($name = $trans->name($locale))
	//			{
	//				break;
	//			}
	//		}
	//		
	//		return $name;
	//	}
}