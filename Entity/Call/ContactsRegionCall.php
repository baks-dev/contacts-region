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
use BaksDev\Contacts\Region\Type\Call\Const\ContactsRegionCallConst;
use BaksDev\Contacts\Region\Type\Call\ContactsRegionCallUid;
use BaksDev\Core\Entity\EntityEvent;
use BaksDev\Core\Type\Locale\Locale;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use Symfony\Component\Validator\Constraints as Assert;

// Перевод ContactsRegionCall

#[ORM\Entity]
#[ORM\Table(name: 'contacts_region_call')]
#[ORM\Index(columns: ['pickup'])]
#[ORM\Index(columns: ['stock'])]
#[ORM\Index(columns: ['active'])]
#[ORM\Index(columns: ['sort'])]
class ContactsRegionCall extends EntityEvent
{
    public const TABLE = 'contacts_region_call';

    /** ID */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Id]
    #[ORM\Column(type: ContactsRegionCallUid::TYPE)]
    private ContactsRegionCallUid $id;

    /** Связь на событие */
    #[ORM\ManyToOne(targetEntity: ContactsRegionEvent::class, inversedBy: 'call')]
    #[ORM\JoinColumn(name: 'event', referencedColumnName: 'id', nullable: true)]
    private ?ContactsRegionEvent $event = null;

    /** Const */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Column(type: ContactsRegionCallConst::TYPE, nullable: true)]
    private ContactsRegionCallConst $const;

    /** Ответственное лицо (Профиль пользователя) */
    #[Assert\Uuid]
    #[ORM\Column(type: UserProfileUid::TYPE, nullable: true)]
    private ?UserProfileUid $profile = null;

    /** Обложка */
    #[ORM\OneToOne(mappedBy: 'call', targetEntity: Cover\ContactsRegionCallCover::class, cascade: ['all'])]
    #[ORM\OrderBy(['sort' => 'ASC'])]
    private ?Cover\ContactsRegionCallCover $cover = null;

    /** Перевод */
    #[Assert\Valid]
    #[Assert\Count(min: 1)]
    #[ORM\OneToMany(mappedBy: 'call', targetEntity: ContactsRegionCallTrans::class, cascade: ['all'])]
    private Collection $translate;

    /** Контактные номера телефонов Колл-центра */
    #[Assert\Valid]
    #[ORM\OneToMany(mappedBy: 'call', targetEntity: ContactsRegionCallPhone::class, cascade: ['all'])]
    private Collection $phone;

    /** Контактная информация */
    #[Assert\Valid]
    #[ORM\OneToOne(mappedBy: 'call', targetEntity: ContactsRegionCallInfo::class, cascade: ['all'])]
    private ContactsRegionCallInfo $info;

    /** Пункт самовывоза (выводит в списки пунктов самовывоза) */
    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private bool $pickup = false;

    /** Склад (выводит в списки складских помещений) */
    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private bool $stock = false;

    /** Сортировка */
    #[Assert\NotBlank]
    #[ORM\Column(type: Types::SMALLINT, options: ['default' => 500])]
    private int $sort = 500;

    /** Флаг активности */
    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => true])]
    private bool $active = true;

    public function __construct(ContactsRegionEvent $event)
    {
        $this->id = new ContactsRegionCallUid();
        $this->const = new ContactsRegionCallConst();
        $this->event = $event;
    }

    public function __clone(): void
    {
        $this->id = clone $this->id;
    }

    public function __toString(): string
    {
        return (string) $this->id;
    }

    public function getId(): ContactsRegionCallUid
    {
        return $this->id;
    }

    public function getConst(): ContactsRegionCallConst
    {
        return $this->const;
    }

    public function getEvent(): ContactsRegionEvent
    {
        return $this->event;
    }

    public function remove(): void
    {
        $this->event = null;
    }

    public function getDto($dto): mixed
    {
        $dto = is_string($dto) && class_exists($dto) ? new $dto() : $dto;

        if ($dto instanceof ContactsRegionCallInterface)
        {
            return parent::getDto($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }

    public function setEntity($dto): mixed
    {
        if ($dto instanceof ContactsRegionCallInterface || $dto instanceof self)
        {
            return parent::setEntity($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }


	public function getNameByLocale(Locale $locale) : ?string
	{
		$name = null;

		/** @var ContactsRegionCallTrans $trans */
		foreach($this->translate as $trans)
		{
			if($name = $trans->name($locale))
			{
				break;
			}
		}
		
		return $name;
	}

}
