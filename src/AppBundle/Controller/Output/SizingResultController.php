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
use AppBundle\Cryosoft\KernelCalculateService;
use AppBundle\Cryosoft\SizingResultService;

class SizingResultController extends Controller 
{

    public function __construct(CheckControlService $check, UnitConvert $units, Session $session, TokenStorageInterface $tokenStorage, EquipmentsService $equipments, DimaResultsService $dima, EconomicResultsService $ecomicResultService, StudyService $studyService, KernelCalculateService $kernel, SizingResultService $sizingResultService)
    {
        $this->user = $tokenStorage->getToken()->getUser();
        $this->_check = $check;
        $this->_unit = $units;
        $this->_equip = $equipments;
        $this->_dima = $dima;
        $this->_eco = $ecomicResultService;
        $this->_study = $studyService;
        $this->_kernel = $kernel;
        $this->_sizing = $sizingResultService;

        /*$user = $this->user;
        if ($user == NULL) {
            return $this->redirectToRoute("login");
        }

        $idProd = $session->get("idProd");
        $loadEquipment = $session->get("loadEquipment");
        $idProd = 0;
        $loadEquipment = 0;
        $checkControl = $check->isCheckControl($idUser, $idStudy, $loadEquipment, $idProd);
        if($checkControl == false){
            return $this->redirectToRoute("checkcontrol");
        }*/
    }

    /**
    * @Route("/out-sizing-result", name="out-sizing-result")
    */
    public function outSizingResultAction(Request $request)
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
        $dimaResults = null;
        $massUnit = 37;
        $lfcoef = $this->_unit->unitConvert(Post::TYPE_UNIT_MASS_PER_UNIT, 1.0);

        $studyEquipments = $this->getDoctrine()->getRepository(StudyEquipments::class)->findBy(["idStudy"=>$objStudy->getIdStudy()],["idStudyEquipments"=>"ASC"]);

        $arrStudyEquipment = array();

        $listOfSelectedEquipments =  array();
        $listOfAvailableEquipments = array(); 

        $selectTR = ($request->query->get("selectTR") != "") ? $request->query->get("selectTR") : 1;

