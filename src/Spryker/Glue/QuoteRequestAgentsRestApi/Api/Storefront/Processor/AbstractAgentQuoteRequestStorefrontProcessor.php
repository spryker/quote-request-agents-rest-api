<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\QuoteRequestAgentsRestApi\Api\Storefront\Processor;

use Generated\Shared\Transfer\QuoteRequestFilterTransfer;
use Generated\Shared\Transfer\QuoteRequestResponseTransfer;
use Spryker\ApiPlatform\State\Processor\AbstractStorefrontProcessor;
use Spryker\Client\QuoteRequestAgent\QuoteRequestAgentClientInterface;
use Spryker\Glue\QuoteRequestAgentsRestApi\Api\Storefront\Exception\QuoteRequestAgentsExceptionFactory;
use Spryker\Service\Serializer\SerializerServiceInterface;

/**
 * Shared scaffold for the four AgentQuoteRequests Storefront processors
 * (`AgentQuoteRequestsStorefrontProcessor` for POST/PATCH on the main resource plus the three
 * agent action processors — Cancel, Revise, SendToCustomer). Carries the common dependencies
 * (client / serializer / exception factory), the shared `QuoteRequestFilterTransfer` builder
 * used by every action, the status-only resource denormalizer, and the standard error-response
 * path: a `RestErrorCollectionTransfer` arriving inside an unsuccessful
 * `QuoteRequestResponseTransfer` is mapped to a `GlueApiException` through
 * `QuoteRequestAgentsExceptionFactory::createExceptionFromErrorIdentifier()`.
 */
abstract class AbstractAgentQuoteRequestStorefrontProcessor extends AbstractStorefrontProcessor
{
    public function __construct(
        protected QuoteRequestAgentClientInterface $quoteRequestAgentClient,
        protected SerializerServiceInterface $serializer,
        protected QuoteRequestAgentsExceptionFactory $exceptionFactory,
    ) {
    }

    /**
     * Builds the standard filter used by every agent action endpoint: the `quoteRequestReference`
     * from the URI plus `withVersions: true`. Agents bypass the per-company-user ownership check
     * applied on the storefront side.
     */
    protected function buildQuoteRequestActionFilter(): QuoteRequestFilterTransfer
    {
        $quoteRequestReference = $this->getUriVariables()['quoteRequestReference'] ?? null;

        return (new QuoteRequestFilterTransfer())
            ->setQuoteRequestReference($quoteRequestReference)
            ->setWithVersions(true);
    }

    /**
     * Builds the status-only response — used by action endpoints that surface
     * only the updated `{quoteRequestReference, status}` pair (Cancel, Revise, SendToCustomer).
     *
     * @template TResource of object
     *
     * @param class-string<TResource> $resourceClass
     *
     * @return TResource
     */
    protected function denormalizeQuoteRequestStatusResource(
        QuoteRequestResponseTransfer $quoteRequestResponseTransfer,
        string $resourceClass,
    ): object {
        $quoteRequestTransfer = $quoteRequestResponseTransfer->getQuoteRequestOrFail();

        return $this->serializer->denormalize([
            'quoteRequestReference' => $quoteRequestTransfer->getQuoteRequestReference(),
            'status' => $quoteRequestTransfer->getStatus(),
        ], $resourceClass);
    }

    /**
     * Translates the first error in an unsuccessful `QuoteRequestResponseTransfer` into a
     * `GlueApiException`. Falls back to a generic "problem creating RFQ by agent" error
     * when no message matches the `errorIdentifier → REST error` mapping in
     * `QuoteRequestAgentsRestApiConfig`.
     *
     * @throws \Spryker\ApiPlatform\Exception\GlueApiException
     */
    protected function assertSuccessful(QuoteRequestResponseTransfer $quoteRequestResponseTransfer): void
    {
        if ($quoteRequestResponseTransfer->getIsSuccessful()) {
            return;
        }

        foreach ($quoteRequestResponseTransfer->getMessages() as $messageTransfer) {
            $messageValue = $messageTransfer->getValue();

            if ($messageValue !== null) {
                throw $this->exceptionFactory->createExceptionFromErrorIdentifier($messageValue);
            }
        }

        throw $this->exceptionFactory->createProblemCreatingQuoteRequestByAgentException();
    }
}
