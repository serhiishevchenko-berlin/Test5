<?php

namespace App\Entity;

use App\Repository\CurrenciesRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CurrenciesRepository::class)]
class Currencies
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    #[UniqueEntity('country_id')]
    private ?int $country_id = null;

    #[ORM\Column(length: 50)]
    private ?string $country_name = null;

    #[ORM\Column(length: 3)]
    private ?string $currency_name = null;

    #[ORM\Column(nullable: true)]
    private ?float $rate = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $date_rate = null;

    #[ORM\Column(nullable: true)]
    private ?bool $status = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCountryId(): ?int
    {
        return $this->country_id;
    }

    public function setCountryId(int $country_id): static
    {
        $this->country_id = $country_id;

        return $this;
    }

    public function getCountryName(): ?string
    {
        return $this->country_name;
    }

    public function setCountryName(string $country_name): static
    {
        $this->country_name = $country_name;

        return $this;
    }

    public function getCurrencyName(): ?string
    {
        return $this->currency_name;
    }

    public function setCurrencyName(string $currency_name): static
    {
        $this->currency_name = $currency_name;

        return $this;
    }

    public function getRate(): ?float
    {
        return $this->rate;
    }

    public function setRate(?float $rate): static
    {
        $this->rate = $rate;

        return $this;
    }

    public function getDateRate(): ?\DateTimeInterface
    {
        return $this->date_rate;
    }

    public function setDateRate(?\DateTimeInterface $date_rate): static
    {
        $this->date_rate = $date_rate;

        return $this;
    }

    public function isStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(?bool $status): static
    {
        $this->status = $status;

        return $this;
    }
}