        if(!empty($studyEquipments)){

            if($calculationMode == 1){
                //get list equipment value
                foreach ($studyEquipments as $row) {
                    $idStudyEquipments = $row->getidStudyEquipments();
                    $equipment = $row->getIdEquip();

                    $capabilities = $equipment->getCapabilities();

                    $sEquipName = $this->_equip->getSpecificEquipName($row->getidStudyEquipments());
                    $equipStatus = $row->getEquipStatus();

                    $dimaResults = $this->getDoctrine()->getRepository(DimaResults::class)->findBy(["idStudyEquipments" => $row->getidStudyEquipments()], ["setpoint" => "DESC"]);
                    $idCoolingFamily = $equipment->getIdCoolingFamily()->getIdCoolingFamily();
                    

                    $sTR = "";
                    $sHrefTR = "#";
                    $sOptionTR = "";
                    $sdhp = $sconso = $staux = $sdhpmax = $sconsomax = $stauxmax = "";

                    // check isOnlyOneTr
                    if ($equipment->getNbTr() > 1 && $dimaResults != null) {
                        $dimaR = $dimaResults[$selectTR];
                        if ( ((!($this->_equip->getCapabilityNnc($capabilities, Post::CAP_DIMMAT_ENABLE))) 
                            || (!($this->_equip->getCapabilityNnc($capabilities, Post::CAP_VARIABLE_TR))))
                            && ($selectTR == Post::TRHIGHT_INDEX || $selectTR == Post::TRLOW_INDEX)
                        ) { 
                            $sTR = Post::NO_RESULTS; 
                            $sHrefTR = "<a href='#'>". $sTR ."</a>"; 
                            $sOptionTR = "disabled";
                            $sdhp = $sconso = $staux = Post::NO_RESULTS;
                            $sdhpmax  = $sconsomax = $stauxmax = Post::NO_RESULTS;
                        } else {
                            if ($this->_sizing->isValidTemperature($idStudyEquipments, $selectTR) && $dimaR != null) {
                                $sTR  = $this->_unit->controlTemperature($dimaR->getSetpoint());
                                $shref_TR = "<a href='javascript:;'' class='output-popup-tr' data-url='view-equip-tr' ". $sOptionTR .">". $sTR ."</a>";

                                if ($this->_equip->getCapabilityNnc($capabilities, Post::CAP_CONSO_ENABLE)) {
                                    if ($lfcoef != 0.0) {
                                        if ($this->_dima->isConsoToDisplay($dimaR->getDimaStatus() == 0)) {
                                            $sconso = Post::RESULT_NOT_APPLIC;
                                        } else {
                                            $consumption = $dimaR->getConsum() / $lfcoef;
                                            $sconso = $this->_unit->consumption($consumption, $idCoolingFamily, 1);
                                        }
                                        $consumptionMax = $dimaR->getConsumMax() / $lfcoef;
                                        $sconsomax = $this->_unit->consumption($consumptionMax, $idCoolingFamily, 1);
                                    } else {
                                        $sconso = $sconsomax = Post::RESULT_NOT_APPLIC;
                                    }
                                } else {
                                    $sconso = $sconsomax = Post::NO_RESULTS;
                                }

                                if ($this->_equip->getCapabilityNnc($capabilities, Post::CAP_VARIABLE_TOC)) {
                                    $batch = $equipment->getIdEquipseries()->getIdFamily()->isBatchProcess();

                                    $calculationStatus = $this->_dima->getCalculationStatus($dimaR->getDimaStatus());
                                    if ($calculationStatus != Post::DIMA_STATUS_OK) {
                                        $staux = $sdhp = Post::RESULT_NOT_APPLIC;
                                    } else {                                        
                                        if ($batch) {
                                            $massConvert = $this->_unit->mass($dimaR->getUserate());
                                            $massSymbol = $this->_unit->massSymbol();

                                            $staux = $massConvert . $massSymbol . "/batch";
                                        } else {
                                            $staux = $this->_unit->mass($dimaResults->getUserate()) . " %";
                                        }
                                        $sdhp = $this->_unit->productFlow($this->_sizing->initFlowRateFromDb($idStudy));
                                    }

                                    if ($batch) {
                                        $stauxmax = $this->_unit->mass($dimaR->getUseratemax()) . " " . $this->_unit->massSymbol() . "/batch";
                                    } else {
                                        $stauxmax = $this->_unit->mass($dimaR->getUseratemax()) . " %";
                                    }
                                    $sdhpmax = $this->_unit->productFlow($dimaR->getHourlyoutputmax());
                                } else {
                                    $staux = $stauxmax = Post::NO_RESULTS;
                                    $sdhp = $sdhpmax = Post::NO_RESULTS;
                                }
                            } else {
                                $sHrefTR = "<a href='#'>". $sTR ."</a>";;
                                $sOptionTR = "disabled";
                                $sdhp = $sconso = $staux = Post::RESULT_NOT_APPLIC;
                                $sdhpmax  = $sconsomax = $stauxmax = Post::RESULT_NOT_APPLIC;
                            }
                        }
                    } else {
                        $sTR = Post::NO_RESULTS; 
                        $sHrefTR = "<a href='#'>". $sTR ."</a>";; 
                        $sOptionTR = "disabled";
                        $sdhp = $sconso = $staux = Post::NO_RESULTS;
                        $sdhpmax  = $sconsomax = $stauxmax = Post::NO_RESULTS;
                    }
                    
                    if ($this->_equip->getCapabilityNnc($capabilities , Post::CAP_DIM_SPECIAL_ENABLE)) {
                        $equipementString = "<a href='javascript' class='add-equipment' data-href='add-equipment'>". $sEquipName ."</a>";
                    } else {
                        $equipementString = "<a href='#' disabled>". $sEquipName ."</a>";
                    }
                    

                    $item["equipment"] = $equipment;
                    $item["sEquipName"] = $sEquipName;
                    $item["equipementString"] = $equipementString;
                    $item["sHrefTR"] = $sHrefTR;
                    $item["sTR"] = $sTR;
                    $item["sdhp"] = $sdhp;
                    $item["sconso"] = $sconso;
                    $item["staux"] = $staux;
                    $item["sdhpmax"] = $sdhpmax;
                    $item["sconsomax"] = $sconsomax;
                    $item["stauxmax"] = $stauxmax;

                    $arrStudyEquipment[] = $item;
                }

                // get list equipment grap
                
            }

            if($calculationMode == 2 || $calculationMode == 3){
                //get list equipment value
                foreach ($studyEquipments as $row) {
                    $equipment = $row->getIdEquip();

                    $capabilities = $equipment->getCapabilities();

                    $sEquipName = $this->_equip->getSpecificEquipName($row->getidStudyEquipments());
                    $equipStatus = $row->getEquipStatus();

                    $sTR = array();
                    $sTS = array();
                    $sVC = array();
                    $sDHP = array();
                    $sConso = array();
                    $sTOC = array();

                    
                    if (!($this->_equip->getCapabilityNnc($capabilities , 128))){
                        for ($i = 0; $i < 2; $i++) {
                            $tmp181_180 = $sVC[$i] = $sDHP[$i] = $sConso[$i] =  $sTOC[$i] = "****";
                            $sTS[$i] = $tmp181_180;
                            $sTR[$i] = $tmp181_180;
                        }
                    } else if ($equipStatus == 100000) {
                        for ($i = 0; $i < 2; $i++) {
                            $tmp246_245 = $sVC[$i] = $sDHP[$i] = $sConso[$i] =  $sTOC[$i] = "";
                            $sTS[$i] = $tmp246_245;
                            $sTR[$i] = $tmp246_245;
                        }
                    } else {
                        for ($i = 0; $i < 2; $i++) {
                            if (($i == 0) && ($equipStatus != 0) && ($equipStatus != 1) && ($equipStatus != 100000)) {
                                $tmp333_332 = $sVC[$i] = $sDHP[$i] = $sConso[$i] =  $sTOC[$i] = "****";
                                $sTS[$i] = $tmp333_332;
                                $sTR[$i] = $tmp333_332;
                            } else {
                                $dimaType = ($i == 0) ? 1 : 16;
                                $dimaResults = $doc->getRepository(DimaResults::class)->findOneBy(["idStudyEquipments" => $row->getidStudyEquipments(), "dimaType" => $dimaType]);

                                if ($dimaResults == null) {
                                    $tmp403_402 = $sVC[$i] = $sDHP[$i] = $sConso[$i] =  $sTOC[$i] = "";
                                    $sTS[$i] = $tmp403_402;
                                    $sTR[$i] = $tmp403_402;
                                } else {
                                    $ldError = 0;
                                    if ($i == 1) {
                                        $ldError = $this->_dima->getCalculationWarning($dimaResults->getDimaStatus());
                                        if (($ldError == 282) || ($ldError == 283) || ($ldError == 284) || ($ldError == 285) || ($ldError == 286)) {
                                            $ldError = 0;
                                        }
                                    }
                                    if (($i == 1) && ($ldError != 0)) {
                                        $tmp513_512 = $sVC[$i] = $sDHP[$i] = $sConso[$i] =  $sTOC[$i] = "****";
                                        $sTS[$i] = $tmp513_512;
                                        $sTR[$i] = $tmp513_512;
                                    } else {
                                        $sTR[$i] = $this->_unit->controlTemperature($dimaResults->getSetpoint());
                                        $sTS[$i] = $this->_unit->timeUnit($dimaResults->getDimaTS());
                                        $sVC[$i] = $this->_unit->convectionSpeed($dimaResults->getDimaVC());

                                        if ($this->_equip->getCapabilityNnc($capabilities, 128)) {
                                            $consumption = $dimaResults->getConsum() / $lfcoef;
                                            $idCoolingFamily = $equipment->getIdCoolingFamily()->getIdCoolingFamily();

                                            $valueStr = $this->_unit->consumption($consumption, $idCoolingFamily, 1);

                                            $calculationStatus = $this->_dima->getCalculationStatus($dimaResults->getDimaStatus());
                                            $fluidOverImg = "<img src='assets/dist/img/output/warning_fluid_overflow.gif' width='30' height='30' /> ";
                                            $dhpOverImg = "<img src='assets/dist/img/output/warning_dhp_overflow.gif' width='30' height='30' /> ";

                                            $sConso[$i] = $this->_dima->consumptionCell($lfcoef, $calculationStatus, $valueStr, $fluidOverImg, $dhpOverImg); 
                                        } else {
                                            $sConso[$i] = "****";
                                        }
                                        if ($this->_equip->getCapabilityNnc($capabilities, 32)) {
                                            $sDHP[$i] = $this->_unit->productFlow($dimaResults->getHourlyoutputmax());

                                            $batch = $equipment->getIdEquipseries()->getIdFamily()->isBatchProcess();
                                            if ($batch) {
                                                $sTOC[$i] = $this->_unit->mass($dimaResults->getUserate()) . " " . $this->_unit->massSymbol() . "/batch"; 
                                            } else {
                                                $sTOC[$i] = $this->_unit->toc($dimaResults->getUserate()) . " %";
                                            }
                                        } else {
                                            $tmp866_864 = "****";
                                            $sTOC[$i] = $tmp866_864;
                                            $sDHP[$i] = $tmp866_864;
                                        }
                                    }
                                }
                            }
                        }
                    }


                    $item["equipment"] = $equipment;
                    $item["sEquipName"] = $sEquipName;
                    $item["sTR"] = $sTR;
                    $item["sTS"] = $sTS;
                    $item["sVC"] = $sVC;
                    $item["sDHP"] = $sDHP;
                    $item["sConso"] = $sConso;
                    $item["sTOC"] = $sTOC;

                    $arrStudyEquipment[] = $item;
                }

                // get list equipment grap
                $j = 0;
                foreach ($studyEquipments as $row) {
                    $equipment = $row->getIdEquip();
                    $idStudyEquipments = $row->getIdStudyEquipments();

                    $capabilities = $equipment->getCapabilities();

                    $sEquipName = $this->_equip->getSpecificEquipName($row->getidStudyEquipments());
                    $equipStatus = $row->getEquipStatus();
                    $energy = $equipment->getIdCoolingFamily()->getIdCoolingFamily();

                    if ((!($this->_equip->getCapabilityNnc($capabilities , 128)) || ($row->getBrainType() == 0) || ($row->getEquipStatus() != 1))) {
                        $debug = "brain type: " . $row->getBrainType();
                        $debug .= " - equip status: " . $row->getEquipStatus();
                    } else {
                        $dimaResults = array();
                        $dimaResults[0] = $doc->getRepository(DimaResults::class)->findOneBy(["idStudyEquipments" => $row->getidStudyEquipments(), "dimaType" => 1]);
                        $dimaResults[1] = $doc->getRepository(DimaResults::class)->findOneBy(["idStudyEquipments" => $row->getidStudyEquipments(), "dimaType" => 16]);
                        if($dimaResults[0] != null || $dimaResults[1] != null){
                            for ($i = 0; $i < 2; $i++) {
                                $dimaType = ($i == 0) ? 1 : 16;
                                $dimaResult = $dimaResults[$i];

                                if (($dimaResult != null)) {
                                    
                                    if ($this->_equip->getCapabilityNnc($capabilities , 256)){
                                        if ($this->_dima->isConsoToDisplay($dimaResult->getDimaStatus())) {
                                            if ($lfcoef != 0.0) {
                                                $sConso[$i] = $this->_unit->consumption($dimaResult->getConsum() / $lfcoef, $energy, 1);
                                            } else {
                                                $sConso[$i] = "****";
                                            }
                                        } else {
                                            $sConso[$i] = "****";
                                        }
                                    } else {
                                        $sConso[$i] = "****";
                                    }

                                    if ($this->_equip->getCapabilityNnc($capabilities , 32)) {
                                        $sDHP[$i] = $this->_unit->productFlow($dimaResult->getHourlyoutputmax());

                                    } else {
                                        $sDHP[$i] = "****";
                                    }
                                                                   
                                } else {
                                    $tmp425_423 = "";
                                    $sConso[$i] = $tmp425_423;
                                    $sDHP[$i] = $tmp425_423;
                                }
                            }

                            $itemGrap["equipment"] = $equipment;
                            $itemGrap["idStudyEquipments"] = $idStudyEquipments;
                            $itemGrap["sEquipName"] = $sEquipName;
                            $itemGrap["sDHP"] = $sDHP;
                            $itemGrap["sConso"] = $sConso;

                            if ($j < 4) {
                                $listOfSelectedEquipments[] = $itemGrap;
                            } else {
                                $listOfAvailableEquipments[] = $itemGrap;
                            }

                        }
                        
                    }

                    $j++;
                }
            }
            

        }

