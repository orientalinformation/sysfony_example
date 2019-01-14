<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * RecordPosition
 *
 * @ORM\Table(name="record_position", indexes={@ORM\Index(name="IX_ID_STUDY_EQP", columns={"ID_STUDY_EQUIPMENTS"})})
 * @ORM\Entity
 */
class RecordPosition
{
    /**
     * @var float
     *
     * @ORM\Column(name="RECORD_TIME", type="float", precision=24, scale=0, nullable=true)
     */
    private $recordTime;

    /**
     * @var float
     *
     * @ORM\Column(name="AVERAGE_TEMP", type="float", precision=10, scale=0, nullable=true)
     */
    private $averageTemp;

    /**
     * @var float
     *
     * @ORM\Column(name="AVERAGE_ENTH_VAR", type="float", precision=10, scale=0, nullable=true)
     */
    private $averageEnthVar;

    /**
     * @var float
     *
     * @ORM\Column(name="ENTHALPY_VAR", type="float", precision=10, scale=0, nullable=true)
     */
    private $enthalpyVar;

    /**
     * @var boolean
     *
     * @ORM\Column(name="RECORD_BUFFER", type="boolean", nullable=true)
     */
    private $recordBuffer;

    /**
     * @var boolean
     *
     * @ORM\Column(name="RECORD_STATE", type="boolean", nullable=true)
     */
    private $recordState;

    /**
     * @var integer
     *
     * @ORM\Column(name="ID_REC_POS", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idRecPos;

    /**
     * @var \AppBundle\Entity\StudyEquipments
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\StudyEquipments")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ID_STUDY_EQUIPMENTS", referencedColumnName="ID_STUDY_EQUIPMENTS")
     * })
     */
    private $idStudyEquipments;



    /**
     * @return float
     */
    public function getRecordTime()
    {
        return $this->recordTime;
    }

    /**
     * @param float $recordTime
     *
     * @return self
     */
    public function setRecordTime($recordTime)
    {
        $this->recordTime = $recordTime;

        return $this;
    }

    /**
     * @return float
     */
    public function getAverageTemp()
    {
        return $this->averageTemp;
    }

    /**
     * @param float $averageTemp
     *
     * @return self
     */
    public function setAverageTemp($averageTemp)
    {
        $this->averageTemp = $averageTemp;

        return $this;
    }

    /**
     * @return float
     */
    public function getAverageEnthVar()
    {
        return $this->averageEnthVar;
    }

    /**
     * @param float $averageEnthVar
     *
     * @return self
     */
    public function setAverageEnthVar($averageEnthVar)
    {
        $this->averageEnthVar = $averageEnthVar;

        return $this;
    }

    /**
     * @return float
     */
    public function getEnthalpyVar()
    {
        return $this->enthalpyVar;
    }

    /**
     * @param float $enthalpyVar
     *
     * @return self
     */
    public function setEnthalpyVar($enthalpyVar)
    {
        $this->enthalpyVar = $enthalpyVar;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isRecordBuffer()
    {
        return $this->recordBuffer;
    }

    /**
     * @param boolean $recordBuffer
     *
     * @return self
     */
    public function setRecordBuffer($recordBuffer)
    {
        $this->recordBuffer = $recordBuffer;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isRecordState()
    {
        return $this->recordState;
    }

    /**
     * @param boolean $recordState
     *
     * @return self
     */
    public function setRecordState($recordState)
    {
        $this->recordState = $recordState;

        return $this;
    }

    /**
     * @return integer
     */
    public function getIdRecPos()
    {
        return $this->idRecPos;
    }

    /**
     * @param integer $idRecPos
     *
     * @return self
     */
    public function setIdRecPos($idRecPos)
    {
        $this->idRecPos = $idRecPos;

        return $this;
    }

    /**
     * @return \AppBundle\Entity\StudyEquipments
     */
    public function getIdStudyEquipments()
    {
        return $this->idStudyEquipments;
    }

    /**
     * @param \AppBundle\Entity\StudyEquipments $idStudyEquipments
     *
     * @return self
     */
    public function setIdStudyEquipments(\AppBundle\Entity\StudyEquipments $idStudyEquipments)
    {
        $this->idStudyEquipments = $idStudyEquipments;

        return $this;
    }
}

