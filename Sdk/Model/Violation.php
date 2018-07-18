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
use JMS\Serializer\Annotation\XmlAttribute;
use JMS\Serializer\Annotation\Exclude;

class Violation
{
    CONST SEVERITY_INFO     = 'info';
    CONST SEVERITY_MINOR    = 'minor';
    CONST SEVERITY_MAJOR    = "major";
    CONST SEVERITY_CRITICAL = 'critical';

    /**
     * @Exclude
     * @var array
     */
    static public $severitys = [
        self::SEVERITY_INFO => 0,
        self::SEVERITY_MINOR => 1,
        self::SEVERITY_MAJOR => 2,
        self::SEVERITY_CRITICAL => 3
    ];

    /** @Type("string") */
    private $title;

    /** @Type("string") */
    private $message;

    /** @Type("string") */
    private $resource;

    /** @Type("integer") */
    private $line;

    /**
     * @Type("string")
     * @XmlAttribute
     */
    private $severity;

    /**
     * @Type("string")
     * @XmlAttribute
     */
    private $category;

    /**
     * @Type("boolean")
     * @XmlAttribute
     */
    private $ignored;

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return string
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * @return int
     */
    public function getLine()
    {
        return $this->line;
    }

    /**
     * @return string
     */
    public function getSeverity()
    {
        return $this->severity;
    }

    /**
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @return bool
     */
    public function isIgnored()
    {
        return $this->ignored;
    }

    /**
     * @return string
     */
    public function getMd5()
    {
        return md5(implode('-',[
                $this->title,
                $this->message,
                $this->resource,
                $this->line,
                $this->severity,
                $this->category,
                $this->ignored
            ]));
    }
}
