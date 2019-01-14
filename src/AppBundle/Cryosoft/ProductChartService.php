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
use AppBundle\Entity\RecordPosition;
use AppBundle\Entity\TempRecordPts;
use AppBundle\Cryosoft\MinMaxService;

class ProductChartService
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

	public function getlistRecodPos($idStudyEquipments) 
	{
		return $this->getDoctrine()->getRepository(RecordPosition::class)->findBy(["idStudyEquipments" => $idStudyEquipments], ["recordTime" => "ASC"]);
	}

	public function getNbSteps($idStudy) 
	{
		return $this->getDoctrine()->getRepository(TempRecordPts::class)->findOneBy(["idStudy" => $idStudy]);
	}

	public function calculateEchantillon($ldNbSample, $ldNbRecord, $lfDwellingTime, $lfTimeStep) 
	{
		$tdSamplePos = array();
		$pos = 0;
		$lfSampleTime = 0.0;

		if ($ldNbSample > $ldNbRecord) {
            $ldNbSample = $ldNbRecord;
        }

        $lfSampleTime = $lfDwellingTime / ($ldNbSample - 1);

        for ($i = 0; $i < $ldNbSample - 1; $i++) {
            $pos = round($i * $lfSampleTime / $lfTimeStep);
            $tdSamplePos[] = $pos;
        }

        $pos = $ldNbRecord - 1;
        $tdSamplePos[] = $pos;

        return $tdSamplePos;
	}
}