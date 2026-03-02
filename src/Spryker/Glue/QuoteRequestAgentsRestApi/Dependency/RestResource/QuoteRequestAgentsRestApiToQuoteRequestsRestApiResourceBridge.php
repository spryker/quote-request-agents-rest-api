<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\QuoteRequestAgentsRestApi\Dependency\RestResource;

use Generated\Shared\Transfer\QuoteRequestCollectionTransfer;
use Generated\Shared\Transfer\QuoteRequestResponseTransfer;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface;
use Spryker\Glue\GlueApplication\Rest\Request\Data\RestRequestInterface;

class QuoteRequestAgentsRestApiToQuoteRequestsRestApiResourceBridge implements QuoteRequestAgentsRestApiToQuoteRequestsRestApiResourceInterface
{
    /**
     * @var \Spryker\Glue\QuoteRequestsRestApi\QuoteRequestsRestApiResourceInterface
     */
    protected $quoteRequestsRestApiResource;

    /**
     * @param \Spryker\Glue\QuoteRequestsRestApi\QuoteRequestsRestApiResourceInterface $quoteRequestsRestApiResource
     */
    public function __construct($quoteRequestsRestApiResource)
    {
        $this->quoteRequestsRestApiResource = $quoteRequestsRestApiResource;
    }

    public function createQuoteRequestRestResponse(
        QuoteRequestResponseTransfer $quoteRequestResponseTransfer,
        RestRequestInterface $restRequest
    ): RestResponseInterface {
        return $this->quoteRequestsRestApiResource->createQuoteRequestRestResponse(
            $quoteRequestResponseTransfer,
            $restRequest,
        );
    }

    public function createQuoteRequestCollectionRestResponse(
        QuoteRequestCollectionTransfer $quoteRequestCollectionTransfer,
        RestRequestInterface $restRequest
    ): RestResponseInterface {
        return $this->quoteRequestsRestApiResource->createQuoteRequestCollectionRestResponse(
            $quoteRequestCollectionTransfer,
            $restRequest,
        );
    }
}
