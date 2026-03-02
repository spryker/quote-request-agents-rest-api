<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\QuoteRequestAgentsRestApi\Dependency\Client;

use Generated\Shared\Transfer\QuoteRequestCollectionTransfer;
use Generated\Shared\Transfer\QuoteRequestFilterTransfer;
use Generated\Shared\Transfer\QuoteRequestResponseTransfer;
use Generated\Shared\Transfer\QuoteRequestTransfer;

class QuoteRequestAgentsRestApiToQuoteRequestAgentClientBridge implements QuoteRequestAgentsRestApiToQuoteRequestAgentClientInterface
{
    /**
     * @var \Spryker\Client\QuoteRequestAgent\QuoteRequestAgentClientInterface
     */
    protected $quoteRequestAgentClient;

    /**
     * @param \Spryker\Client\QuoteRequestAgent\QuoteRequestAgentClientInterface $quoteRequestAgentClient
     */
    public function __construct($quoteRequestAgentClient)
    {
        $this->quoteRequestAgentClient = $quoteRequestAgentClient;
    }

    public function findQuoteRequest(QuoteRequestFilterTransfer $quoteRequestFilterTransfer): ?QuoteRequestTransfer
    {
        return $this->quoteRequestAgentClient->findQuoteRequest($quoteRequestFilterTransfer);
    }

    public function createQuoteRequest(QuoteRequestTransfer $quoteRequestTransfer): QuoteRequestResponseTransfer
    {
        return $this->quoteRequestAgentClient->createQuoteRequest($quoteRequestTransfer);
    }

    public function getQuoteRequestCollectionByFilter(QuoteRequestFilterTransfer $quoteRequestFilterTransfer): QuoteRequestCollectionTransfer
    {
        return $this->quoteRequestAgentClient->getQuoteRequestCollectionByFilter($quoteRequestFilterTransfer);
    }

    public function reviseQuoteRequest(QuoteRequestFilterTransfer $quoteRequestFilterTransfer): QuoteRequestResponseTransfer
    {
        return $this->quoteRequestAgentClient->reviseQuoteRequest($quoteRequestFilterTransfer);
    }

    public function cancelQuoteRequest(QuoteRequestFilterTransfer $quoteRequestFilterTransfer): QuoteRequestResponseTransfer
    {
        return $this->quoteRequestAgentClient->cancelQuoteRequest($quoteRequestFilterTransfer);
    }

    public function sendQuoteRequestToCustomer(QuoteRequestFilterTransfer $quoteRequestFilterTransfer): QuoteRequestResponseTransfer
    {
        return $this->quoteRequestAgentClient->sendQuoteRequestToCustomer($quoteRequestFilterTransfer);
    }

    public function updateQuoteRequest(QuoteRequestTransfer $quoteRequestTransfer): QuoteRequestResponseTransfer
    {
        return $this->quoteRequestAgentClient->updateQuoteRequest($quoteRequestTransfer);
    }
}
