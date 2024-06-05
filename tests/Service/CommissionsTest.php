<?php
namespace App\Tests\Service;

use App\Entity\Currencies;
use App\Service\Commissions;
use App\Service\Countries;
use App\Service\Rates;
use App\Service\Transactions;
use App\Repository\CurrenciesRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

class CommissionsTest extends TestCase
{
    public function testCalculateCommissions(): void
    {
        // Mock dependencies
        $transactionsMock = $this->createMock(Transactions::class);
        $transactionsMock->method('getTransactions')
            ->willReturn([
                ['bin' => '45717360', 'amount' => 100.00, 'currency' => 'EUR'],
                ['bin' => '516793', 'amount' => 50.00, 'currency' => 'USD'],
                ['bin' => '45417360', 'amount' => 10000.00, 'currency' => 'JPY'],
                ['bin' => '41417360', 'amount' => 130.00, 'currency' => 'USD'],
                ['bin' => '4745030', 'amount' => 2000.00, 'currency' => 'GBP'],
            ]);

        $countriesMock = $this->createMock(Countries::class);
        $countriesMock->method('getCountry')
            ->willReturnCallback(function ($bin) {
                $countryData = [
                    '45717360' => '{"country":{"numeric":"208","alpha2":"DK","name":"Spain","currency":"DKK"}}',
                    '516793' => '{"country":{"numeric":"440","alpha2":"LT","name":"Lithuania","currency":"EUR"}}',
                ];
                return $countryData[$bin] ?? '';
            });

        $ratesMock = $this->createMock(Rates::class);
        $ratesMock->method('getRates')
            ->willReturn([
                'success' => true,
                'timestamp' => time(),
                'rates' => [
                    'EUR' => 1,
                    'USD' => 1.083882,
                    'DKK' => 7.458842,
                    'JPY' => 169.945619
                ],
            ]);

        $entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $entityManagerMock->method('getRepository')
            ->willReturnCallback(function ($class) {
                $repositoryMock = $this->createMock(CurrenciesRepository::class);
                $repositoryMock->method('findOneBySomeField')
                    ->willReturnCallback(function ($countryId) use ($class) {
                        if ($class === Currencies::class) {
                            $currency = new Currencies();
                            $currency->setCountryId($countryId);
                            $status = true;
                            $rate = 1;
                            if($countryId === 392) { $rate = 169.945619; $status = false; }
                            if($countryId === 208) { $rate = 7.458842;}
                            $currency->setRate($rate);
                            $currency->setStatus($status);
                            return $currency;
                        }
                        return null;
                    });
                return $repositoryMock;
            });

        $containerBagMock = $this->createMock(ContainerBagInterface::class);
        $containerBagMock->method('get')->with('app.es.country')->willReturn(['AT','BE','BG','CY','CZ','DE','DK','EE','ES','FI','FR','GR','HR','HU','IE','IT','LT','LU','LV','MT','NL','PO','PT','RO','SE','SI','SK']
        );

        // Create the Commissions service and test the calculateCommissions method
        $commissions = new Commissions(
            $transactionsMock,
            $countriesMock,
            $ratesMock,
            $entityManagerMock,
            $containerBagMock
        );

        $message = $commissions->calculateCommissions();
        $this->assertStringContainsString('<br>Card number = 45717360 Commission = 1<br>', $message);
        $this->assertStringContainsString('<br>Card number = 516793 Commission = 0.5<br>', $message);
    }

    public function testCalculateCommissionsWithEmptyCountry(): void
    {
        // Mock dependencies
        $transactionsMock = $this->createMock(Transactions::class);
        $transactionsMock->method('getTransactions')
            ->willReturn([
                ['bin' => '45717360', 'amount' => 100.00, 'currency' => 'EUR'],
                ['bin' => '516793', 'amount' => 50.00, 'currency' => 'USD'],
                ['bin' => '45417360', 'amount' => 10000.00, 'currency' => 'JPY'],
                ['bin' => '41417360', 'amount' => 130.00, 'currency' => 'USD'],
                ['bin' => '4745030', 'amount' => 2000.00, 'currency' => 'GBP'],
            ]);

        $countriesMock = $this->createMock(Countries::class);
        $countriesMock->method('getCountry')
            ->willReturnCallback(function ($bin) {
                $countryData = [];
                return $countryData[$bin] ?? '';
            });

        $ratesMock = $this->createMock(Rates::class);
        $ratesMock->method('getRates')
            ->willReturn([
                'success' => true,
                'timestamp' => time(),
                'rates' => [
                    'EUR' => 1,
                    'USD' => 1.083882,
                    'DKK' => 7.458842,
                    'JPY' => 169.945619
                ],
            ]);

        $entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $entityManagerMock->method('getRepository')
            ->willReturnCallback(function ($class) {
                $repositoryMock = $this->createMock(CurrenciesRepository::class);
                $repositoryMock->method('findOneBySomeField')
                    ->willReturnCallback(function ($countryId) use ($class) {
                        if ($class === Currencies::class) {
                            $currency = new Currencies();
                            $currency->setCountryId($countryId);
                            $status = true;
                            $rate = 1;
                            //echo '<br> countryId = ',$countryId;
                            if($countryId === 392) { $rate = 169.945619; $status = false; }
                            if($countryId === 208) { $rate = 7.458842;}
                            $currency->setRate($rate);
                            $currency->setStatus($status);
                            return $currency;
                        }
                        return null;
                    });
                return $repositoryMock;
            });

        $containerBagMock = $this->createMock(ContainerBagInterface::class);
        $containerBagMock->method('get')->with('app.es.country')->willReturn(['AT','BE','BG','CY','CZ','DE','DK','EE','ES','FI','FR','GR','HR','HU','IE','IT','LT','LU','LV','MT','NL','PO','PT','RO','SE','SI','SK']
        );

        // Create the Commissions service and test the calculateCommissions method
        $commissions = new Commissions(
            $transactionsMock,
            $countriesMock,
            $ratesMock,
            $entityManagerMock,
            $containerBagMock
        );

        $message = $commissions->calculateCommissions();
        $this->assertStringContainsString('The server lookup.binlist.net is unavailable! Too many requests.', $message);
    }
}