<?php

namespace App\Service;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
class Transactions
{

    public function __construct(private readonly ContainerBagInterface $params,)
    {

    }

    public function getTransactions(): array
    {
        $url =  $this->params->get('app.url.trunsaction');
        $content = file_get_contents($url);
        $result = explode("\n",$content);
        return   json_decode('['.implode(",",$result).']', true);
    }

}