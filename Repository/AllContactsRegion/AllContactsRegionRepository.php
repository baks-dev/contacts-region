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

namespace BaksDev\Contacts\Region\Repository\AllContactsRegion;


use BaksDev\Contacts\Region\Entity\Call\ContactsRegionCall;
use BaksDev\Contacts\Region\Entity\Call\Cover\ContactsRegionCallCover;
use BaksDev\Contacts\Region\Entity\Call\Trans\ContactsRegionCallTrans;
use BaksDev\Contacts\Region\Entity\ContactsRegion;
use BaksDev\Contacts\Region\Entity\Event\ContactsRegionEvent;
use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Core\Form\Search\SearchDTO;
use BaksDev\Core\Services\Paginator\PaginatorInterface;
use BaksDev\Reference\Region\Entity\Event\RegionEvent;
use BaksDev\Reference\Region\Entity\Region;
use BaksDev\Reference\Region\Entity\Trans\RegionTrans;

final class AllContactsRegionRepository implements AllContactsRegionInterface
{
    private PaginatorInterface $paginator;

    private DBALQueryBuilder $DBALQueryBuilder;

    private ?SearchDTO $search = null;

    public function __construct(
        DBALQueryBuilder $DBALQueryBuilder,
        PaginatorInterface $paginator
    )
    {
        $this->paginator = $paginator;
        $this->DBALQueryBuilder = $DBALQueryBuilder;
    }

    public function search(SearchDTO $search): self
    {
        $this->search = $search;
        return $this;
    }


    /**
     * Метод возвращает массив всех добавленных региональных контактов
     */
    public function findAllPaginator(): PaginatorInterface
    {
        $dbal = $this->DBALQueryBuilder
            ->createQueryBuilder(self::class)
            ->bindLocal();

        $dbal
            ->select('contact.id')
            ->addSelect('contact.event')
            ->from(ContactsRegion::class, 'contact');

        $dbal
            ->addSelect('event.sort AS event_sort')
            ->join(
                'contact',
                ContactsRegionEvent::class,
                'event',
                'event.id = contact.event'
            );

        $dbal->leftJoin(
            'contact',
            Region::class,
            'region',
            'region.id = contact.id'
        );

        $dbal->join(
            'region',
            RegionEvent::class,
            'region_event',
            'region_event.id = region.event'
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

        $dbal
            ->addSelect('call.id AS call_id')
            ->addSelect('call.sort AS call_sort')
            ->addSelect('call.pickup AS call_pickup')
            ->addSelect('call.stock AS call_stock')
            ->addSelect('call.active AS call_active')
            ->leftJoin(
                'event',
                ContactsRegionCall::class,
                'call',
                'call.event = event.id'
            );

        $dbal
            ->addSelect('call_trans.name AS call_name')
            ->addSelect('call_trans.description AS call_description')
            ->leftJoin(
                'call',
                ContactsRegionCallTrans::class,
                'call_trans',
                'call_trans.call = call.id AND call_trans.local = :local'
            );

        // Обложка
        $dbal
            ->addSelect('cover.ext AS call_cover_ext')
            ->addSelect('cover.cdn AS call_cover_cdn')
            ->addSelect(
                "
			CASE
			   WHEN cover.name IS NOT NULL 
			   THEN CONCAT ( '/upload/".$dbal->table(ContactsRegionCallCover::class)."' , '/', cover.name)
			   ELSE NULL
			END AS call_cover_name
		"
            )
            ->leftJoin(
                'event',
                ContactsRegionCallCover::class,
                'cover',
                'cover.call = call.id'
            );

        // Поиск
        if($this->search?->getQuery())
        {

            $dbal
                ->createSearchQueryBuilder($this->search)
                ->addSearchLike('call_trans.name')
                ->addSearchLike('region_trans.name');
        }

        // $dbal->orderBy('event.sort, contact_trans.name, call.sort');
        $dbal->orderBy('event.sort, call.sort');

        return $this->paginator->fetchAllAssociative($dbal);


    }

}
