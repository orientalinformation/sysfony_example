<?php
/****************************************************************************
**
** Copyright (C) 2017 Oriental Tran.
** Contact: dongtp@dfm-engineering.com
** Company: DFM-Engineering Vietnam
**
** This file is part of the cryosoft project.
**
**All rights reserved.
****************************************************************************/
namespace  AppBundle\Cryosoft;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityRepository;
use AppBundle\Entity\Unit;
use AppBundle\Entity\StudyEquipments;
use AppBundle\Entity\Studies;
use AppBundle\Entity\Post;
use AppBundle\Entity\StudEqpPrm;
use AppBundle\Entity\Production;
use AppBundle\Entity\StudEquipprofile;
use AppBundle\Cryosoft\MinMaxService;

class SizingResultService
{
	private $doctrine;
	private $user;

	public function __construct(\Doctrine\ORM\EntityManager $doctrine, TokenStorageInterface $tokenStorage, Session $session, MinMaxService $minmaxService) 
	{
		$this->doctrine = $doctrine;
		$this->user = $tokenStorage->getToken()->getUser();
		$this->session = $session;
		$this->_minmax = $minmaxService;
	}

	private function getDoctrine() 
	{
		return $this->doctrine;
	}

	public function isValidTemperature($idStudyEquipments, $selectTr)
	{
		$bisValid = true;
		$rStudyEquipments = $this->getDoctrine()->getRepository(StudyEquipments::class)->findOneBy(["idStudyEquipments" => $idStudyEquipments]);

		$lStudEqpPrm = $this->getDoctrine()->getRepository(StudEqpPrm::class)->createQueryBuilder("s")
                    ->where("s.valueType >= :valueType1 AND s.valueType < :valueType2")
                    ->andWhere("s.idStudyEquipments = :idStudyEquipments")
                    ->setParameter("valueType1", "300")
                    ->setParameter("valueType2", "400")
                    ->setParameter("idStudyEquipments", $idStudyEquipments)
                    ->getQuery()->getResult();

        $rEquipment = $rStudyEquipments->getIdEquip();
        $trType = $rEquipment->getItemTR();
        $mm = $this->_minmax->getDefaultValue($trType);

        if (count($lStudEqpPrm) == 1) {
        	$lfTr = $lStudEqpPrm[0]->getValue();
        	if ($selectTr == 2) {   		
        		if ($lfTr <= $mm->getLimitMin()) {
		          $bisValid = false;
		        }
        	} else if ($selectTr == 0) {
    			if ($lfTr >= $mm->getLimitMax()) {
		          $bisValid = false;
		        }
        	}
        } else {
	      $bisValid = false;
	    }

		return $bisValid;	    
	}

	public function initFlowRateFromDb($idStudy) 
	{
		$nFlowRate = 0.0;
		$production = $this->getDoctrine()->getRepository(Production::claas)->findOneBy(["idStudy" => $idStudy]);
		if (!empty($nFlowRate)) $nFlowRate = $production->getProdFlowRate();
		return $nFlowRate;
	}

	public function getStudEquipProfiles($idStudyEquipments) 
	{
		return $this->getDoctrine()->getRepository(StudEquipprofile::class)->findBy(["idStudyEquipments" => $idStudyEquipments], ["epXPosition" => "ASC"]);
	}

	
}