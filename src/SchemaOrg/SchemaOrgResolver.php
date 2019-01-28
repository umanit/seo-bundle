<?php

namespace Umanit\SeoBundle\SchemaOrg;

use Spatie\SchemaOrg\BaseType;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Umanit\SeoBundle\Model\AnnotationReaderTrait;

/**
 * Class SchemaOrgResolver
 *
 * Resolves a SchemaBuilder service from
 * an entity and builds its schema.org.
 *
 * @author Arthur Guigand <aguigand@umanit.fr>
 */
class SchemaOrgResolver implements ContainerAwareInterface
{
    use AnnotationReaderTrait,
        ContainerAwareTrait;

    /**
     * @param object|null $entity
     *
     * @return BaseType
     * @throws \ReflectionException
     * @throws \Umanit\SeoBundle\Exception\NotSchemaOrgEntityException
     */
    public function getSchemaBuilder(?object $entity): BaseType
    {
        $annotation = $this->getSchemaOrgAnnotation($entity);

        $serviceId = $annotation->getValue();

        try {
            $service = $this->container->get($serviceId);
        } catch (ServiceNotFoundException $e) {
            if (class_exists($serviceId)) {
                throw new \ErrorException(sprintf('The SchemaBuilder "%s" must be declared public.', $serviceId));
            }
            // Try to access via method
            /** @noinspection GetClassUsageInspection */
            $className = get_class($entity);
            try {
                $reflMethod = new \ReflectionMethod($className, $serviceId);
            } catch (\ReflectionException $e) {
                throw new \ErrorException(sprintf('Method "%s" of class "%s" does not exist.', $serviceId, $className));
            }

            if (!$reflMethod->isPublic()) {
                throw new \ErrorException(sprintf('Method "%s" of class "%s" must be public.', $serviceId, $className));
            }

            $schema = $entity->{$serviceId}();

            if (!$schema instanceof BaseType) {
                throw new \ErrorException(sprintf('Return of "%s" must be of type "%s".', $className.'::'.$serviceId, BaseType::class));
            }

            return $schema;
        }

        if (!$service instanceof SchemaBuilderInterface) {
            throw new \ErrorException(sprintf('The SchemaBuilder "%s" must implement %s.', get_class($service), SchemaBuilderInterface::class));
        }

        return $service->buildSchema($entity);
    }
}
