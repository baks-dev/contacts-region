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

namespace BaksDev\Contacts\Region\Repository\ContactCallDetail;

use BaksDev\Contacts\Region\Entity\Call\ContactsRegionCall;
use BaksDev\Contacts\Region\Entity\Call\Cover\ContactsRegionCallCover;
use BaksDev\Contacts\Region\Entity\Call\Info\ContactsRegionCallInfo;
use BaksDev\Contacts\Region\Entity\Call\Phone\ContactsRegionCallPhone;
use BaksDev\Contacts\Region\Entity\Call\Trans\ContactsRegionCallTrans;
use BaksDev\Contacts\Region\Entity\ContactsRegion;
use BaksDev\Contacts\Region\Entity\Event\ContactsRegionEvent;
use BaksDev\Contacts\Region\Type\Call\ContactsRegionCallUid;
use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Core\Type\Locale\Locale;
use BaksDev\Reference\Region\Entity\Event\RegionEvent;
use BaksDev\Reference\Region\Entity\Invariable\RegionInvariable;
use BaksDev\Reference\Region\Entity\Region;
use BaksDev\Reference\Region\Entity\Trans\RegionTrans;
use BaksDev\Reference\Region\Type\Id\RegionUid;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ContactCallDetailRepository implements ContactCallDetailInterface
{

    private TranslatorInterface $translator;
    private DBALQueryBuilder $DBALQueryBuilder;

    public function __construct(
        DBALQueryBuilder $DBALQueryBuilder,
        TranslatorInterface $translator,
    )
    {

        $this->translator = $translator;
        $this->DBALQueryBuilder = $DBALQueryBuilder;
    }


    /**
     * Метод возвращает детальную информацию о региональном контакте по его идентификатору
     */
    public function fetchContactCallDetailById(ContactsRegionCallUid $call): ?array
    {

        $qb = $this->DBALQueryBuilder->createQueryBuilder(self::class);

        $qb->from(ContactsRegionCall::class, 'call');

        $qb->addSelect('call_trans.name')
            ->addGroupBy('call_trans.name');

        $qb->addSelect('call_trans.description')
            ->addGroupBy('call_trans.description');


        $qb->addSelect('call_info.address AS call_address')
            ->addGroupBy('call_info.address');

        $qb->addSelect('call_info.email AS call_email')
            ->addGroupBy('call_info.email');

        $qb->addSelect('call_info.working AS call_working')
            ->addGroupBy('call_info.working');

        $qb->addSelect('call_info.latitude AS call_latitude')
            ->addGroupBy('call_info.latitude');

        $qb->addSelect('call_info.longitude AS call_longitude')
            ->addGroupBy('call_info.longitude');

        $qb->addSelect('call.pickup AS call_pickup')
            ->addGroupBy('call.pickup');

        $qb->addSelect('call.stock AS call_stock')
            ->addGroupBy('call.stock');

        $qb->leftJoin(
            'call',
            ContactsRegionCallInfo::class,
            'call_info',
            'call_info.call = call.id'
        );

        $qb->leftJoin(
            'call',
            ContactsRegionCallTrans::class,
            'call_trans',
            'call_trans.call = call.id AND call_trans.local = :local'
        );


        $qb->addSelect("JSON_AGG
		( DISTINCT
				
					JSONB_BUILD_OBJECT
					(
						'call_phone_name', call_phone.name,
						'call_phone_number', call_phone.phone
					)
					
			) AS calls_phone
		"
        );

        $qb->leftJoin('call',
            ContactsRegionCallPhone::class,
            'call_phone',
            'call_phone.call = call.id'
        );

        $qb->where('call.id = :call');

        $qb->setParameter('call', $call, ContactsRegionCallUid::TYPE);
        $qb->setParameter('local', new Locale($this->translator->getLocale()), Locale::TYPE);


        /* Кешируем результат DBAL */
        return $qb
            ->enableCache('contacts-region', 86400)
            ->fetchAssociative();


    }

    public function fetchContactCallAllAssociative(?RegionUid $region = null): ?array
    {
        $qb = $this->DBALQueryBuilder->createQueryBuilder(self::class);

        $qb->setParameter('local', new Locale($this->translator->getLocale()), Locale::TYPE);

        $qb->from(ContactsRegion::class, 'contact_region');

        $qb->join(
            'contact_region',
            Region::class,
            'region',
            'region.id = contact_region.id'
        );

        $qb->join(
            'region',
            RegionInvariable::class,
            'region_invariable',
            'region_invariable.main = region.id AND region_invariable.active = true'
        );

        $qb->join(
            'region',
            RegionEvent::class,
            'region_event',
            'region_event.id = region.event'
        );

        $qb->addSelect('region_trans.name AS region_name')
            ->addGroupBy('region_trans.name');
        $qb->addSelect('region_trans.description AS region_description')
            ->addGroupBy('region_trans.description');

        $qb->leftJoin(
            'region',
            RegionTrans::class,
            'region_trans',
            'region_trans.event = region.event AND region_trans.local = :local'
        );

        $qb->leftJoin(
            'contact_region',
            ContactsRegionEvent::class,
            'contact_region_event',
            'contact_region_event.id = contact_region.event'
        );

        $qb->leftJoin(
            'contact_region_event',
            ContactsRegionCall::class,
            'contact_region_call',
            'contact_region_call.event = contact_region_event.id'
        );

        $qb->addSelect(
            "JSON_AGG
		( DISTINCT
				
					JSONB_BUILD_OBJECT
					(
						'0', contact_region_call.sort,
						'call_uid', contact_region_call.id,
	
						'call_name', contact_region_call_trans.name,
						'call_description', contact_region_call_trans.description,
						'call_address', contact_region_call_info.address,
						'call_email', contact_region_call_info.email,
						'call_working', contact_region_call_info.working,
						
						'call_latitude', contact_region_call_info.latitude,
						
						'call_longitude', contact_region_call_info.longitude,

						'call_cover_name', CASE
						   WHEN contact_region_call_cover.name IS NOT NULL THEN
								CONCAT ( '/upload/".$qb->table(ContactsRegionCallCover::class)."' , '/', contact_region_call_cover.name)
						   ELSE NULL
						END,
					
						'call_cover_ext', contact_region_call_cover.ext,
						'call_cover_cdn', contact_region_call_cover.cdn

					)
					
			) AS calls
		"
        );

        $qb->leftJoin(
            'contact_region_call',
            ContactsRegionCallTrans::class,
            'contact_region_call_trans',
            'contact_region_call_trans.call = contact_region_call.id AND contact_region_call_trans.local = :local'
        );

        $qb->addSelect(
            "JSON_AGG
		( DISTINCT
				
					JSONB_BUILD_OBJECT
					(
						'0', contact_region_call.sort,
						'call_uid', contact_region_call.id,
						'call_phone_name', contact_region_call_phone.name,
						'call_phone_number', contact_region_call_phone.phone
					)
					
			) AS calls_phone
		"
        );

        $qb->leftJoin(
            'contact_region_call',
            ContactsRegionCallPhone::class,
            'contact_region_call_phone',
            'contact_region_call_phone.call = contact_region_call.id'
        );

        $qb->leftJoin(
            'contact_region_call',
            ContactsRegionCallInfo::class,
            'contact_region_call_info',
            'contact_region_call_info.call = contact_region_call.id'
        );

        $qb->leftJoin(
            'contact_region_call',
            ContactsRegionCallCover::class,
            'contact_region_call_cover',
            'contact_region_call_cover.call = contact_region_call.id'
        );

        if($region)
        {
            $qb->where('contact_region.id = :region');
            $qb->setParameter('region', $region, RegionUid::TYPE);
        }


        /* Кешируем результат DBAL */
        return $qb
            ->enableCache('contacts-region', 86400)
            ->fetchAllAssociative();

    }
}
