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
use BaksDev\Contacts\Region\Type\Call\Email\ContactRegionEmail;
use BaksDev\Contacts\Region\Type\Call\Gps\ContactRegionGps;
use BaksDev\Core\Entity\EntityEvent;
use BaksDev\Users\Address\Type\Geocode\GeocodeAddressUid;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use Symfony\Component\Validator\Constraints as Assert;

/* ContactsRegionCallInfo */


#[ORM\Entity]
#[ORM\Table(name: 'contacts_region_call_info')]
class ContactsRegionCallInfo extends EntityEvent
{
	/** ID колл-центра */
    #[Assert\NotBlank]
	#[ORM\Id]
    #[ORM\OneToOne(targetEntity: ContactsRegionCall::class, inversedBy: 'info')]
	#[ORM\JoinColumn(name: 'call', referencedColumnName: 'id')]
	private ContactsRegionCall $call;
	
	/** Адрес колл-центра */
	#[ORM\Column(type: Types::STRING, nullable: true)]
	private ?string $address = null;
	
	/** Контактный Email */
	#[ORM\Column(type: ContactRegionEmail::TYPE, nullable: true)]
	private ?ContactRegionEmail $email = null;
	
	/** Режим работы: */
	#[ORM\Column(type: Types::STRING, nullable: true)]
	private ?string $working = null;

	/** GPS широта:*/
	#[ORM\Column(type: ContactRegionGps::TYPE, nullable: true)]
	private ?ContactRegionGps $latitude = null;
	
	/** GPS долгота:*/
	#[ORM\Column(type: ContactRegionGps::TYPE, nullable: true)]
	private ?ContactRegionGps $longitude = null;

    /** Координаты на карте */
    #[ORM\Column(type: GeocodeAddressUid::TYPE, nullable: true)]
    private ?GeocodeAddressUid $geocode = null;
	
	
	public function __construct(ContactsRegionCall $call)
	{
		$this->call = $call;
	}

    public function __toString(): string
    {
        return (string) $this->call;
    }
	
	public function getDto($dto): mixed
	{
        $dto = is_string($dto) && class_exists($dto) ? new $dto() : $dto;

		if($dto instanceof ContactsRegionCallInfoInterface)
		{
			return parent::getDto($dto);
		}
		
		throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
	}
	
	
	public function setEntity($dto): mixed
	{
		if($dto instanceof ContactsRegionCallInfoInterface || $dto instanceof self)
		{
			if(
				empty($dto->getAddress()) &&
				empty($dto->getEmail()) &&
				empty($dto->getLatitude()) &&
				empty($dto->getLength())
			)
			{
				return false;
			}
			
			return parent::setEntity($dto);
		}
		
		throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
	}
	
}