<?php

namespace JMS\Serializer\Tests\Fixtures\Doctrine\SingleTableInheritance;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Tests\Fixtures\Doctrine\SingleTableInheritance\Teacher;

/**
 * @ORM\Entity
 */
class Clazz extends AbstractModel
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy = "AUTO")
     * @ORM\Column(type = "integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity = "JMS\Serializer\Tests\Fixtures\Doctrine\SingleTableInheritance\Teacher")
     */
    private $teacher;

    /**
     * @ORM\ManyToMany(targetEntity = "JMS\Serializer\Tests\Fixtures\Doctrine\SingleTableInheritance\Student")
     */
    private $students;

    /**
     * @ORM\ManyToOne(targetEntity = "JMS\Serializer\Tests\Fixtures\Doctrine\SingleTableInheritance\School")
     */
    private $school;

    public function __construct(Teacher $teacher, array $students, School $school = null)
    {
        $this->teacher = $teacher;
        $this->students = new ArrayCollection($students);
        $this->school = $school;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getTeacher()
    {
        return $this->teacher;
    }

    public function getStudents()
    {
        return $this->students;
    }

    public function getSchool()
    {
        return $this->school;
    }
}
