<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\QuoteRequestAgentsRestApi\Api\Storefront\Provider;

use Generated\Api\Storefront\AgentQuoteRequestsStorefrontResource;
use Generated\Shared\Transfer\QuoteRequestCollectionTransfer;
use Generated\Shared\Transfer\QuoteRequestFilterTransfer;
use Spryker\ApiPlatform\State\Provider\AbstractStorefrontProvider;
use Spryker\Client\QuoteRequestAgent\QuoteRequestAgentClientInterface;
use Spryker\Glue\QuoteRequestAgentsRestApi\Api\Storefront\Exception\QuoteRequestAgentsExceptionFactory;
use Spryker\Glue\QuoteRequestAgentsRestApi\Api\Storefront\Mapper\AgentQuoteRequestResourceMapper;
use Spryker\Service\Serializer\SerializerServiceInterface;

class AgentQuoteRequestsStorefrontProvider extends AbstractStorefrontProvider
{
    public function __construct(
        protected QuoteRequestAgentClientInterface $quoteRequestAgentClient,
        protected SerializerServiceInterface $serializer,
        protected QuoteRequestAgentsExceptionFactory $exceptionFactory,
        protected AgentQuoteRequestResourceMapper $agentQuoteRequestResourceMapper,
    ) {
    }

    /**
     * @return array<\Generated\Api\Storefront\AgentQuoteRequestsStorefrontResource>
     */
    protected function provideCollection(): array
    {
        $quoteRequestFilterTransfer = (new QuoteRequestFilterTransfer())
            ->setWithVersions(true)
            ->setPagination($this->buildPaginationTransfer());

        $quoteRequestCollectionTransfer = $this->quoteRequestAgentClient
            ->getQuoteRequestCollectionByFilter($quoteRequestFilterTransfer);

        return $this->mapQuoteRequestCollectionToResourceArray($quoteRequestCollectionTransfer);
    }

    /**
     * @throws \Spryker\ApiPlatform\Exception\GlueApiException
     */
    protected function provideItem(): ?object
    {
        $quoteRequestReference = $this->getUriVariables()['quoteRequestReference'] ?? null;

        if ($quoteRequestReference === null) {
            return null;
        }

        $quoteRequestFilterTransfer = (new QuoteRequestFilterTransfer())
            ->setQuoteRequestReference($quoteRequestReference)
            ->setWithVersions(true);

        $quoteRequestTransfer = $this->quoteRequestAgentClient->findQuoteRequest($quoteRequestFilterTransfer);

        if ($quoteRequestTransfer === null) {
            throw $this->exceptionFactory->createQuoteRequestNotFoundException();
        }

        return $this->agentQuoteRequestResourceMapper->denormalizeAgentQuoteRequestResource(
            $quoteRequestTransfer,
            AgentQuoteRequestsStorefrontResource::class,
        );
    }

    /**
     * @return array<\Generated\Api\Storefront\AgentQuoteRequestsStorefrontResource>
     */
    protected function mapQuoteRequestCollectionToResourceArray(
        QuoteRequestCollectionTransfer $quoteRequestCollectionTransfer,
    ): array {
        $resources = [];

        foreach ($quoteRequestCollectionTransfer->getQuoteRequests() as $quoteRequestTransfer) {
            $resources[] = $this->agentQuoteRequestResourceMapper->denormalizeAgentQuoteRequestResource(
                $quoteRequestTransfer,
                AgentQuoteRequestsStorefrontResource::class,
            );
        }

        return $resources;
    }
}
