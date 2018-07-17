<?php

/*
 * This file is part of the SensioLabsInsight package.
 *
 * (c) SensioLabs <contact@sensiolabs.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SensioLabs\Insight\Sdk\Model;

use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\XmlList;

class Violations implements \Countable, \IteratorAggregate
{
    /**
     * @Type("array<SensioLabs\Insight\Sdk\Model\Violation>")
     * @XmlList(inline = true, entry = "violation")
     */
    private $violations = array();

    public function count()
    {
        return count($this->violations);
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->violations);
    }

    /**
     * @return Violation[]
     */
    public function getViolations()
    {
        return $this->violations;
    }

    /**
     * @param callable $callback
     */
    public function filter($callback)
    {
        if (!is_callable($callback)) {
            throw new \InvalidArgumentException('The callback is not callable.');
        }

        $this->violations = array_filter($this->violations, $callback);
    }

    /**
     * @param mixed $violations
     * @return $this
     */
    public function setViolations(array $violations)
    {
        $this->violations = $violations;
        return $this;
    }

    /**
     * @param callable $callback
     */
    public function sort($callback = null)
    {
        if (!is_callable($callback)) {
            $callback =function (Violation $a, Violation $b) {
                if (Violation::$severitys[$a->getSeverity()] == Violation::$severitys[$b->getSeverity()]) {
                    return 0;
                }
                return (Violation::$severitys[$a->getSeverity()] < Violation::$severitys[$b->getSeverity()]) ? +1 : -1;
            };
        }

        usort($this->violations, $callback);
    }
}
