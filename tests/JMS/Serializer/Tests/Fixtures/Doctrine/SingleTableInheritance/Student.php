<?php

namespace JMS\Serializer\Tests\Fixtures\Doctrine\SingleTableInheritance;

use JMS\Serializer\Annotation as JMS;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Student extends Person
{
    /**
     * @ORM\Column(type = "boolean")
     * @JMS\Groups({"bar"})
     */
    protected $isStudent = true;
}
