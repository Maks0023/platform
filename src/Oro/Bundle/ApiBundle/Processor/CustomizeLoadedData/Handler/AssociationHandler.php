<?php

namespace Oro\Bundle\ApiBundle\Processor\CustomizeLoadedData\Handler;

use Oro\Bundle\ApiBundle\Config\EntityDefinitionConfig;
use Oro\Bundle\ApiBundle\Processor\CustomizeLoadedData\CustomizeLoadedDataContext;
use Oro\Bundle\ApiBundle\Request\RequestType;
use Oro\Component\ChainProcessor\ActionProcessorInterface;

/**
 * The handler that can be used to modify loaded data for an entity association.
 */
class AssociationHandler extends EntityHandler
{
    /** @var string */
    private $rootEntityClass;

    /** @var string */
    private $propertyPath;

    /**
     * @param ActionProcessorInterface $customizationProcessor
     * @param string                   $version
     * @param RequestType              $requestType
     * @param string                   $rootEntityClass
     * @param string                   $propertyPath
     * @param string                   $entityClass
     * @param EntityDefinitionConfig   $config
     * @param callable|null            $previousHandler
     */
    public function __construct(
        ActionProcessorInterface $customizationProcessor,
        string $version,
        RequestType $requestType,
        string $rootEntityClass,
        string $propertyPath,
        string $entityClass,
        EntityDefinitionConfig $config,
        ?callable $previousHandler = null
    ) {
        $this->rootEntityClass = $rootEntityClass;
        $this->propertyPath = $propertyPath;
        parent::__construct(
            $customizationProcessor,
            $version,
            $requestType,
            $entityClass,
            $config,
            $previousHandler
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function createCustomizationContext(): CustomizeLoadedDataContext
    {
        $customizationContext = parent::createCustomizationContext();
        $customizationContext->setRootClassName($this->rootEntityClass);
        $customizationContext->setPropertyPath($this->propertyPath);

        return $customizationContext;
    }

    /**
     * {@inheritdoc}
     */
    protected function isRedundantHandler(callable $handler): bool
    {
        return
            $handler instanceof self
            && $this->propertyPath === $handler->propertyPath
            && \is_a($this->rootEntityClass, $handler->rootEntityClass, true)
            && parent::isRedundantHandler($handler);
    }
}
