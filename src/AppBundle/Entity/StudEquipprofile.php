<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * StudEquipprofile
 *
 * @ORM\Table(name="stud_equipprofile", indexes={@ORM\Index(name="ID_STUDY_EQP", columns={"ID_STUDY_EQUIPMENTS"})})
 * @ORM\Entity
 */
class StudEquipprofile
{
    /**
     * @var float
     *
     * @ORM\Column(name="EP_X_POSITION", type="float", precision=10, scale=0, nullable=true)
     */
    private $epXPosition;

    /**
     * @var float
     *
     * @ORM\Column(name="EP_TEMP_REGUL", type="float", precision=10, scale=0, nullable=true)
     */
    private $epTempRegul;

    /**
     * @var float
     *
     * @ORM\Column(name="EP_ALPHA_TOP", type="float", precision=10, scale=0, nullable=true)
     */
    private $epAlphaTop;

    /**
     * @var float
     *
     * @ORM\Column(name="EP_ALPHA_BOTTOM", type="float", precision=10, scale=0, nullable=true)
     */
    private $epAlphaBottom;

    /**
     * @var float
     *
     * @ORM\Column(name="EP_ALPHA_LEFT", type="float", precision=10, scale=0, nullable=true)
     */
    private $epAlphaLeft;

    /**
     * @var float
     *
     * @ORM\Column(name="EP_ALPHA_RIGHT", type="float", precision=10, scale=0, nullable=true)
     */
    private $epAlphaRight;

    /**
     * @var float
     *
     * @ORM\Column(name="EP_ALPHA_FRONT", type="float", precision=10, scale=0, nullable=true)
     */
    private $epAlphaFront;

    /**
     * @var float
     *
     * @ORM\Column(name="EP_ALPHA_REAR", type="float", precision=10, scale=0, nullable=true)
     */
    private $epAlphaRear;

    /**
     * @var float
     *
     * @ORM\Column(name="EP_TEMP_TOP", type="float", precision=10, scale=0, nullable=true)
     */
    private $epTempTop;

    /**
     * @var float
     *
     * @ORM\Column(name="EP_TEMP_BOTTOM", type="float", precision=10, scale=0, nullable=true)
     */
    private $epTempBottom;

    /**
     * @var float
     *
     * @ORM\Column(name="EP_TEMP_LEFT", type="float", precision=10, scale=0, nullable=true)
     */
    private $epTempLeft;

    /**
     * @var float
     *
     * @ORM\Column(name="EP_TEMP_RIGHT", type="float", precision=10, scale=0, nullable=true)
     */
    private $epTempRight;

    /**
     * @var float
     *
     * @ORM\Column(name="EP_TEMP_FRONT", type="float", precision=10, scale=0, nullable=true)
     */
    private $epTempFront;

    /**
     * @var float
     *
     * @ORM\Column(name="EP_TEMP_REAR", type="float", precision=10, scale=0, nullable=true)
     */
    private $epTempRear;

    /**
     * @var integer
     *
     * @ORM\Column(name="ID_STUD_EQUIPPROFILE", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idStudEquipprofile;

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
    public function getEpXPosition()
    {
        return $this->epXPosition;
    }

    /**
     * @param float $epXPosition
     *
     * @return self
     */
    public function setEpXPosition($epXPosition)
    {
        $this->epXPosition = $epXPosition;

        return $this;
    }

    /**
     * @return float
     */
    public function getEpTempRegul()
    {
        return $this->epTempRegul;
    }

    /**
     * @param float $epTempRegul
     *
     * @return self
     */
    public function setEpTempRegul($epTempRegul)
    {
        $this->epTempRegul = $epTempRegul;

        return $this;
    }

    /**
     * @return float
     */
    public function getEpAlphaTop()
    {
        return $this->epAlphaTop;
    }

    /**
     * @param float $epAlphaTop
     *
     * @return self
     */
    public function setEpAlphaTop($epAlphaTop)
    {
        $this->epAlphaTop = $epAlphaTop;

        return $this;
    }

