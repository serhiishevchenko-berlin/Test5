<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Service\Transactions;
use App\Service\Rates;
use App\Service\Countries;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use DateTimeImmutable;
use App\Entity\Currencies;
use Doctrine\ORM\EntityManagerInterface;

class CommissionsController extends AbstractController
{
    private array $countryEs = [];
    private array $currentTransactions;
    private array $rates;

    private array $counties_number = [];
    private EntityManagerInterface $entityManager;

public function __construct(
    Transactions $transactions,
    Countries $countries,
    Rates $rates,
    EntityManagerInterface $entityManager
)
    {
        $this->currentTransactions = $transactions->getTransactions();
        $this->rates = $rates->getRates();
        $this->entityManager = $entityManager;
        $this->updateCurrensy($countries);
    }

    #[Route('coms', name: 'app_coms')]
    public function getCommissions(): Response
    {
        # calculate commissions
        $message = '';
        if (count($this->counties_number) > 0 ){
            $counties_number = $this->counties_number;
            foreach ($counties_number as $key => $value){
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
        return new Response($message);
    }

    private function updateCurrensy(Countries $countries):void
    {
        foreach ($this->currentTransactions as $key => $value) {
           $result = $countries->getCountry($value["bin"]);
           if($result !== ''){
               $card = json_decode($result, true);
               $this->counties_number[$value["bin"]]["country_id"] = $card["country"]["numeric"];
               $this->counties_number[$value["bin"]]["amount"] = $value["amount"];
               $country = $this->entityManager->getRepository(Currencies::class)->findOneBySomeField($card["country"]["numeric"]);
               if($country === null) {
                   $currency = new Currencies();
                   $currency->setCountryId($card["country"]["numeric"]);
                   $currency->setCountryName($card["country"]["name"]);
                   $currency->setCurrencyName($card["country"]["currency"]);
                   $c_arr = explode(",",'AT,BE,BG,CY,CZ,DE,DK,EE,ES,FI,FR,GR,HR,HU,IE,IT,LT,LU,LV,MT,NL,PO,PT,RO,SE,SI,SK');
                   $status = false;
                   if (in_array($card["country"]["alpha2"], $c_arr, true)) { $status = true; };
                   $currency->setStatus($status);
                   $this->entityManager->persist($currency);
                   $this->entityManager->flush();
               }
           }
        }
        $result = $this->entityManager->getRepository(Currencies::class)->findByExampleField(0);
        if($this->rates["success"]) {
            $date = new DateTimeImmutable();
            foreach ($result as $value){
               $currency = $this->entityManager->getRepository(Currencies::class)->find($value->getId());
               $currency->setRate($this->rates["rates"][$value->getCurrencyName()]);
               $currency->setDateRate($date->setTimestamp($this->rates["timestamp"]));
               $this->entityManager->flush();
            }
        }
    }
}