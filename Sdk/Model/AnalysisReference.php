<?php
/**
 * Created by PhpStorm.
 * User: bdm
 * Date: 17/07/18
 * Time: 20:18
 */

namespace SensioLabs\Insight\Sdk\Model;

use JMS\Serializer\Annotation\SerializedName;
use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\XmlAttribute;
use JMS\Serializer\Annotation\XmlValue;

class AnalysisReference
{

    /**
     * @Type("integer")
     * @XmlAttribute
     */
    private $number;

    /**
     * @XmlValue
     * @Type("string")
     */
    private $reference;

    /**
     * @return mixed
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @return mixed
     */
    public function getReference()
    {
        return $this->reference;
    }

}