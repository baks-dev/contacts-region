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

namespace BaksDev\Contacts\Region\Repository\ContactCallByRegion;

use BaksDev\Contacts\Region\Entity as ContactsRegionEntity;
use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Core\Type\Locale\Locale;
use BaksDev\Reference\Region\Entity as RegionEntity;
use BaksDev\Reference\Region\Type\Id\RegionUid;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ContactCallByRegion implements ContactCallByRegionInterface
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


    public function fetchContactCallByRegionAssociative(?RegionUid $region, bool $pickup = false): ?array
    {
        $qb = $this->DBALQueryBuilder->createQueryBuilder(self::class);

        $qb->setParameter('local', new Locale($this->translator->getLocale()), Locale::TYPE);

        $qb->from(ContactsRegionEntity\ContactsRegion::TABLE, 'contact_region');

        if($region)
        {
            $qb->where('contact_region.id = :region');
            $qb->setParameter('region', $region, RegionUid::TYPE);
        }

        $qb->join('contact_region', RegionEntity\Region::TABLE, 'region', 'region.id = contact_region.id');

        $qb->join('region',
            RegionEntity\Event\RegionEvent::TABLE,
            'region_event',
            'region_event.id = region.event AND region_event.active = true'
        );

        $qb->addSelect('region_trans.name AS region_name')
            ->addGroupBy('region_trans.name');
        $qb->addSelect('region_trans.description AS region_description')
            ->addGroupBy('region_trans.description');

        $qb->leftJoin('region_event',
            RegionEntity\Trans\RegionTrans::TABLE,
            'region_trans',
            'region_trans.event = region_event.id AND region_trans.local = :local'
        );

        $qb->leftJoin('contact_region',
            ContactsRegionEntity\Event\ContactsRegionEvent::TABLE,
            'contact_region_event',
            'contact_region_event.id = contact_region.event'
        );

        $qb->leftJoin('contact_region_event',
            ContactsRegionEntity\Call\ContactsRegionCall::TABLE,
            'contact_region_call',
            'contact_region_call.event = contact_region_event.id'.($pickup ? ' AND contact_region_call.pickup = true' : '')
        );

        $qb->addSelect('contact_region_call_trans.name AS call_name')
            ->addGroupBy('contact_region_call_trans.name');
        $qb->addSelect('contact_region_call_trans.description AS call_description')
            ->addGroupBy('contact_region_call_trans.description');

        $qb->leftJoin('contact_region_call',
            ContactsRegionEntity\Call\Trans\ContactsRegionCallTrans::TABLE,
            'contact_region_call_trans',
            'contact_region_call_trans.call = contact_region_call.id AND contact_region_call_trans.local = :local'
        );

        $qb->addSelect("JSON_AGG
		( DISTINCT
				
					JSONB_BUILD_OBJECT
					(
						'call_phone_name', contact_region_call_phone.name,
						'call_phone_number', contact_region_call_phone.phone
					)
					
			) AS calls_phone
		"
        );

        $qb->leftJoin('contact_region_call',
            ContactsRegionEntity\Call\Phone\ContactsRegionCallPhone::TABLE,
            'contact_region_call_phone',
            'contact_region_call_phone.call = contact_region_call.id'
        );

        $qb->addSelect('contact_region_call_info.address AS call_address')
            ->addGroupBy('contact_region_call_info.address');
        $qb->addSelect('contact_region_call_info.email AS call_email')
            ->addGroupBy('contact_region_call_info.email');
        $qb->addSelect('contact_region_call_info.working AS call_working')
            ->addGroupBy('contact_region_call_info.working');
        $qb->addSelect('contact_region_call_info.latitude AS call_latitude')
            ->addGroupBy('contact_region_call_info.latitude');
        $qb->addSelect('contact_region_call_info.longitude AS call_longitude')
            ->addGroupBy('contact_region_call_info.longitude');

        $qb->leftJoin('contact_region_call',
            ContactsRegionEntity\Call\Info\ContactsRegionCallInfo::TABLE,
            'contact_region_call_info',
            'contact_region_call_info.call = contact_region_call.id'
        );

        $qb->addSelect("
			CASE
				WHEN contact_region_call_cover.name IS NOT NULL THEN
					CONCAT ( '/upload/".ContactsRegionEntity\Call\Cover\ContactsRegionCallCover::TABLE."' , '/', contact_region_call_cover.name)
				ELSE NULL
			END AS call_cover_name
		")
            ->addGroupBy('contact_region_call_cover.name');

        $qb->addSelect('contact_region_call_cover.ext AS call_cover_ext')
            ->addGroupBy('contact_region_call_cover.ext');
        $qb->addSelect('contact_region_call_cover.cdn AS call_cover_cdn')
            ->addGroupBy('contact_region_call_cover.cdn');


        $qb->leftJoin('contact_region_call',
            ContactsRegionEntity\Call\Cover\ContactsRegionCallCover::TABLE,
            'contact_region_call_cover',
            'contact_region_call_cover.call = contact_region_call.id'
        );


        $qb->addGroupBy('contact_region_call.sort');
        $qb->addOrderBy('contact_region_call.sort');


        /* Кешируем результат DBAL */
        return $qb
            ->enableCache('contacts-region', 86400)
            ->fetchAllAssociative();


    }

}