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

namespace App\Entity;

use ApiPlatform\Doctrine\Common\Filter\SearchFilterInterface;
use ApiPlatform\Doctrine\Odm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Constant\Entity\ProductSerializationGroupConstants;
use Doctrine\ORM\Mapping as ORM;
use DateTimeInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/** A Product */
#[ApiResource(
        operations: [
            new Get(),
            new Put(
                security: "is_granted('ROLE_USER') and object.getOwner() == user",
                securityMessage: 'A Product can only be updated by the owner'
            ),
            new GetCollection(),
            new Post(security: "is_granted('ROLE_ADMIN')")
        ],
        normalizationContext: [
            'groups' => ProductSerializationGroupConstants::API_READ_GROUP,
        ],
        denormalizationContext: [
            'groups' => ProductSerializationGroupConstants::API_WRITE_GROUP,
        ],
        paginationItemsPerPage: 5,
    ),
    ApiFilter(
        SearchFilter::class,
        properties: [
            'name' => SearchFilterInterface::STRATEGY_PARTIAL,
            'description' => SearchFilterInterface::STRATEGY_PARTIAL,
            'manufacturer.countryCode' => SearchFilterInterface::STRATEGY_EXACT,
            'manufacturer.id' => SearchFilterInterface::STRATEGY_EXACT,
        ]
    ),
    ApiFilter(
        OrderFilter::class,
        properties: ['issueData']
    )
]
#[ApiResource(
    uriTemplate: '/manufacturers/{id}/products',
    operations: [new GetCollection()],
    uriVariables: [
        'id' => new Link(
            fromProperty: 'products',
            fromClass: Manufacturer::class,
        ),
    ],
)]

#[ORM\Entity]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column]
    #[Assert\NotNull]
    #[Groups([
        ProductSerializationGroupConstants::API_READ_GROUP,
        ProductSerializationGroupConstants::API_WRITE_GROUP,
    ])]
    private ?string $mpn = null;

    #[ORM\Column]
    #[Assert\NotBlank]
    #[Groups([
        ProductSerializationGroupConstants::API_READ_GROUP,
        ProductSerializationGroupConstants::API_WRITE_GROUP,
    ])]
    private string $name = '';

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank]
    #[Groups([
        ProductSerializationGroupConstants::API_READ_GROUP,
        ProductSerializationGroupConstants::API_WRITE_GROUP,
    ])]
    private string $description = '';

    #[ORM\Column(type: 'datetime')]
    #[Assert\NotNull]
    #[Groups([
        ProductSerializationGroupConstants::API_READ_GROUP,
        ProductSerializationGroupConstants::API_WRITE_GROUP,
    ])]
    private ?DateTimeInterface $issueDate = null;

    #[ORM\ManyToOne(targetEntity: Manufacturer::class, inversedBy: 'products')]
    #[Assert\NotNull]
    #[Groups([
        ProductSerializationGroupConstants::API_READ_GROUP,
        ProductSerializationGroupConstants::API_WRITE_GROUP,
    ])]
    private ?Manufacturer $manufacturer = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[Groups([
        ProductSerializationGroupConstants::API_READ_GROUP,
        ProductSerializationGroupConstants::API_WRITE_GROUP,
    ])]
    private ?User $owner = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMpn(): ?string
    {
        return $this->mpn;
    }

    public function setMpn(?string $mpn): Product
    {
        $this->mpn = $mpn;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): Product
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): Product
    {
        $this->description = $description;

        return $this;
    }

    public function getIssueDate(): ?DateTimeInterface
    {
        return $this->issueDate;
    }

    public function setIssueDate(?DateTimeInterface $issueDate): Product
    {
        $this->issueDate = $issueDate;

        return $this;
    }

    public function getManufacturer(): ?Manufacturer
    {
        return $this->manufacturer;
    }

    public function setManufacturer(?Manufacturer $manufacturer): Product
    {
        $this->manufacturer = $manufacturer;

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): self
    {
        $this->owner = $owner;

        return $this;
    }
}
