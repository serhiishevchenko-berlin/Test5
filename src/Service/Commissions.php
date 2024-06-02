<?php

namespace App\Service;

use App\Entity\Currencies;
use Doctrine\ORM\EntityManagerInterface;
use DateTimeImmutable;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

class Commissions
{
    private array $currentTransactions;
    private array $freshRates;
    private array $countries_number = [];
    public function __construct(private Transactions $transactions,
                                private Countries $countries,
                                private Rates $rates,
                                private EntityManagerInterface $entityManager,
                                private readonly ContainerBagInterface $params
    )
    {
        $this->currentTransactions = $transactions->getTransactions();
        $this->freshRates = $rates->getRates();$rates->getRates();
        $this->updateCurrensy($countries);

    }

    public function calculateCommissions(): string
    {
        $message = '';
        if (count($this->countries_number) > 0 ){
            $countries_number = $this->countries_number;
            foreach ($countries_number as $key => $value){
                $country_id = $value["country_id"];
                $amount = $value["amount"];
                $currency = $this->entityManager->getRepository(Currencies::class)->findOneBySomeField($country_id);
                $rate = $currency->getRate();

                $k = $currency->isStatus() ? 0.01 : 0.02;
                if ($rate > 0) {
                    $amount = $amount / $rate;
                }
                $commission = $amount * $k;
                $message .= '<br>Card number = '.$key.' Commission = '.$commission.'<br>';
            }
        } else {
            $message = 'The server lookup.binlist.net is unavailable! Too many requests.';
        }
        return $message;
    }

    private function updateCurrensy(Countries $countries):void
    {
        foreach ($this->currentTransactions as $key => $value) {
            $result = $countries->getCountry($value["bin"]);
            if($result !== ''){
                $card = json_decode($result, true);
                $this->countries_number[$value["bin"]]["country_id"] = $card["country"]["numeric"];
                $this->countries_number[$value["bin"]]["amount"] = $value["amount"];
                $country = $this->entityManager->getRepository(Currencies::class)->findOneBySomeField($card["country"]["numeric"]);
                if($country === null) {
                    $currency = new Currencies();
                    $currency->setCountryId($card["country"]["numeric"]);
                    $currency->setCountryName($card["country"]["name"]);
                    $currency->setCurrencyName($card["country"]["currency"]);
                    $es_countries = $this->params->get('app.es.country');
                    $status = false;
                    if (in_array($card["country"]["alpha2"], $es_countries, true)) { $status = true; };
                    $currency->setStatus($status);
                    $this->entityManager->persist($currency);
                    $this->entityManager->flush();
                }
            }
        }
        $result = $this->entityManager->getRepository(Currencies::class)->findByExampleField(0);
        if($this->freshRates["success"]) {
            $date = new DateTimeImmutable();
            foreach ($result as $value){
                $currency = $this->entityManager->getRepository(Currencies::class)->find($value->getId());
                $currency->setRate($this->freshRates["rates"][$value->getCurrencyName()]);
                $currency->setDateRate($date->setTimestamp($this->freshRates["timestamp"]));
                $this->entityManager->flush();
            }
        }
    }

}