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


use BaksDev\Contacts\Region\Entity\Call\ContactsRegionCall;
use BaksDev\Contacts\Region\Entity\Call\Cover\ContactsRegionCallCover;
use BaksDev\Contacts\Region\Entity\Call\Info\ContactsRegionCallInfo;
use BaksDev\Contacts\Region\Entity\Call\Phone\ContactsRegionCallPhone;
use BaksDev\Contacts\Region\Entity\Call\Trans\ContactsRegionCallTrans;
use BaksDev\Contacts\Region\Entity\ContactsRegion;
use BaksDev\Contacts\Region\Entity\Event\ContactsRegionEvent;
use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Core\Type\Locale\Locale;
use BaksDev\Reference\Region\Entity\Event\RegionEvent;
use BaksDev\Reference\Region\Entity\Region;
use BaksDev\Reference\Region\Entity\Trans\RegionTrans;
use BaksDev\Reference\Region\Type\Id\RegionUid;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ContactCallByRegionRepository implements ContactCallByRegionInterface
{
    private DBALQueryBuilder $DBALQueryBuilder;

    public function __construct(
        DBALQueryBuilder $DBALQueryBuilder,
    )
    {
        $this->DBALQueryBuilder = $DBALQueryBuilder;
    }


    public function fetchContactCallByRegionAssociative(?RegionUid $region, bool $pickup = false): ?array
    {
        $dbal = $this->DBALQueryBuilder
            ->createQueryBuilder(self::class)
            ->bindLocal();


        $dbal->from(ContactsRegion::class, 'contact_region');

        if($region)
        {
            $dbal->where('contact_region.id = :region');
            $dbal->setParameter('region', $region, RegionUid::TYPE);
        }

        $dbal->leftJoin(
            'contact_region',
            Region::class,
            'region',
            'region.id = contact_region.id'
        );

        $dbal
            ->join(
                'region',
                RegionEvent::class,
                'region_event',
                'region_event.id = region.event AND region_event.active = true'
            );

        $dbal
            ->addSelect('region_trans.name AS region_name')
            ->addSelect('region_trans.description AS region_description')
            ->leftJoin(
                'region_event',
                RegionTrans::class,
                'region_trans',
                'region_trans.event = region_event.id AND region_trans.local = :local'
            );

        $dbal->leftJoin(
            'contact_region',
            ContactsRegionCall::class,
            'contact_region_call',
            'contact_region_call.event = contact_region.event AND contact_region_call.active = true '.($pickup ? ' AND contact_region_call.pickup = true' : '')
        );

        $dbal
            ->addSelect('contact_region_call_trans.name AS call_name')
            ->addSelect('contact_region_call_trans.description AS call_description')
            ->leftJoin('contact_region_call',
                ContactsRegionCallTrans::class,
                'contact_region_call_trans',
                'contact_region_call_trans.call = contact_region_call.id AND contact_region_call_trans.local = :local'
            );

        $dbal->addSelect("JSON_AGG
		( DISTINCT
				
					JSONB_BUILD_OBJECT
					(
						'call_phone_name', contact_region_call_phone.name,
						'call_phone_number', contact_region_call_phone.phone
					)
					
			) AS calls_phone
		"
        );

        $dbal->leftJoin(
            'contact_region_call',
            ContactsRegionCallPhone::class,
            'contact_region_call_phone',
            'contact_region_call_phone.call = contact_region_call.id'
        );

        $dbal
            ->addSelect('contact_region_call_info.address AS call_address')
            ->addSelect('contact_region_call_info.email AS call_email')
            ->addSelect('contact_region_call_info.working AS call_working')
            ->addSelect('contact_region_call_info.latitude AS call_latitude')
            ->addSelect('contact_region_call_info.longitude AS call_longitude')
            ->leftJoin('contact_region_call',
                ContactsRegionCallInfo::class,
                'contact_region_call_info',
                'contact_region_call_info.call = contact_region_call.id'
            );

        $dbal->addSelect("
			CASE
				WHEN contact_region_call_cover.name IS NOT NULL 
				THEN CONCAT ( '/upload/".$dbal->table(ContactsRegionCallCover::class)."' , '/', contact_region_call_cover.name)
				ELSE NULL
			END AS call_cover_name
		")
            ->addSelect('contact_region_call_cover.ext AS call_cover_ext')
            ->addSelect('contact_region_call_cover.cdn AS call_cover_cdn')
            ->leftJoin(
                'contact_region_call',
                ContactsRegionCallCover::class,
                'contact_region_call_cover',
                'contact_region_call_cover.call = contact_region_call.id'
            );


        $dbal->addGroupBy('contact_region_call.sort');
        $dbal->addOrderBy('contact_region_call.sort');

        $dbal->allGroupByExclude();

        /* Кешируем результат DBAL */
        return $dbal
            ->enableCache('contacts-region', 86400)
            ->fetchAllAssociative();


    }

}