        $temperatureSymbol = $this->_unit->symbolUnit(Post::TYPE_UNIT_TEMPERATURE);
        $productFlowSymbol = $this->_unit->productFlowSymbol();
        $perUnitOfMassSymbol = $this->_unit->perUnitOfMassSymbol();
        $consumptionSymbol = $this->_unit->consumptionSymbol($this->_unit->initEnergyDef(), 1);
        $timeSymbol = $this->_unit->symbolUnit(Post::TYPE_UNIT_TIME);
        $convectionSpeedSymbol = $this->_unit->convectionSpeedSymbol();

        $data = [
            "objStudy" => $objStudy,
            "arrStudyEquipment" => $arrStudyEquipment,
            "listOfSelectedEquipments" => $listOfSelectedEquipments,
            "listOfAvailableEquipments" => $listOfAvailableEquipments,
            "temperatureSymbol" => $temperatureSymbol,
            "productFlowSymbol" => $productFlowSymbol,
            "perUnitOfMassSymbol" => $perUnitOfMassSymbol,
            "consumptionSymbol" => $consumptionSymbol,
            "timeSymbol" => $timeSymbol,
            "convectionSpeedSymbol" => $convectionSpeedSymbol
        ];
        
        if ($calculationMode == 1) {
            $urlRender = "output/sizing/estimation/sizing.html.twig";
        } else {
            $urlRender = "output/sizing/optimum/sizing.html.twig";    
        }
        
