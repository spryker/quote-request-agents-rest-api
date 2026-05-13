<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\QuoteRequestAgentsRestApi\Api\Storefront\Processor;

use Generated\Api\Storefront\AgentQuoteRequestSendToCustomerStorefrontResource;

class AgentQuoteRequestSendToCustomerStorefrontProcessor extends AbstractAgentQuoteRequestStorefrontProcessor
{
    /**
     * @param \Generated\Api\Storefront\AgentQuoteRequestSendToCustomerStorefrontResource $data
     */
    protected function processPost(mixed $data): mixed
    {
        $quoteRequestResponseTransfer = $this->quoteRequestAgentClient->sendQuoteRequestToCustomer(
            $this->buildQuoteRequestActionFilter(),
        );

        $this->assertSuccessful($quoteRequestResponseTransfer);

        return $this->denormalizeQuoteRequestStatusResource(
            $quoteRequestResponseTransfer,
            AgentQuoteRequestSendToCustomerStorefrontResource::class,
        );
    }
}
