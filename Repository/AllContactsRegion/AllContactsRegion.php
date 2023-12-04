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

use BaksDev\Contacts\Region\Entity as ContactsRegionEntity;
use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Core\Form\Search\SearchDTO;
use BaksDev\Core\Services\Paginator\PaginatorInterface;
use BaksDev\Core\Services\Switcher\SwitcherInterface;
use BaksDev\Core\Type\Locale\Locale;
use BaksDev\Reference\Region\Entity as RegionEntity;
use Symfony\Contracts\Translation\TranslatorInterface;

final class AllContactsRegion implements AllContactsRegionInterface
{

    private TranslatorInterface $translator;

    private SwitcherInterface $switcher;

    private PaginatorInterface $paginator;

    private mixed $trans;
    private DBALQueryBuilder $DBALQueryBuilder;

    public function __construct(
       DBALQueryBuilder $DBALQueryBuilder,
        TranslatorInterface $translator,
        SwitcherInterface   $switcher,
        PaginatorInterface  $paginator
    )
    {
        $this->translator = $translator;
        $this->switcher = $switcher;
        $this->paginator = $paginator;
        $this->DBALQueryBuilder = $DBALQueryBuilder;
    }

    /**
     * Метод возвращает массив всех добавленных региональных контактов
     */
    public function fetchAllContactsRegionAssociative(SearchDTO $search): PaginatorInterface
    {
        $qb = $this->DBALQueryBuilder->createQueryBuilder(self::class);

        $locale = new Locale($this->translator->getLocale());
        $qb->setParameter('local', $locale, Locale::TYPE);

        $qb->select('contact.id');
        $qb->addSelect('contact.event');

        $qb->addSelect('event.sort AS event_sort');

        $qb->from(ContactsRegionEntity\ContactsRegion::TABLE, 'contact');
        $qb->join(
            'contact',
            ContactsRegionEntity\Event\ContactsRegionEvent::TABLE,
            'event',
            'event.id = contact.event'
        );

        $qb->join('contact', RegionEntity\Region::TABLE, 'region', 'region.id = contact.id');

        $qb->join(
            'region',
            RegionEntity\Event\RegionEvent::TABLE,
            'region_event',
            'region_event.id = region.event'
        );

        $qb->addSelect('region_trans.name AS region_name');
        $qb->addSelect('region_trans.description AS region_description');
        $qb->leftJoin(
            'region_event',
            RegionEntity\Trans\RegionTrans::TABLE,
            'region_trans',
            'region_trans.event = region_event.id AND region_trans.local = :local'
        );

        $qb->addSelect('call.id AS call_id');
        $qb->addSelect('call.sort AS call_sort');
        $qb->addSelect('call.pickup AS call_pickup');
        $qb->addSelect('call.stock AS call_stock');
        $qb->leftJoin('event', ContactsRegionEntity\Call\ContactsRegionCall::TABLE, 'call', 'call.event = event.id');

        $qb->addSelect('call_trans.name AS call_name');
        $qb->addSelect('call_trans.description AS call_description');


        $qb->leftJoin(
            'call',
            ContactsRegionEntity\Call\Trans\ContactsRegionCallTrans::TABLE,
            'call_trans',
            'call_trans.call = call.id AND call_trans.local = :local'
        );

        // Обложка
        $qb->addSelect('cover.ext AS call_cover_ext');
        $qb->addSelect('cover.cdn AS call_cover_cdn');

        $qb->addSelect(
            "
			CASE
			   WHEN cover.name IS NOT NULL THEN
					CONCAT ( '/upload/" . ContactsRegionEntity\Call\Cover\ContactsRegionCallCover::TABLE . "' , '/', cover.name)
			   ELSE NULL
			END AS call_cover_name
		"
        );

        $qb->leftJoin(
            'event',
            ContactsRegionEntity\Call\Cover\ContactsRegionCallCover::TABLE,
            'cover',
            'cover.call = call.id'
        );

        // Поиск
        if ($search->getQuery())
        {

            $qb
                ->createSearchQueryBuilder($search)
                ->addSearchLike('call_trans.name')
                ->addSearchLike('region_trans.name');
        }

        // $qb->orderBy('event.sort, contact_trans.name, call.sort');
        $qb->orderBy('event.sort, call.sort');

        return $this->paginator->fetchAllAssociative($qb);


    }

}
