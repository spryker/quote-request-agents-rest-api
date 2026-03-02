<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\QuoteRequestAgentsRestApi\Processor\Reader;

use Generated\Shared\Transfer\PaginationTransfer;
use Generated\Shared\Transfer\QuoteRequestFilterTransfer;
use Generated\Shared\Transfer\QuoteRequestResponseTransfer;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface;
use Spryker\Glue\GlueApplication\Rest\Request\Data\RestRequestInterface;
use Spryker\Glue\QuoteRequestAgentsRestApi\Dependency\Client\QuoteRequestAgentsRestApiToQuoteRequestAgentClientInterface;
use Spryker\Glue\QuoteRequestAgentsRestApi\Dependency\RestResource\QuoteRequestAgentsRestApiToQuoteRequestsRestApiResourceInterface;
use Spryker\Glue\QuoteRequestAgentsRestApi\Processor\RestResponseBuilder\QuoteRequestRestResponseBuilderInterface;

class QuoteRequestAgentReader implements QuoteRequestAgentReaderInterface
{
    public function __construct(
        protected QuoteRequestAgentsRestApiToQuoteRequestAgentClientInterface $quoteRequestAgentClient,
        protected QuoteRequestAgentsRestApiToQuoteRequestsRestApiResourceInterface $quoteRequestsRestApiResource,
        protected QuoteRequestRestResponseBuilderInterface $quoteRequestRestResponseBuilder
    ) {
    }

    public function findQuoteRequest(RestRequestInterface $restRequest): RestResponseInterface
    {
        $quoteRequestFilterTransfer = (new QuoteRequestFilterTransfer())
            ->setQuoteRequestReference($restRequest->getResource()->getId())
            ->setWithVersions(true);

        $quoteRequestTransfer = $this->quoteRequestAgentClient->findQuoteRequest($quoteRequestFilterTransfer);

        if (!$quoteRequestTransfer) {
            return $this->quoteRequestRestResponseBuilder->createQuoteRequestNotFoundErrorResponse();
        }

        return $this->quoteRequestsRestApiResource->createQuoteRequestRestResponse(
            (new QuoteRequestResponseTransfer())->setQuoteRequest($quoteRequestTransfer),
            $restRequest,
        );
    }

    public function getQuoteRequestCollectionByFilter(RestRequestInterface $restRequest): RestResponseInterface
    {
        $quoteRequestFilterTransfer = (new QuoteRequestFilterTransfer())->setWithVersions(true);

        $page = $restRequest->getPage();
        if ($page !== null) {
            $paginationTransfer = new PaginationTransfer();
            $paginationTransfer
                ->setMaxPerPage($page->getLimit())
                ->setPage(($page->getOffset() / $page->getLimit()) + 1);

            $quoteRequestFilterTransfer->setPagination($paginationTransfer);
        }

        $quoteRequestOverviewCollectionTransfer = $this->quoteRequestAgentClient
            ->getQuoteRequestCollectionByFilter($quoteRequestFilterTransfer);

        return $this->quoteRequestsRestApiResource->createQuoteRequestCollectionRestResponse(
            $quoteRequestOverviewCollectionTransfer,
            $restRequest,
        );
    }
}