        return $this->render($urlRender, $data);
    }

    /**
    * @Route("/temp-profile", name="tem-profile")
    */
    public function tempProfileAction(Request $request)
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
        $dimaResults = null;

        $studyEquipments = $this->getDoctrine()->getRepository(StudyEquipments::class)->findBy(["idStudy"=>$objStudy->getIdStudy()],["idStudyEquipments"=>"ASC"]);

        if (!empty($studyEquipments)) {
            
            $studEquipProfileList = array();
            foreach ($studyEquipments as $row) {
                $idStudyEquipments = $row->getidStudyEquipments();
                $studEquipProfile = $this->getDoctrine()->getRepository(StudEquipprofile::class)->findOneBy(["idStudyEquipments" => $idStudyEquipments]);
                if(count($studEquipProfile) > 0) {
                    $item["id"] = $row->getidStudyEquipments();
                    $item["name"] = $this->_equip->getSpecificEquipName($row->getidStudyEquipments());
                }
                $studEquipProfileList[] = $item;
            }

            $data = [
                "studEquipProfileList" => $studEquipProfileList
            ];

            $urlRender = "output/sizing/common/sizing-temp.html.twig";
            return $this->render($urlRender, $data);
        }

        
    }

    /**
    * @Route("/temp-profile-chart-data", name="temp-profile-chart-data")
    */
    public function tempProfileChartDataAction(Request $request){
        $idStudyEquipments = $request->get("idstudyequipments");
        $tempProfile = $this->_sizing->getStudEquipProfiles($idStudyEquipments);

        $data = array();
        if (!empty($tempProfile)) {
            $lfTime = 0.0;
            $lfTemp = 0.0;
            $lfConv = 0.0;

            $point2DSeriesTempCurveTop = array();
            $point2DSeriesConvCurveTop = array();

            $point2DSeriesTempCurveBottom = array();
            $point2DSeriesConvCurveBottom = array();

            $point2DSeriesTempCurveLeft = array();
            $point2DSeriesConvCurveLeft = array();

            $point2DSeriesTempCurveRight = array();
            $point2DSeriesConvCurveRight = array();

            $point2DSeriesTempCurveFront = array();
            $point2DSeriesConvCurveFront = array();

            $point2DSeriesTempCurveRear = array();
            $point2DSeriesConvCurveRear = array();

            foreach ($tempProfile as $row) {
                $lfTime = $this->_unit->timePosition($row->getEpXPosition());

                $lfTempTop = $this->_unit->temperature($row->getEpTempTop());
                $lfConvTop = $this->_unit->convectionCoeff($row->getEpAlphaTop()); 

                $lfTempBottom= $this->_unit->temperature($row->getEpTempBottom());
                $lfConvBottom = $this->_unit->convectionCoeff($row->getEpAlphaBottom());

                $lfTempLeft = $this->_unit->temperature($row->getEpTempLeft());
                $lfConvLeft = $this->_unit->convectionCoeff($row->getEpAlphaLeft());

                $lfTempRight = $this->_unit->temperature($row->getEpTempRight());
                $lfConvRight = $this->_unit->convectionCoeff($row->getEpAlphaRight());
           
                $lfTempFront = $this->_unit->temperature($row->getEpTempFront());
                $lfConvFront = $this->_unit->convectionCoeff($row->getEpAlphaFront());

                $lfTempRear = $this->_unit->temperature($row->getEpTempRear());
                $lfConvRear = $this->_unit->convectionCoeff($row->getEpAlphaRear());

                // Top position
                $itemTempCurveTop = ["x" => $lfTime, "y" => $lfTempTop];
                $itemConvCurveTop = ["x" => $lfTime, "y" => $lfConvTop];
                
                // Bottom position
                $itemTempCurveBottom = ["x" => $lfTime, "y" => $lfTempBottom];
                $itemConvCurveBottom = ["x" => $lfTime, "y" => $lfConvBottom];

                // Left position
                $itemTempCurveLeft = ["x" => $lfTime, "y" => $lfTempLeft];
                $itemConvCurveLeft = ["x" => $lfTime, "y" => $lfConvLeft];

                // Right position
                $itemTempCurveRight = ["x" => $lfTime, "y" => $lfTempRight];
                $itemConvCurveRight = ["x" => $lfTime, "y" => $lfConvRight];

                // Front position
                $itemTempCurveFront = ["x" => $lfTime, "y" => $lfTempFront];
                $itemConvCurveFront = ["x" => $lfTime, "y" => $lfConvFront];

                // Rear position
                $itemTempCurveRear = ["x" => $lfTime, "y" => $lfTempRear];
                $itemConvCurveRear = ["x" => $lfTime, "y" => $lfConvRear];

                //add item top to array
                $point2DSeriesTempCurveTop[] = $itemTempCurveTop;
                $point2DSeriesConvCurveTop[] = $itemConvCurveTop;

                //add item bottom to array
                $point2DSeriesTempCurveBottom[] = $itemTempCurveBottom;
                $point2DSeriesConvCurveBottom[] = $itemConvCurveBottom;

                //add item left to array
                $point2DSeriesTempCurveLeft[] = $itemTempCurveLeft;
                $point2DSeriesConvCurveLeft[] = $itemConvCurveLeft;

                //add item right to array
                $point2DSeriesTempCurveRight[] = $itemTempCurveRight;
                $point2DSeriesConvCurveRight[] = $itemConvCurveRight;

                //add item Front to array
                $point2DSeriesTempCurveFront[] = $itemTempCurveFront;
                $point2DSeriesConvCurveFront[] = $itemConvCurveFront;

                //add item rear to array
                $point2DSeriesTempCurveRear[] = $itemTempCurveRear;
                $point2DSeriesConvCurveRear[] = $itemConvCurveRear;
            }
            // dump($point2DSeriesTempCurveTop);die;
        }

        $data = [
            "tempChartData" => [
                "xLabel" => $this->_unit->timePositionSymbol(),
                "yLabel" => $this->_unit->temperatureSymbol(),
                "top" => [
                    "label" => "Top",
                    "color" => "rgb(0,0,255)",
                    "data" => $point2DSeriesTempCurveTop
                ],
                "bottom" => [
                    "label" => "Bottom",
                    "color" => "rgb(0,192,192)",
                    "data" => $point2DSeriesTempCurveBottom
                ],
                "left" => [
                    "label" => "Left",
                    "color" => "rgb(0,255,255)",
                    "data" => $point2DSeriesTempCurveLeft
                ],
                "right" => [
                    "label" => "Right",
                    "color" => "rgb(0,255,0)",
                    "data" => $point2DSeriesTempCurveRight
                ],
                "front" => [
                    "label" => "Front",
                    "color" => "rgb(255,0,0)",
                    "data" => $point2DSeriesTempCurveFront
                ],
                "rear" => [
                    "label" => "Rear",
                    "color" => "rgb(255,0,255)",
                    "data" => $point2DSeriesTempCurveRear
                ]
            ],
            "convChartData" => [
                "xLabel" => $this->_unit->timePositionSymbol(),
                "yLabel" => $this->_unit->convectionCoeffSymbol(),
                "top" => [
                    "label" => "Top",
                    "color" => "rgb(0,0,255)",
                    "data" => $point2DSeriesConvCurveTop
                ],
                "bottom" => [
                    "label" => "Bottom",
                    "color" => "rgb(0,192,192)",
                    "data" => $point2DSeriesConvCurveBottom
                ],
                "left" => [
                    "label" => "Left",
                    "color" => "rgb(0,255,255)",
                    "data" => $point2DSeriesConvCurveLeft
                ],
                "right" => [
                    "label" => "Right",
                    "color" => "rgb(0,255,0)",
                    "data" => $point2DSeriesConvCurveRight
                ],
                "front" => [
                    "label" => "Front",
                    "color" => "rgb(255,0,0)",
                    "data" => $point2DSeriesConvCurveFront
                ],
                "rear" => [
                    "label" => "Rear",
                    "color" => "rgb(255,0,255)",
                    "data" => $point2DSeriesConvCurveRear
                ]
            ]
        ];
        
       return new JsonResponse($data);
    }

    /**
    * @Route("/grap-out-sizing-result", name="grap-out-sizing-result")
    */
    public function grapOutSizingResultAction(Request $request){

        $listStudyEquip = $request->get("liststudyequip");

        $idStudy = 26;

        $objStudy = $this->getDoctrine()->getRepository(Studies::class)->find($idStudy);

        $studyEquipments = $this->getDoctrine()->getRepository(StudyEquipments::class)->createQueryBuilder("s")
            ->where("s.idStudy = :idStudy")
            ->andWhere("s.idStudyEquipments IN(:idStudyEquipments)")
            ->setParameter("idStudy", $idStudy)
            ->setParameter("idStudyEquipments", $listStudyEquip)
            ->orderBy("s.idStudyEquipments", "ASC")
            ->getQuery()->getResult();

        $dimaResults = null;
        $lfcoef = $this->_unit->unitConvert(Post::TYPE_UNIT_MASS_PER_UNIT, 1.0);

        $s1 = "Product flowrate";
        $s2 = "Maximum product flowrate";
        $s3 = "Cryogen consumption (product + equipment heat losses)";
        $s4 = "Maximum cryogen consumption (product + equipment heat losses)";
        $s5 = "Custom flow rate";

        $axisLeftLabel = "Flow rate " . $this->_unit->productFlowSymbol();
        

        $production = $this->getDoctrine()->getRepository(Production::class)->findOneBy(["idStudy" => $idStudy]);

        $lfRequiredProductFlow = $this->_unit->productFlow($production->getProdFlowRate());
        $ldDefaultEnergy = $this->_unit->consumptionSymbol($this->_equip->initEnergyDef(), 1);
        $perUnitOfMassSymbol = $this->_unit->perUnitOfMassSymbol();

        $axisRightLabel = "Conso ". $ldDefaultEnergy . "/" . $perUnitOfMassSymbol;

        $data = array();
        $dataChart = array();
        $listOfSelectedEquipments = array();
        foreach ($studyEquipments as $row) {
            $equipment = $row->getIdEquip();
            $idStudyEquipments = $row->getIdStudyEquipments();

            $capabilities = $equipment->getCapabilities();

            $sEquipName = $this->_equip->getSpecificEquipName($row->getidStudyEquipments());
            $equipStatus = $row->getEquipStatus();
            $energy = $equipment->getIdCoolingFamily()->getIdCoolingFamily();

            if ((!($this->_equip->getCapabilityNnc($capabilities , 128)) || ($row->getBrainType() == 0) || ($row->getEquipStatus() != 1))) {
                $debug = "brain type: " . $row->getBrainType();
                $debug .= " - equip status: " . $row->getEquipStatus();
            } else {
                for ($i = 0; $i < 2; $i++) {
                    $dimaType = ($i == 0) ? 1 : 16;
                    $dimaResults = $this->getDoctrine()->getRepository(DimaResults::class)->findOneBy(["idStudyEquipments" => $row->getidStudyEquipments(), "dimaType" => $dimaType]);

                    if (($dimaResults != null)) {
                        
                        if ($this->_equip->getCapabilityNnc($capabilities , 256)){
                            if ($this->_dima->isConsoToDisplay($dimaResults->getDimaStatus())) {
                                if ($lfcoef != 0.0) {
                                    $sConso[$i] = $this->_unit->consumption($dimaResults->getConsum() / $lfcoef, $energy, 1);
                                } else {
                                    $sConso[$i] = "****";
                                }
                            } else {
                                $sConso[$i] = "****";
                            }
                        } else {
                            $sConso[$i] = "****";
                        }

                        if ($this->_equip->getCapabilityNnc($capabilities , 32)) {
                            $sDHP[$i] = $this->_unit->productFlow($dimaResults->getHourlyoutputmax());

                        } else {
                            $sDHP[$i] = "****";
                        }
                            
                        
                    } else {
                        $tmp425_423 = "";
                        $sConso[$i] = $tmp425_423;
                        $sDHP[$i] = $tmp425_423;
                    }
                }

            }

            $itemGrap["equipment"] = $equipment;
            $itemGrap["idStudyEquipments"] = $idStudyEquipments;
            $itemGrap["sEquipName"] = $sEquipName;
            $itemGrap["sDHP"] = $sDHP;
            $itemGrap["sConso"] = $sConso;

            $listOfSelectedEquipments[] = $itemGrap;
        }

        foreach ($listOfSelectedEquipments as $key => $row) {
            $sDHP = $row["sDHP"];
            $sConso = $row["sConso"]; 

            foreach ($sDHP as $keyDph => $sdph) {
                if (($sdph == null) || ($sdph == "****") || ($sdph == "")) {
                    $sDHPData[$key][$keyDph] = 0.0;
                } else {
                    $sDHPData[$key][$keyDph] = $this->_unit->convertToDouble($sdph);
                }
            }

            foreach ($sConso as $keyConso => $sconso) {
                if (($sconso == null) || ($sconso == "****") || ($sconso == "")) {
                    $sConsoData[$key][$keyConso] = 0.0;
                } else {
                    $sConsoData[$key][$keyConso] = $this->_unit->convertToDouble($sconso);
                }
            }

            $item["sDHP"] = $sDHPData[$key];
            $item["sConso"] = $sConsoData[$key];
            $item["sEquipName"] = $row["sEquipName"];
            $dataChart[] =  $item;
            
        }

        $data = [
            "s1" => $s1,
            "s2" => $s2,
            "s3" => $s3,
            "s4" => $s4,
            "s5" => $s5,
            "custom_flow_rate" => $lfRequiredProductFlow,
            "axisLeftLabel" => $axisLeftLabel,
            "axisRightLabel" => $axisRightLabel,
            "dataChart" => $dataChart
        ];
        
       return new JsonResponse($data);
    }

    /**
    * @Route("/test-kernel", name="test-kernel")
    */
    public function testKernelAction(Request $request){
        echo $this->_kernel->testKernel(3);die;
    }

}