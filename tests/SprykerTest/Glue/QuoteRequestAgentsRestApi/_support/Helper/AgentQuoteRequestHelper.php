<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerTest\Glue\QuoteRequestAgentsRestApi\Helper;

use Codeception\Module;
use Generated\Shared\Transfer\QuoteRequestTransfer;
use SprykerTest\ApiPlatform\Helper\ApiRequestHelperTrait;
use Symfony\Component\HttpFoundation\Response;

/**
 * The agent side of the quote-request action resources: the same POST to a sub-path of a single
 * quote request carrying an empty attribute object, under the `agent-quote-requests` collection.
 */
class AgentQuoteRequestHelper extends Module
{
    use ApiRequestHelperTrait;

    /**
     * @uses \Spryker\Glue\QuoteRequestAgentsRestApi\QuoteRequestAgentsRestApiConfig::RESOURCE_AGENT_QUOTE_REQUEST_CANCEL
     */
    protected const string ACTION_CANCEL = 'agent-quote-request-cancel';

    /**
     * @uses \Spryker\Glue\QuoteRequestAgentsRestApi\QuoteRequestAgentsRestApiConfig::RESOURCE_AGENT_QUOTE_REQUEST_REVISE
     */
    protected const string ACTION_REVISE = 'agent-quote-request-revise';

    /**
     * @uses \Spryker\Glue\QuoteRequestAgentsRestApi\QuoteRequestAgentsRestApiConfig::RESOURCE_AGENT_QUOTE_REQUEST_SEND_TO_CUSTOMER
     */
    protected const string ACTION_SEND_TO_CUSTOMER = 'agent-quote-request-send-to-customer';

    /**
     * @uses \Spryker\Glue\QuoteRequestAgentsRestApi\QuoteRequestAgentsRestApiConfig::RESOURCE_AGENT_QUOTE_REQUESTS
     */
    protected const string COLLECTION_AGENT_QUOTE_REQUESTS = 'agent-quote-requests';

    public function cancelAgentQuoteRequest(QuoteRequestTransfer $quoteRequestTransfer): Response
    {
        return $this->cancelAgentQuoteRequestByReference($this->getReference($quoteRequestTransfer));
    }

    public function cancelAgentQuoteRequestByReference(string $quoteRequestReference): Response
    {
        return $this->postAgentQuoteRequestAction($quoteRequestReference, static::ACTION_CANCEL);
    }

    public function reviseAgentQuoteRequest(QuoteRequestTransfer $quoteRequestTransfer): Response
    {
        return $this->reviseAgentQuoteRequestByReference($this->getReference($quoteRequestTransfer));
    }

    public function reviseAgentQuoteRequestByReference(string $quoteRequestReference): Response
    {
        return $this->postAgentQuoteRequestAction($quoteRequestReference, static::ACTION_REVISE);
    }

    public function sendAgentQuoteRequestToCustomer(QuoteRequestTransfer $quoteRequestTransfer): Response
    {
        return $this->sendAgentQuoteRequestToCustomerByReference($this->getReference($quoteRequestTransfer));
    }

    public function sendAgentQuoteRequestToCustomerByReference(string $quoteRequestReference): Response
    {
        return $this->postAgentQuoteRequestAction($quoteRequestReference, static::ACTION_SEND_TO_CUSTOMER);
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function encodeAgentQuoteRequestBody(array $attributes, ?string $quoteRequestReference = null): string
    {
        return $this->encodeJsonApiBody(
            static::COLLECTION_AGENT_QUOTE_REQUESTS,
            $attributes,
            $quoteRequestReference,
        );
    }

    protected function postAgentQuoteRequestAction(string $quoteRequestReference, string $action): Response
    {
        return $this->handleApiRequest(
            'POST',
            sprintf('/%s/%s/%s', static::COLLECTION_AGENT_QUOTE_REQUESTS, $quoteRequestReference, $action),
            $this->encodeJsonApiBody($action, []),
        );
    }

    protected function getReference(QuoteRequestTransfer $quoteRequestTransfer): string
    {
        return (string)$quoteRequestTransfer->getQuoteRequestReference();
    }
}
