<?php

namespace AppBundle\Controller\Output;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\Studies;
use AppBundle\Entity\Production;
use AppBundle\Entity\Product;
use AppBundle\Entity\StudyEquipments;
use AppBundle\Entity\StudEqpPrm;
use AppBundle\Entity\Post;
use AppBundle\Entity\DimaResults;
use AppBundle\Entity\Unit;
use AppBundle\Entity\EconomicResults;
use AppBundle\Entity\StudEquipprofile;
use AppBundle\Cryosoft\CheckControlService;
use AppBundle\Cryosoft\UnitsConverterService as UnitConvert;
use AppBundle\Cryosoft\EquipmentsService;
use AppBundle\Cryosoft\DimaResultsService;
use AppBundle\Cryosoft\EconomicResultsService;
use AppBundle\Cryosoft\StudyService;
use AppBundle\Cryosoft\SizingResultService;
use AppBundle\Cryosoft\ProductChartService;

class ProductChartController extends Controller 
{

    public function __construct(CheckControlService $check, UnitConvert $units, Session $session, TokenStorageInterface $tokenStorage, EquipmentsService $equipments, DimaResultsService $dima, EconomicResultsService $ecomicResultService, StudyService $studyService, SizingResultService $sizingResultService, ProductChartService $productChartService)
    {
        $this->user = $tokenStorage->getToken()->getUser();
        $this->_check = $check;
        $this->_unit = $units;
        $this->_equip = $equipments;
        $this->_dima = $dima;
        $this->_eco = $ecomicResultService;
        $this->_study = $studyService;
        $this->_sizing = $sizingResultService;
        $this->_productChart = $productChartService;
    }

    /**
    * @Route("/head-exchange", name="head-exchange")
    */
    public function headExchangeAction(Request $request)
    {
        $session = $this->get("session");
        $doc = $this->getDoctrine();
        // check user login
        $user = $this->getUser();
        if ($user == NULL) {
            return $this->redirectToRoute("login");
        }
        // check idStudy already exists
        $idStudy = $session->get("idStudy");
        if ($idStudy == null || $idStudy==0 || $idStudy == "") {
            return $this->redirectToRoute("load-study");
        }

        $idStudy = 26;
        $idUser = $user->getIdUser();
        $idProd = $session->get("idProd");
        $loadEquipment = $session->get("loadEquipment");
        
        $checkControl = $this->_check->isCheckControl($idUser, $idStudy, $loadEquipment, $idProd);
        //check control
        /*if($checkControl == false){
            return $this->redirectToRoute("checkcontrol");
        }*/

        // get object Study
        // $idStudy = 3;
        $objStudy = $this->getDoctrine()->getRepository(Studies::class)->find($idStudy);
        $calculationMode = $objStudy->getCalculationMode();

        $data = array();
        $idStudyEquipments = 49;

        $studyEquipment = $this->getDoctrine()->getRepository(StudyEquipments::class)->findOneBy(["idStudyEquipments"=>$idStudyEquipments]);

        $sEquipName = $this->_equip->getSpecificEquipName($studyEquipment->getidStudyEquipments());
        $equipment = $studyEquipment->getIdEquip();

        $listRecordPos = $this->_productChart->getlistRecodPos($idStudyEquipments);

        $nbSteps = $this->_productChart->getNbSteps($idStudy);
        $nbSample = $nbSteps->getNbSteps(); 
        $nbRecord = count($listRecordPos);

        $lfTS = $listRecordPos[$nbRecord - 1]->getRecordTime();

        $lfStep = $listRecordPos[1]->getRecordTime() - $listRecordPos[0]->getRecordTime();
        $lEchantillon = $this->_productChart->calculateEchantillon($nbSample, $nbRecord, $lfTS, $lfStep);

        $point2DSeriesContainer = array();

        foreach ($lEchantillon as $key => $value) {
            $recordPos = $listRecordPos[$key];

            $item["x"] = $this->_unit->unitConvert(Post::TYPE_UNIT_TIME, $recordPos->getRecordTime(), 1);
            $item["y"] = $this->_unit->unitConvert(Post::TYPE_UNIT_ENTHALPY, $recordPos->getAverageEnthVar(), 3);
            $point2DSeriesContainer[] = $item;
        }

        $label = "Enthalpy";
        $xLabel = "(" . $this->_unit->enthalpySymbol() . ")";
        $yLabel = "(" . $this->_unit->timeSymbol() . ")";

        $dataChart = [
            "title" => $sEquipName,
            "label" => $label,
            "xLabel" => $xLabel,
            "yLabel" => $yLabel,
            "data" => $point2DSeriesContainer
        ]; 


        $dataChart = json_encode($dataChart);  


        $data = [
            "studyEquipment" => $studyEquipment,
            "sEquipName" => $sEquipName,
            "equipment" => $equipment,
            "dataChart" => $dataChart
        ];
        
        $urlRender = "output/product_chart/head-exchange.html.twig";
        
        return $this->render($urlRender, $data);
    }

    

}