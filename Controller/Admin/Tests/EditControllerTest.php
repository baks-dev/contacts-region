<?php
/*
 *  Copyright 2022.  Baks.dev <admin@baks.dev>
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *   limitations under the License.
 *
 */

namespace BaksDev\Contacts\Region\Controller\Admin\Tests;

use BaksDev\Contacts\Region\Entity\Call\ContactsRegionCall;
use BaksDev\Contacts\Region\Type\Call\ContactsRegionCallUid;
use BaksDev\Users\User\Tests\TestUserAccount;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

/** @group contacts-region */
#[When(env: 'test')]
final class EditControllerTest extends WebTestCase
{
    private const string URL = '/admin/call/edit/%s';

    private const string ROLE = 'ROLE_CONTACTS_REGION_EDIT';


    private static ?ContactsRegionCallUid $identifier = null;

    public static function setUpBeforeClass(): void
    {
        // Получаем одно из событий Продукта
        $em = self::getContainer()->get(EntityManagerInterface::class);
        self::$identifier = $em->getRepository(ContactsRegionCall::class)->findOneBy([], ['id' => 'DESC'])?->getId();

        $em->clear();
        //$em->close();
    }


    /** Доступ по без роли */
    public function testGuestFiled(): void
    {
        // Получаем одно из событий
        $ContactsRegionCall = self::$identifier;

        if($ContactsRegionCall)
        {
            self::ensureKernelShutdown();
            $client = static::createClient();

            foreach(TestUserAccount::getDevice() as $device)
            {
                $client->setServerParameter('HTTP_USER_AGENT', $device);

                $client->request('GET', sprintf(self::URL, $ContactsRegionCall->getValue()));

                // Full authentication is required to access this resource
                self::assertResponseStatusCodeSame(401);
            }
        }

        self::assertTrue(true);
    }

    /** Доступ по роли */
    public function testRoleSuccessful(): void
    {
        // Получаем одно из событий
        $ContactsRegionCall = self::$identifier;

        if($ContactsRegionCall)
        {
            self::ensureKernelShutdown();
            $client = static::createClient();
            foreach(TestUserAccount::getDevice() as $device)
            {
                $usr = TestUserAccount::getModer(self::ROLE);

                $client->setServerParameter('HTTP_USER_AGENT', $device);
                $client->loginUser($usr, 'user');
                $client->request('GET', sprintf(self::URL, $ContactsRegionCall->getValue()));

                self::assertResponseIsSuccessful();
            }
        }

        self::assertTrue(true);
    }

    // доступ по роли ROLE_ADMIN
    public function testRoleAdminSuccessful(): void
    {
        // Получаем одно из событий
        $ContactsRegionCall = self::$identifier;

        if($ContactsRegionCall)
        {
            self::ensureKernelShutdown();
            $client = static::createClient();

            foreach(TestUserAccount::getDevice() as $device)
            {
                $usr = TestUserAccount::getAdmin();

                $client->setServerParameter('HTTP_USER_AGENT', $device);
                $client->loginUser($usr, 'user');
                $client->request('GET', sprintf(self::URL, $ContactsRegionCall->getValue()));

                self::assertResponseIsSuccessful();
            }
        }

        self::assertTrue(true);
    }

    // доступ по роли ROLE_USER
    public function testRoleUserDeny(): void
    {
        // Получаем одно из событий
        $ContactsRegionCall = self::$identifier;

        if($ContactsRegionCall)
        {
            self::ensureKernelShutdown();
            $client = static::createClient();
            foreach(TestUserAccount::getDevice() as $device)
            {
                $usr = TestUserAccount::getUsr();

                $client->setServerParameter('HTTP_USER_AGENT', $device);
                $client->loginUser($usr, 'user');
                $client->request('GET', sprintf(self::URL, $ContactsRegionCall->getValue()));

                self::assertResponseStatusCodeSame(403);
            }
        }

        self::assertTrue(true);
    }
}
