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

use BaksDev\Contacts\Region\Entity\Call\ContactsRegionCall;
use BaksDev\Contacts\Region\Entity\Call\Trans\ContactsRegionCallTrans;
use BaksDev\Contacts\Region\Entity\ContactsRegion;
use BaksDev\Contacts\Region\Entity\Modify\ContactsRegionModify;
use BaksDev\Contacts\Region\Type\Event\ContactsRegionEventUid;
use BaksDev\Core\Entity\EntityEvent;
use BaksDev\Core\Type\Locale\Locale;
use BaksDev\Reference\Region\Type\Id\RegionUid;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use Symfony\Component\Validator\Constraints as Assert;

// ContactsRegionEvent

#[ORM\Entity]
#[ORM\Table(name: 'contacts_region_event')]
class ContactsRegionEvent extends EntityEvent
{
    public const TABLE = 'contacts_region_event';

    /** ID */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Id]
    #[ORM\Column(type: ContactsRegionEventUid::TYPE)]
    private ContactsRegionEventUid $id;

    /** ID ContactsRegion */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Column(type: RegionUid::TYPE, nullable: false)]
    private ?RegionUid $region;

    /** One To One */
    // #[ORM\OneToOne(targetEntity: ContactsRegionLogo::class, mappedBy: 'event',  cascade: ['all'])]
    // private ?ContactsRegionOne $one = null;

    /** Модификатор */
    #[ORM\OneToOne(targetEntity: ContactsRegionModify::class, mappedBy: 'event', cascade: ['all'])]
    private ContactsRegionModify $modify;


    /** Колл-центры */
    #[ORM\OneToMany(targetEntity: ContactsRegionCall::class, mappedBy: 'event', cascade: ['all'])]
    #[ORM\OrderBy(['sort' => 'ASC'])]
    private Collection $call;

    /** Сортировка */
    #[Assert\NotBlank]
    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private int $sort = 500;

    public function __construct()
    {
        $this->id = new ContactsRegionEventUid();
        //$this->modify = new ContactsRegionModify($this);
        $this->call = new ArrayCollection();
    }

    public function __clone()
    {
        $this->id = clone $this->id;
    }

    public function __toString(): string
    {
        return (string) $this->id;
    }

    public function getId(): ContactsRegionEventUid
    {
        return $this->id;
    }

    public function setMain(RegionUid|ContactsRegion $region): void
    {
        $this->region = $region instanceof ContactsRegion ? $region->getId() : $region;
    }

    public function getMain(): ?RegionUid
    {
        return $this->region;
    }

    public function getCall(): Collection
    {
        return $this->call;
    }

    public function getDto($dto): mixed
    {
        $dto = is_string($dto) && class_exists($dto) ? new $dto() : $dto;

        if ($dto instanceof ContactsRegionEventInterface)
        {
            return parent::getDto($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }

    public function setEntity($dto): mixed
    {

        if ($dto instanceof ContactsRegionEventInterface || $dto instanceof self)
        {
            return parent::setEntity($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }

    public function getNameByLocale(Locale $locale): ?string
    {
        $name = null;

        /** @var ContactsRegionCallTrans $trans */
        foreach ($this->call as $trans)
        {
            if ($name = $trans->name($locale))
            {
                break;
            }
        }

        return $name;
    }
}
