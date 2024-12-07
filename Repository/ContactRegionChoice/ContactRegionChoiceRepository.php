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

namespace BaksDev\Contacts\Region\Repository\ContactRegionChoice;

use BaksDev\Contacts\Region\Entity\ContactsRegion;
use BaksDev\Core\Doctrine\ORMQueryBuilder;
use BaksDev\Core\Type\Locale\Locale;
use BaksDev\Reference\Region\Entity\Invariable\RegionInvariable;
use BaksDev\Reference\Region\Entity\Region;
use BaksDev\Reference\Region\Entity\Trans\RegionTrans;
use BaksDev\Reference\Region\Type\Id\RegionUid;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ContactRegionChoiceRepository implements ContactRegionChoiceInterface
{
    private TranslatorInterface $translator;
    private ORMQueryBuilder $ORMQueryBuilder;


    public function __construct(
        ORMQueryBuilder $ORMQueryBuilder,
        TranslatorInterface $translator
    )
    {
        $this->translator = $translator;
        $this->ORMQueryBuilder = $ORMQueryBuilder;
    }


    public function getRegionChoice()
    {
        $qb = $this->ORMQueryBuilder->createQueryBuilder(self::class);

        $select = sprintf('new %s(region.id, trans.name)', RegionUid::class);
        $qb->select($select);

        $qb->from(ContactsRegion::class, 'contact');


        $qb->join(Region::class,
            'region',
            'WITH',
            'region.id = contact.id'
        );


        $qb->join(RegionInvariable::class,
            'invariable',
            'WITH',
            'invariable.main = region.id AND invariable.active = true'
        );


        $qb
            ->leftJoin(RegionTrans::class,
                'trans',
                'WITH',
                'trans.event = region.event AND trans.local = :local'
            )
            ->setParameter(
                'local',
                new Locale($this->translator->getLocale()),
                Locale::TYPE
            );

        $qb->orderBy('invariable.sort');
        $qb->addOrderBy('trans.name');

        $qb->setMaxResults(1);


        /* Кешируем результат ORM */
        return $qb->enableCache('contacts-region', 86400)->getResult();


    }

}