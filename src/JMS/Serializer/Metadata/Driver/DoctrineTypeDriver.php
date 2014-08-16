<?php

/*
 * Copyright 2013 Johannes M. Schmitt <schmittjoh@gmail.com>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace JMS\Serializer\Metadata\Driver;

use Doctrine\Common\Persistence\Mapping\ClassMetadata as DoctrineClassMetadata;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadata as DoctrineODMClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadata as DoctrineORMClassMetadata;
use JMS\Serializer\Metadata\ClassMetadata;
use JMS\Serializer\Metadata\PropertyMetadata;

/**
 * This class decorates any other driver. If the inner driver does not provide a
 * a property type, the decorator will guess based on Doctrine 2 metadata.
 */
class DoctrineTypeDriver extends AbstractDoctrineTypeDriver
{
    /**
     * {@inheritdoc}
     */
    protected function setDiscriminator(DoctrineClassMetadata $doctrineMetadata, ClassMetadata $classMetadata)
    {
        if (!empty($classMetadata->discriminatorMap) || $classMetadata->discriminatorDisabled) {
            return;
        }

        // ORM
        if ($doctrineMetadata instanceof DoctrineORMClassMetadata
            && $doctrineMetadata->isRootEntity()
            && !empty($doctrineMetadata->discriminatorMap)
        ) {
            $classMetadata->setDiscriminator(
                $doctrineMetadata->discriminatorColumn['name'],
                $doctrineMetadata->discriminatorMap
            );
        }

        // ODM
        if ($doctrineMetadata instanceof DoctrineODMClassMetadata
            && $doctrineMetadata->name == $doctrineMetadata->rootDocumentName
            && !empty($doctrineMetadata->discriminatorMap)
        ) {
            $classMetadata->setDiscriminator(
                $doctrineMetadata->discriminatorField,
                $doctrineMetadata->discriminatorMap
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function setPropertyType(DoctrineClassMetadata $doctrineMetadata, PropertyMetadata $propertyMetadata)
    {
        $propertyName = $propertyMetadata->name;

        if ($doctrineMetadata->hasField($propertyName)) {
            if (null === $fieldType = $this->normalizeFieldType($doctrineMetadata->getTypeOfField($propertyName))) {
                return;
            }

            $propertyMetadata->setType($fieldType);
        }

        if ($doctrineMetadata->hasAssociation($propertyName)) {
            $targetEntity = $doctrineMetadata->getAssociationTargetClass($propertyName);

            if (null === $targetMetadata = $this->tryLoadingDoctrineMetadata($targetEntity)) {
                return;
            }

            // For inheritance schemes, we cannot add any type as we would only add the super-type of the hierarchy.
            // On serialization, this would lead to only the supertype being serialized, and properties of subtypes
            // being ignored.
            if (($targetMetadata instanceof DoctrineORMClassMetadata || $targetMetadata instanceof DoctrineODMClassMetadata)
                && !$targetMetadata->isInheritanceTypeNone()
            ) {
                return;
            }

            if (!$doctrineMetadata->isSingleValuedAssociation($propertyName)) {
                $targetEntity = "ArrayCollection<{$targetEntity}>";
            }

            $propertyMetadata->setType($targetEntity);
        }
    }

    /**
     * Order properties like they appear in Doctrine: identifiers first, then
     * fields ordered the way they appear in the class
     *
     * @param DoctrineClassMetadata $doctrineMetadata
     * @param ClassMetadata         $classMetadata
     */
    protected function setPropertyOrder(DoctrineClassMetadata $doctrineMetadata, ClassMetadata $classMetadata)
    {
        $identifierFields = $doctrineMetadata->getIdentifierFieldNames();
        $propertyFields = array_keys($doctrineMetadata->reflFields);

        $customOrder = array_unique(array_merge($identifierFields, $propertyFields));

        $classMetadata->setAccessorOrder(ClassMetadata::ACCESSOR_ORDER_CUSTOM, $customOrder);
    }
}
