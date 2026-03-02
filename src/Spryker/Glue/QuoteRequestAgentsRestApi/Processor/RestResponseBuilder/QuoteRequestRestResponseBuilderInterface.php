<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\QuoteRequestAgentsRestApi\Processor\RestResponseBuilder;

use Generated\Shared\Transfer\QuoteRequestResponseTransfer;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface;

interface QuoteRequestRestResponseBuilderInterface
{
    public function createFailedErrorResponse(QuoteRequestResponseTransfer $quoteRequestResponseTransfer): RestResponseInterface;

    public function createCompanyUserNotFoundErrorResponse(): RestResponseInterface;

    public function createQuoteRequestNotFoundErrorResponse(): RestResponseInterface;

    public function createQuoteRequestReferenceMissingErrorResponse(): RestResponseInterface;

    public function createDeliveryDateIsNotValidErrorResponse(): RestResponseInterface;
}