    /**
     * @return float
     */
    public function getEpAlphaBottom()
    {
        return $this->epAlphaBottom;
    }

    /**
     * @param float $epAlphaBottom
     *
     * @return self
     */
    public function setEpAlphaBottom($epAlphaBottom)
    {
        $this->epAlphaBottom = $epAlphaBottom;

        return $this;
    }

    /**
     * @return float
     */
    public function getEpAlphaLeft()
    {
        return $this->epAlphaLeft;
    }

    /**
     * @param float $epAlphaLeft
     *
     * @return self
     */
    public function setEpAlphaLeft($epAlphaLeft)
    {
        $this->epAlphaLeft = $epAlphaLeft;

        return $this;
    }

    /**
     * @return float
     */
    public function getEpAlphaRight()
    {
        return $this->epAlphaRight;
    }

    /**
     * @param float $epAlphaRight
     *
     * @return self
     */
    public function setEpAlphaRight($epAlphaRight)
    {
        $this->epAlphaRight = $epAlphaRight;

        return $this;
    }

    /**
     * @return float
     */
    public function getEpAlphaFront()
    {
        return $this->epAlphaFront;
    }

    /**
     * @param float $epAlphaFront
     *
     * @return self
     */
    public function setEpAlphaFront($epAlphaFront)
    {
        $this->epAlphaFront = $epAlphaFront;

        return $this;
    }

    /**
     * @return float
     */
    public function getEpAlphaRear()
    {
        return $this->epAlphaRear;
    }

    /**
     * @param float $epAlphaRear
     *
     * @return self
     */
    public function setEpAlphaRear($epAlphaRear)
    {
        $this->epAlphaRear = $epAlphaRear;

        return $this;
    }

    /**
     * @return float
     */
    public function getEpTempTop()
    {
        return $this->epTempTop;
    }

    /**
     * @param float $epTempTop
     *
     * @return self
     */
    public function setEpTempTop($epTempTop)
    {
        $this->epTempTop = $epTempTop;

        return $this;
    }

    /**
     * @return float
     */
    public function getEpTempBottom()
    {
        return $this->epTempBottom;
    }

    /**
     * @param float $epTempBottom
     *
     * @return self
     */
    public function setEpTempBottom($epTempBottom)
    {
        $this->epTempBottom = $epTempBottom;

        return $this;
    }

    /**
     * @return float
     */
    public function getEpTempLeft()
    {
        return $this->epTempLeft;
    }

    /**
     * @param float $epTempLeft
     *
     * @return self
     */
    public function setEpTempLeft($epTempLeft)
    {
        $this->epTempLeft = $epTempLeft;

        return $this;
    }

    /**
     * @return float
     */
    public function getEpTempRight()
    {
        return $this->epTempRight;
    }

    /**
     * @param float $epTempRight
     *
     * @return self
     */
    public function setEpTempRight($epTempRight)
    {
        $this->epTempRight = $epTempRight;

        return $this;
    }

    /**
     * @return float
     */
    public function getEpTempFront()
    {
        return $this->epTempFront;
    }

    /**
     * @param float $epTempFront
     *
     * @return self
     */
    public function setEpTempFront($epTempFront)
    {
        $this->epTempFront = $epTempFront;

        return $this;
    }

    /**
     * @return float
     */
    public function getEpTempRear()
    {
        return $this->epTempRear;
    }

    /**
     * @param float $epTempRear
     *
     * @return self
     */
    public function setEpTempRear($epTempRear)
    {
        $this->epTempRear = $epTempRear;

        return $this;
    }

    /**
     * @return integer
     */
    public function getIdStudEquipprofile()
    {
        return $this->idStudEquipprofile;
    }

    /**
     * @param integer $idStudEquipprofile
     *
     * @return self
     */
    public function setIdStudEquipprofile($idStudEquipprofile)
    {
        $this->idStudEquipprofile = $idStudEquipprofile;

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

