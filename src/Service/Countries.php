<?php

namespace App\Service;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

class Countries
{

    public function __construct(private readonly ContainerBagInterface $params,)
    {

    }

    public function getCountry_test(string $bin): string
    {
        //return '{"number":{},"scheme":"visa","type":"debit","brand":"Visa Classic","country":{"numeric":"208","alpha2":"DK","name":"Denmark","emoji":"🇩🇰","currency":"DKK","latitude":56,"longitude":10},"bank":{"name":"Jyske Bank A/S"}}';
        //return '{"number":{},"scheme":"mastercard","type":"debit","brand":"Debit Mastercard Unembossed","country":{"numeric":"440","alpha2":"LT","name":"Lithuania","emoji":"🇱🇹","currency":"EUR","latitude":56,"longitude":24},"bank":{"name":"Swedbank Ab"}}';
        //return '{"number":{},"scheme":"visa","type":"credit","brand":"Visa Classic","country":{"numeric":"392","alpha2":"JP","name":"Japan","emoji":"🇯🇵","currency":"JPY","latitude":36,"longitude":138},"bank":{"name":"Credit Saison Co., Ltd."}}';
        return '{"number":null,"country":{},"bank":{}}';
    }

    public function getCountry(string $bin_code): string
    {
        $url =  $this->params->get('app.url.country');
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url.$bin_code,
            CURLOPT_HTTPHEADER => array(
                "Content-Type: text/plain"
            ),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET"
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        return $response;

    }

}