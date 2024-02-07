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

namespace BaksDev\Contacts\Region\Form\ContactRegionChoice;

use BaksDev\Contacts\Region\Repository\ContactCallRegion\ContactCallRegionInterface;
use BaksDev\Contacts\Region\Type\Call\ContactsRegionCallUid;
use Symfony\Component\Form\DataTransformerInterface;

final class ContactRegionFieldTransformer implements DataTransformerInterface
{
	private ContactCallRegionInterface $callRegion;
	
	public function __construct(ContactCallRegionInterface $callRegion) {
		$this->callRegion = $callRegion;
	}
	
	public function transform(mixed $value): ContactRegionFieldDTO
    {
        if($value && !preg_match('{^[0-9a-f]{8}(?:-[0-9a-f]{4}){3}-[0-9a-f]{12}$}Di', $value))
        {
            $value = null;
        }

		$ContactRegionFieldDTO = new ContactRegionFieldDTO();

		if($value)
		{
			$ContactsRegionCallUid = $this->callRegion->getContactCall(new ContactsRegionCallUid($value));
			
			if($ContactsRegionCallUid)
			{
				$ContactRegionFieldDTO->setRegion($ContactsRegionCallUid->getAttr());
				$ContactRegionFieldDTO->setCall($ContactsRegionCallUid);
			}
		}

		return $ContactRegionFieldDTO;
	}


	public function reverseTransform(mixed $value) : ?string
	{
		/** @var ContactRegionFieldDTO $value */
		return (string) $value->getCall()?->getValue();
	}
	
}