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

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Constant\Entity\ManufacturerSerializationGroupConstants;
use App\Constant\Entity\ProductSerializationGroupConstants;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use DateTimeInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/** A manufacturer */
#[ApiResource(
    operations: [
        new GetCollection(),
        new Post(),
        new Get(),
        new Put(),
        new Patch(),
        new Delete()
    ],
    normalizationContext: [
        'groups' => ManufacturerSerializationGroupConstants::API_READ_GROUP
    ],
    paginationItemsPerPage: 5,
)]

#[ORM\Entity]
class Manufacturer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups([ManufacturerSerializationGroupConstants::API_READ_GROUP])]
    private ?int $id = null;

    #[ORM\Column]
    #[Assert\NotBlank]
    #[Groups([
        ManufacturerSerializationGroupConstants::API_READ_GROUP,
        ProductSerializationGroupConstants::API_READ_GROUP,
    ])]
    private string $name = '';

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank]
    #[Groups([ManufacturerSerializationGroupConstants::API_READ_GROUP])]
    private string $description = '';

    #[ORM\Column(length: 3)]
    #[Assert\NotBlank]
    #[Groups([ManufacturerSerializationGroupConstants::API_READ_GROUP])]
    private string $countryCode = '';

    #[ORM\Column(type: 'datetime')]
    #[Assert\NotNull]
    #[Groups([ManufacturerSerializationGroupConstants::API_READ_GROUP])]
    private ?DateTimeInterface $listedDate = null;

    /** @var Product[] */
    #[ORM\OneToMany(
        mappedBy: 'manufacturer',
        targetEntity: Product::class,
        cascade: ['persist', 'remove'])
    ]
    private iterable $products;

    public function __construct()
    {
        $this->products = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): Manufacturer
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): Manufacturer
    {
        $this->description = $description;

        return $this;
    }

    public function getCountryCode(): string
    {
        return $this->countryCode;
    }

    public function setCountryCode(string $countryCode): Manufacturer
    {
        $this->countryCode = $countryCode;

        return $this;
    }

    public function getListedDate(): ?DateTimeInterface
    {
        return $this->listedDate;
    }

    public function setListedDate(?DateTimeInterface $listedDate): Manufacturer
    {
        $this->listedDate = $listedDate;

        return $this;
    }

    public function getProducts(): iterable|ArrayCollection
    {
        return $this->products;
    }
}
