<?php

namespace Oro\Bundle\ApiBundle\Tests\Unit\Processor\Subresource\Shared;

use Oro\Bundle\ApiBundle\Model\Error;
use Oro\Bundle\ApiBundle\Processor\Subresource\Shared\RecognizeAssociationType;
use Oro\Bundle\ApiBundle\Provider\SubresourcesProvider;
use Oro\Bundle\ApiBundle\Request\ApiResourceSubresources;
use Oro\Bundle\ApiBundle\Request\ApiSubresource;
use Oro\Bundle\ApiBundle\Tests\Unit\Processor\Subresource\GetSubresourceProcessorTestCase;

class RecognizeAssociationTypeTest extends GetSubresourceProcessorTestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject|SubresourcesProvider */
    private $subresourcesProvider;

    /** @var RecognizeAssociationType */
    private $processor;

    protected function setUp()
    {
        parent::setUp();

        $this->subresourcesProvider = $this->createMock(SubresourcesProvider::class);

        $this->processor = new RecognizeAssociationType(
            $this->subresourcesProvider
        );
    }

    public function testProcessWhenEntityClassNameIsAlreadySet()
    {
        $this->subresourcesProvider->expects(self::never())
            ->method('getSubresources');

        $this->context->setClassName('Test\Class');
        $this->processor->process($this->context);
    }

    public function testProcessWhenAssociationNameIsNotSet()
    {
        $this->processor->process($this->context);

        self::assertEquals(
            [
                Error::createValidationError(
                    'relationship constraint',
                    'The association name must be set in the context.'
                )
            ],
            $this->context->getErrors()
        );
    }

    /**
     * @expectedException \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @expectedExceptionMessage Unsupported subresource.
     */
    public function testProcessForUnknownParentEntity()
    {
        $parentEntityClass = 'Test\ParentClass';
        $associationName = 'testAssociation';

        $this->subresourcesProvider->expects(self::once())
            ->method('getSubresources')
            ->with($parentEntityClass, $this->context->getVersion(), $this->context->getRequestType())
            ->willReturn(null);

        $this->context->setParentClassName($parentEntityClass);
        $this->context->setAssociationName($associationName);
        $this->processor->process($this->context);
    }

    /**
     * @expectedException \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @expectedExceptionMessage Unsupported subresource.
     */
    public function testProcessForUnknownAssociation()
    {
        $parentEntityClass = 'Test\ParentClass';
        $associationName = 'testAssociation';

        $entitySubresources = new ApiResourceSubresources($parentEntityClass);

        $this->subresourcesProvider->expects(self::once())
            ->method('getSubresources')
            ->with($parentEntityClass, $this->context->getVersion(), $this->context->getRequestType())
            ->willReturn($entitySubresources);

        $this->context->setParentClassName($parentEntityClass);
        $this->context->setAssociationName($associationName);
        $this->processor->process($this->context);
    }

    /**
     * @expectedException \Oro\Bundle\ApiBundle\Exception\ActionNotAllowedException
     */
    public function testProcessForExcludedAssociation()
    {
        $parentEntityClass = 'Test\ParentClass';
        $associationName = 'testAssociation';

        $entitySubresources = new ApiResourceSubresources($parentEntityClass);
        $associationSubresource = new ApiSubresource();
        $associationSubresource->setIsCollection(true);
        $associationSubresource->setTargetClassName('Test\Class');
        $associationSubresource->setExcludedActions([$this->context->getAction()]);
        $entitySubresources->addSubresource($associationName, $associationSubresource);

        $this->subresourcesProvider->expects(self::once())
            ->method('getSubresources')
            ->with($parentEntityClass, $this->context->getVersion(), $this->context->getRequestType())
            ->willReturn($entitySubresources);

        $this->context->setParentClassName($parentEntityClass);
        $this->context->setAssociationName($associationName);
        $this->processor->process($this->context);
    }

    public function testProcessForKnownAssociation()
    {
        $parentEntityClass = 'Test\ParentClass';
        $associationName = 'testAssociation';

        $entitySubresources = new ApiResourceSubresources($parentEntityClass);
        $associationSubresource = new ApiSubresource();
        $associationSubresource->setIsCollection(true);
        $associationSubresource->setTargetClassName('Test\Class');
        $entitySubresources->addSubresource($associationName, $associationSubresource);

        $this->subresourcesProvider->expects(self::once())
            ->method('getSubresources')
            ->with($parentEntityClass, $this->context->getVersion(), $this->context->getRequestType())
            ->willReturn($entitySubresources);

        $this->context->setParentClassName($parentEntityClass);
        $this->context->setAssociationName($associationName);
        $this->processor->process($this->context);

        self::assertEquals(
            $associationSubresource->getTargetClassName(),
            $this->context->getClassName()
        );
        self::assertEquals(
            $associationSubresource->isCollection(),
            $this->context->isCollection()
        );
    }

    public function testProcessForSubresourceWithEmptyTargetClass()
    {
        $parentEntityClass = 'Test\ParentClass';
        $associationName = 'testAssociation';

        $entitySubresources = new ApiResourceSubresources($parentEntityClass);
        $entitySubresources->addSubresource('testAssociation');

        $this->subresourcesProvider->expects(self::once())
            ->method('getSubresources')
            ->with($parentEntityClass, $this->context->getVersion(), $this->context->getRequestType())
            ->willReturn($entitySubresources);

        $this->context->setParentClassName($parentEntityClass);
        $this->context->setAssociationName($associationName);
        $this->processor->process($this->context);

        self::assertEquals(
            [
                Error::createValidationError(
                    'relationship constraint',
                    'The target entity type cannot be recognized.'
                )
            ],
            $this->context->getErrors()
        );
    }
}
