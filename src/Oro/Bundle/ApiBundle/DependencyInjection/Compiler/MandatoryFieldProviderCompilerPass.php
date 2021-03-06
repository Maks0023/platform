<?php

namespace Oro\Bundle\ApiBundle\DependencyInjection\Compiler;

use Oro\Bundle\ApiBundle\Util\DependencyInjectionUtil;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\LogicException;

/**
 * Registers all providers of mandatory fields.
 */
class MandatoryFieldProviderCompilerPass implements CompilerPassInterface
{
    private const PROVIDER_REGISTRY_SERVICE_ID = 'oro_api.entity_serializer.mandatory_field_provider_registry';
    private const PROVIDER_TAG                 = 'oro.api.mandatory_field_provider';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        // find providers
        $providers = [];
        $taggedServices = $container->findTaggedServiceIds(self::PROVIDER_TAG);
        foreach ($taggedServices as $id => $attributes) {
            if (!$container->getDefinition($id)->isPublic()) {
                throw new LogicException(
                    \sprintf('The mandatory field provider service "%s" should be public.', $id)
                );
            }
            foreach ($attributes as $tagAttributes) {
                $providers[] = [
                    $id,
                    DependencyInjectionUtil::getAttribute($tagAttributes, 'requestType', null)
                ];
            }
        }
        if (empty($providers)) {
            return;
        }

        // register
        $container->getDefinition(self::PROVIDER_REGISTRY_SERVICE_ID)
            ->replaceArgument(0, $providers);
    }
}
