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

namespace BaksDev\Contacts\Region\UseCase\Admin\NewEdit\Call;

use BaksDev\Contacts\Region\Entity\Call\ContactsRegionCallInterface;
use BaksDev\Contacts\Region\Type\Call\Const\ContactsRegionCallConst;
use BaksDev\Contacts\Region\UseCase\Admin\NewEdit\Call\Info\ContactsRegionCallInfoDTO;
use BaksDev\Core\Type\Locale\Locale;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use Doctrine\Common\Collections\ArrayCollection;
use ReflectionProperty;
use Symfony\Component\Validator\Constraints as Assert;

/** @see ContactsRegionCall */
final class ContactsRegionCallDTO implements ContactsRegionCallInterface
{
//    /** Идентификатор контактного региона */
//    private ?ContactsRegionCallUid $id = null;

    /** Постоянный неизменяемый идентификатор */
    #[Assert\Uuid]
    private readonly ContactsRegionCallConst $const;

    /** Ответственное лицо (Профиль пользователя) */
    private ?UserProfileUid $profile = null;

    /** Обложка */
    #[Assert\Valid]
    private Cover\ContactsRegionCallCoverDTO $cover;

    /** Перевод */
    #[Assert\Valid]
    private ArrayCollection $translate;

    /** Контактные номера телефонов Колл-центра */
    #[Assert\Valid]
    private ArrayCollection $phone;

    /** Контактная информация */
    #[Assert\Valid]
    private ContactsRegionCallInfoDTO $info;

    /** Пункт самовывоза (выводит в списки пунктов самовывоза) */
    private bool $pickup = false;

    /** Склад (выводит в списки складских помещений) */
    private bool $stock = false;

    /** Сортировка */
    private int $sort = 500;

    public function __construct()
    {
        $this->info = new ContactsRegionCallInfoDTO();
        $this->phone = new ArrayCollection();
        $this->translate = new ArrayCollection();
        $this->cover = new Cover\ContactsRegionCallCoverDTO();
    }
    
//    public function setId(): ?ContactsRegionCallUid
//    {
//        return $this->id;
//    }
//
//    public function getCallUid(): ?ContactsRegionCallUid
//    {
//        return $this->id;
//    }



    /** Постоянный неизменяемый идентификатор */
    public function getConst(): ContactsRegionCallConst
    {
        if (!(new ReflectionProperty(self::class, 'const'))->isInitialized($this)) {
            $this->const = new ContactsRegionCallConst();
        }
        
        return $this->const;
    }

    public function setConst(ContactsRegionCallConst $const): void
    {
        if (!(new ReflectionProperty(self::class, 'const'))->isInitialized($this)) {
            $this->const = $const;
        }
    }

    /** Контактные номера телефонов Колл-центра */
    public function getPhone(): ArrayCollection
    {
        if ($this->phone->isEmpty()) {
            $this->addPhone(new Phone\ContactsRegionCallPhoneDTO());
        }

        return $this->phone;
    }

    public function addPhone(Phone\ContactsRegionCallPhoneDTO $phone): void
    {
        if (!$this->phone->contains($phone)) {
            $this->phone->add($phone);
        }
    }

    public function removePhone(Phone\ContactsRegionCallPhoneDTO $phone): void
    {
        $this->phone->removeElement($phone);
    }

    /** Контактная информация */
    public function getInfo(): ContactsRegionCallInfoDTO
    {
        return $this->info;
    }

    public function setInfo(ContactsRegionCallInfoDTO $info): void
    {
        $this->info = $info;
    }

    /** Перевод */
    public function setTranslate(ArrayCollection $trans): void
    {
        $this->translate = $trans;
    }

    public function getTranslate(): ArrayCollection
    {
        // Вычисляем расхождение и добавляем неопределенные локали
        foreach (Locale::diffLocale($this->translate) as $locale) {
            $ContactsRegionCallTransDTO = new Trans\ContactsRegionCallTransDTO();
            $ContactsRegionCallTransDTO->setLocal($locale);
            $this->addTranslate($ContactsRegionCallTransDTO);
        }

        return $this->translate;
    }

    public function addTranslate(Trans\ContactsRegionCallTransDTO $trans): void
    {
        if(empty($trans->getLocal()->getLocalValue()))
        {
            return;
        }

        if (!$this->translate->contains($trans)) {
            $this->translate->add($trans);
        }
    }

    public function removeTranslate(Trans\ContactsRegionCallTransDTO $trans): void
    {
        $this->translate->removeElement($trans);
    }

    /** Пункт самовывоза (выводит в списки вынктов самовывоза) */
    public function getPickup(): bool
    {
        return $this->pickup;
    }

    public function setPickup(bool $pickup): void
    {
        $this->pickup = $pickup;
    }

    /** Сортировка */
    public function getSort(): int
    {
        return $this->sort;
    }

    public function setSort(int $sort): void
    {
        $this->sort = $sort;
    }

    /** Обложка */
    public function getCover(): Cover\ContactsRegionCallCoverDTO
    {
        return $this->cover;
    }

    public function setCover(Cover\ContactsRegionCallCoverDTO $cover): void
    {
        $this->cover = $cover;
    }

    /** Склад  */
    public function getStock(): bool
    {
        return $this->stock;
    }

    public function setStock(bool $stock): void
    {
        $this->stock = $stock;
    }

    /** Ответственное лицо */
    public function getProfile(): ?UserProfileUid
    {
        return $this->profile;
    }

    public function setProfile(?UserProfileUid $profile): void
    {
        $this->profile = $profile;
    }
}
