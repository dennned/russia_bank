<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class HomeController
 * @package App\Controller
 */
class HomeController extends AbstractController
{

    const XML_ALL_CURRENCIES = 'http://www.cbr.ru/scripts/XML_val.asp?d=0';
    const XML_DAILY_RATE = 'http://www.cbr.ru/scripts/XML_daily.asp';

    /**
     * @return Response
     */
    public function index(): Response
    {
        $dailyInfo = self::XML_DAILY_RATE.'?date_req='.date('d/m/yy');

        dump($dailyInfo);

        return $this->render('base.html.twig', [
            'controller_name' => 'homeAction',
        ]);
    }
}

