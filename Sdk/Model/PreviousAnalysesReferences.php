<?php
/**
 * Created by PhpStorm.
 * User: bdm
 * Date: 18/07/18
 * Time: 00:22
 */

namespace SensioLabs\Insight\Sdk\Model;

use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\XmlList;


class PreviousAnalysesReferences
{
    /**
     * @Type("array<SensioLabs\Insight\Sdk\Model\AnalysisReference>")
     * @XmlList(inline = true, entry = "analysis-reference")
     */
    private $previousAnalysesReferences = array();

    public function count()
    {
        return count($this->previousAnalysesReferences);
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->previousAnalysesReferences);
    }

    /**
     * @return mixed
     */
    public function getPreviousAnalysesReferences()
    {
        return $this->previousAnalysesReferences;
    }

    /**
     * @param $ref
     * @return mixed|null
     */
    public function findAnalysisNumberByReference($ref)
    {
        foreach ($this->previousAnalysesReferences as $analysesReference) {
            /** @var $analysesReference AnalysisReference */
            if ($analysesReference->getReference() === $ref) {
                return $analysesReference->getNumber();
            }
        }
        return null;
    }

}