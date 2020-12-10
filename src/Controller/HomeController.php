<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Class HomeController
 * @package App\Controller
 */
class HomeController extends AbstractController
{

    const XML_ALL_CURRENCIES = 'http://www.cbr.ru/scripts/XML_val.asp?d=0';
    const XML_DAILY_RATE = 'http://www.cbr.ru/scripts/XML_daily.asp';
    const XML_DINAMIC_RATE = 'http://www.cbr.ru/scripts/XML_dynamic.asp';

    const PATH_FILE = '/var/www/russia_bank/public/';

    /**
     * @return Response
     */
    public function index(Request $request): Response
    {
        // get all currencies
        $currencies = $this->getCurrencies(self::XML_ALL_CURRENCIES);

        $dailyInfo = self::XML_DAILY_RATE.'?date_req='.date('d/m/yy');

        // get daily xml data
        $data = $this->getDailyData($dailyInfo, $request);

        if ($data['status'] === false) {

            return $this->render('base.html.twig', [
                'error' => $data['data']
            ]);
        }

        return $this->render('base.html.twig', [
            'data' => $data['data'],
            'currencies' => $currencies['status'] === true ? $currencies['data'] : '',
            'file' => $data['file']
        ]);
    }


    /**
     * get xml data
     * @param string $url
     * @param Request $request
     */
    private function getDailyData(string $url, Request $request) : array
    {
        // get params
        $paramsCurrencies = $request->query->get('currencies');
        $paramsFromDate = $request->query->get('date_from');
        $paramsToDate = $request->query->get('date_to');
        $paramsByPage = $request->query->get('by_page');
        $paramsExport = $request->query->get('export');

        if (isset($paramsFromDate) && !empty($paramsFromDate)) {
            $url = self::XML_DAILY_RATE.'?date_req='.$paramsFromDate;
        }

        if (isset($paramsToDate) && !empty($paramsToDate)) {
            $url = self::XML_DAILY_RATE.'?date_req='.$paramsToDate;
        }

        if (isset($paramsFromDate) && !empty($paramsFromDate) && isset($paramsToDate) && !empty($paramsToDate)) {
            $url = self::XML_DAILY_RATE.'?date_req1='.$paramsFromDate.'&date_req2='.$paramsToDate;
        }

        if (isset($paramsFromDate) && !empty($paramsFromDate) &&
            isset($paramsToDate) && !empty($paramsToDate) &&
            isset($paramsCurrencies) && !empty($paramsCurrencies)
        ) {
            $url = self::XML_DINAMIC_RATE.'?date_req1='.$paramsFromDate.'&date_req2='.$paramsToDate.'&VAL_NM_RQ='.$paramsCurrencies;
        }

        $data = $this->getXMLData($url);
        if ($data['status'] === false) {
            return $data;
        }

        $xmlData = $data['data'];

        // export to json
        $fileToDownload = '';
        if (isset($paramsExport) && !empty($paramsExport)) {
            $json = json_encode($xmlData);
            $filename = 'export.json';

            $fileinfo = new SplFileInfo($filename, self::PATH_FILE, '../public');

            if ($fileinfo->isWritable()) {

                $fileobj = $fileinfo->openFile('a');

                $fileobj->rewind();
                $fileobj->fwrite($json);

                $response = new BinaryFileResponse($fileobj->getFileInfo());
                $response-> headers->set('Content-Type','text/plain');
                $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $filename);

                $fileToDownload = $filename;
            }
        }

        // find currencies
        if (isset($paramsCurrencies) && !empty($paramsCurrencies)) {
            $xmlData = $xmlData->xpath('//*[@ID="'.$paramsCurrencies.'"]');
        }

        $resultData = [];
        foreach ($xmlData as $val) {
            $resultData[] = [
                'id' => $val->attributes()->ID->__toString(),
                'numCode' => $val->children()->NumCode->__toString(),
                'charCode' => $val->children()->CharCode->__toString(),
                'nominal' => $val->children()->Nominal->__toString(),
                'name' => $val->children()->Name->__toString(),
                'value' => $val->children()->Value->__toString(),
            ];
        }

        return ['status' => true, 'data' => $resultData, 'file' => $fileToDownload];
    }

    /**
     * @param string $url
     * @return array
     */
    private function getCurrencies(string $url) : array
    {
        $data = $this->getXMLData($url);

        if ($data['status'] === false) {
            return ['status' => false, 'data' => $data];
        }

        $resultData = [];
        foreach ($data['data'] as $val) {
            $resultData[] = [
                'id' => $val->attributes()->ID->__toString(),
                'name' => $val->children()->Name->__toString(),
            ];
        }

        return ['status' => true, 'data' => $resultData];
    }

    /**
     * @param string $url
     * @return array
     */
    private function getXMLData(string $url) : array
    {
        try {
            $context  = stream_context_create(['http' => ['header' => 'Accept: application/xml']]);
            $xmlContent = @file_get_contents($url, false, $context);
            $xmlData = simplexml_load_string($xmlContent);

            if ($xmlData instanceof \SimpleXMLElement) {
                $nodes = $xmlData->children();

                if (0 === $nodes->count()) {
                    return ['status' => false, 'data' => 'Empty result'];
                }

                return ['status' => true, 'data' => $nodes];
            }

            return ['status' => false, 'data' => 'Error xml data, reload the page'];

        } catch (Exception $e) {
            return ['status' => false, 'data' => $e->getMessage()];
        }
    }
}

