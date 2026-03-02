<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\QuoteRequestAgentsRestApi;

use Spryker\Glue\Kernel\AbstractFactory;
use Spryker\Glue\QuoteRequestAgentsRestApi\Dependency\Client\QuoteRequestAgentsRestApiToCompanyUserStorageClientInterface;
use Spryker\Glue\QuoteRequestAgentsRestApi\Dependency\Client\QuoteRequestAgentsRestApiToQuoteRequestAgentClientInterface;
use Spryker\Glue\QuoteRequestAgentsRestApi\Dependency\RestResource\QuoteRequestAgentsRestApiToQuoteRequestsRestApiResourceInterface;
use Spryker\Glue\QuoteRequestAgentsRestApi\Processor\Canceller\QuoteRequestCanceller;
use Spryker\Glue\QuoteRequestAgentsRestApi\Processor\Canceller\QuoteRequestCancellerInterface;
use Spryker\Glue\QuoteRequestAgentsRestApi\Processor\Creator\QuoteRequestCreator;
use Spryker\Glue\QuoteRequestAgentsRestApi\Processor\Creator\QuoteRequestCreatorInterface;
use Spryker\Glue\QuoteRequestAgentsRestApi\Processor\Mapper\QuoteRequestMapper;
use Spryker\Glue\QuoteRequestAgentsRestApi\Processor\Mapper\QuoteRequestMapperInterface;
use Spryker\Glue\QuoteRequestAgentsRestApi\Processor\Reader\QuoteRequestAgentReader;
use Spryker\Glue\QuoteRequestAgentsRestApi\Processor\Reader\QuoteRequestAgentReaderInterface;
use Spryker\Glue\QuoteRequestAgentsRestApi\Processor\RestResponseBuilder\QuoteRequestRestResponseBuilder;
use Spryker\Glue\QuoteRequestAgentsRestApi\Processor\RestResponseBuilder\QuoteRequestRestResponseBuilderInterface;
use Spryker\Glue\QuoteRequestAgentsRestApi\Processor\Reviser\QuoteRequestReviser;
use Spryker\Glue\QuoteRequestAgentsRestApi\Processor\Reviser\QuoteRequestReviserInterface;
use Spryker\Glue\QuoteRequestAgentsRestApi\Processor\Sender\QuoteRequestSender;
use Spryker\Glue\QuoteRequestAgentsRestApi\Processor\Sender\QuoteRequestSenderInterface;
use Spryker\Glue\QuoteRequestAgentsRestApi\Processor\Updater\QuoteRequestUpdater;
use Spryker\Glue\QuoteRequestAgentsRestApi\Processor\Updater\QuoteRequestUpdaterInterface;
use Spryker\Glue\QuoteRequestAgentsRestApi\Processor\Validator\QuoteRequestValidator;
use Spryker\Glue\QuoteRequestAgentsRestApi\Processor\Validator\QuoteRequestValidatorInterface;

/**
 * @method \Spryker\Glue\QuoteRequestAgentsRestApi\QuoteRequestAgentsRestApiConfig getConfig()
 */
class QuoteRequestAgentsRestApiFactory extends AbstractFactory
{
    public function createQuoteRequestCreator(): QuoteRequestCreatorInterface
    {
        return new QuoteRequestCreator(
            $this->getQuoteRequestAgentClient(),
            $this->getCompanyUserStorageClient(),
            $this->getQuoteRequestsRestApiResource(),
            $this->createQuoteRequestRestResponseBuilder(),
        );
    }

    public function createQuoteRequestUpdater(): QuoteRequestUpdaterInterface
    {
        return new QuoteRequestUpdater(
            $this->getQuoteRequestAgentClient(),
            $this->getQuoteRequestsRestApiResource(),
            $this->createQuoteRequestRestResponseBuilder(),
            $this->createQuoteRequestMapper(),
            $this->createQuoteRequestValidator(),
        );
    }

    public function createQuoteRequestReader(): QuoteRequestAgentReaderInterface
    {
        return new QuoteRequestAgentReader(
            $this->getQuoteRequestAgentClient(),
            $this->getQuoteRequestsRestApiResource(),
            $this->createQuoteRequestRestResponseBuilder(),
        );
    }

    public function createQuoteRequestRestResponseBuilder(): QuoteRequestRestResponseBuilderInterface
    {
        return new QuoteRequestRestResponseBuilder(
            $this->getResourceBuilder(),
            $this->getConfig(),
        );
    }

    public function createQuoteRequestMapper(): QuoteRequestMapperInterface
    {
        return new QuoteRequestMapper();
    }

    public function getQuoteRequestsRestApiResource(): QuoteRequestAgentsRestApiToQuoteRequestsRestApiResourceInterface
    {
        return $this->getProvidedDependency(QuoteRequestAgentsRestApiDependencyProvider::RESOURCE_QUOTE_REQUESTS_REST_API);
    }

    public function getQuoteRequestAgentClient(): QuoteRequestAgentsRestApiToQuoteRequestAgentClientInterface
    {
        return $this->getProvidedDependency(QuoteRequestAgentsRestApiDependencyProvider::CLIENT_QUOTE_REQUEST_AGENT);
    }

    public function getCompanyUserStorageClient(): QuoteRequestAgentsRestApiToCompanyUserStorageClientInterface
    {
        return $this->getProvidedDependency(QuoteRequestAgentsRestApiDependencyProvider::CLIENT_COMPANY_USER_STORAGE);
    }

    public function createQuoteRequestCanceller(): QuoteRequestCancellerInterface
    {
        return new QuoteRequestCanceller(
            $this->getQuoteRequestAgentClient(),
            $this->createQuoteRequestRestResponseBuilder(),
            $this->getQuoteRequestsRestApiResource(),
        );
    }

    public function createQuoteRequestReviser(): QuoteRequestReviserInterface
    {
        return new QuoteRequestReviser(
            $this->createQuoteRequestRestResponseBuilder(),
            $this->getQuoteRequestAgentClient(),
            $this->getQuoteRequestsRestApiResource(),
        );
    }

    public function createQuoteRequestSender(): QuoteRequestSenderInterface
    {
        return new QuoteRequestSender(
            $this->createQuoteRequestRestResponseBuilder(),
            $this->getQuoteRequestAgentClient(),
            $this->getQuoteRequestsRestApiResource(),
        );
    }

    public function createQuoteRequestValidator(): QuoteRequestValidatorInterface
    {
        return new QuoteRequestValidator();
    }
}
