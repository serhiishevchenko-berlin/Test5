<?php
namespace App\Controller;

use App\Service\Commissions;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;

class CommissionsController extends AbstractController
{
    #[Route('commissions', name: 'app_commissions')]
    public function getCommissions(Commissions $commissions): Response
    {

        $result = $commissions->calculateCommissions();
        return new Response($result);
    }


}