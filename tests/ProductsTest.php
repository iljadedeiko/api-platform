<?php

/*
 * @copyright C UAB NFQ Technologies
 *
 * This Software is the property of NFQ Technologies
 * and is protected by copyright law – it is NOT Freeware.
 *
 * Any unauthorized use of this software without a valid license key
 * is a violation of the license agreement and will be prosecuted by
 * civil and criminal law.
 *
 * Contact UAB NFQ Technologies:
 * E-mail: info@nfq.lt
 * https://www.nfq.lt
 */

declare(strict_types=1);

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Constant\Token\TestTokenConstant;
use App\Entity\ApiToken;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ProductsTest extends ApiTestCase
{
    use RefreshDatabaseTrait;

    private HttpClientInterface $httpClient;

    private EntityManagerInterface $entityManager;

    public function setUp(): void
    {
        $this->httpClient = self::createClient();
        $this->entityManager = $this->httpClient->getContainer()->get('doctrine')->getManager();

        $user = new User();
        $user->setEmail('info@example.com');
        $user->setPassword('1234567890');
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $apiToken = new ApiToken();
        $apiToken->setToken(TestTokenConstant::API_TOKEN);
        $apiToken->setUser($user);
        $this->entityManager->persist($apiToken);
        $this->entityManager->flush();
    }

    public function testGetCollection(): void
    {
        $response = $this->httpClient->request('GET', '/api/products', [
            'headers' => ['x-api-token' => TestTokenConstant::API_TOKEN]
        ]);

        self::assertResponseIsSuccessful();

        self::assertResponseHeaderSame(
            'content-type', 'application/ld+json; charset=utf-8'
        );

        self::assertJsonContains([
            '@context' => '/api/contexts/Product',
            '@id' => '/api/products',
            '@type' => 'hydra:Collection',
            'hydra:totalItems' => 100,
            'hydra:view' => [
                '@id' => '/api/products?page=1',
                '@type' => 'hydra:PartialCollectionView',
                'hydra:first' => '/api/products?page=1',
                'hydra:last' => '/api/products?page=20',
                'hydra:next' => '/api/products?page=2',
            ],
        ]);

        self::assertCount(5, $response->toArray()['hydra:member']);
    }

    public function testPagination(): void
    {
        $this->httpClient->request('GET', '/api/products?page=2', [
            'headers' => ['x-api-token' => TestTokenConstant::API_TOKEN]
        ]);

        self::assertJsonContains([
            'hydra:view' => [
                '@id' => '/api/products?page=2',
                '@type' => 'hydra:PartialCollectionView',
                'hydra:first' => '/api/products?page=1',
                'hydra:last' => '/api/products?page=20',
                'hydra:previous' => '/api/products?page=1',
                'hydra:next' => '/api/products?page=3',
            ],
        ]);
    }

    public function testCreateProduct(): void
    {
        $this->httpClient->request('POST', '/api/products', [
            'headers' => ['x-api-token' => TestTokenConstant::API_TOKEN],
            'json' => [
                'mpn' => 'Akumuliatorinis suktuvas DF012DSJ',
                'name' => 'Akumuliatorinis suktuvas DF012DSJ',
                'description' => '21 pakopos sukimo momento reguliavimas (0,3 - 2,9Nm)',
                'issueDate' => '2022-10-25',
                'manufacturer' => '/api/manufacturers/1',
            ]
        ]);

        self::assertResponseStatusCodeSame(201);

        self::assertResponseHeaderSame(
            'content-type', 'application/ld+json; charset=utf-8'
        );

        self::assertJsonContains([
            'mpn' => 'Akumuliatorinis suktuvas DF012DSJ',
            'name' => 'Akumuliatorinis suktuvas DF012DSJ',
            'description' => '21 pakopos sukimo momento reguliavimas (0,3 - 2,9Nm)',
            'issueDate' => '2022-10-25T00:00:00+00:00',
        ]);
    }

    public function testUpdateProduct(): void
    {
        $this->httpClient->request('PUT', '/api/products/1', [
            'headers' => ['x-api-token' => TestTokenConstant::API_TOKEN],
            'json' => [
                'description' => 'An updated description',
            ]
        ]);

        self::assertResponseIsSuccessful();
        self::assertJsonContains([
            '@id' => '/api/products/1',
            'description' => 'An updated description',
        ]);
    }

    public function testCreateInvalidProduct(): void
    {
        $this->httpClient->request('POST', '/api/products', [
            'headers' => ['x-api-token' => TestTokenConstant::API_TOKEN],
            'json' => [
                'mpn' => '123',
                'name' => 'test product',
                'description' => 'test description',
                'issueDate' => '1999-10-25',
                'manufacturer' => null,
            ]
        ]);

        self::assertResponseStatusCodeSame(422);
        self::assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        self::assertJsonContains([
            '@context' => '/api/contexts/ConstraintViolationList',
            '@type' => 'ConstraintViolationList',
            'hydra:title' => 'An error occurred',
            'hydra:description' => 'manufacturer: This value should not be null.',
        ]);
    }

    public function testInvalidToken(): void
    {
        $this->httpClient->request('PUT', '/api/products/1', [
            'headers' => ['x-api-token' => 'fake-token'],
            'json' => [
                'description' => 'An updated description',
            ]
        ]);

        self::assertResponseStatusCodeSame(401);
        self::assertJsonContains([
            'message' => 'Invalid credentials.',
        ]);
    }
}
