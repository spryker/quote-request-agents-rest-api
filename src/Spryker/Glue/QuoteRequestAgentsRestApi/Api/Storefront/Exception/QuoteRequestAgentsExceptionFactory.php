<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\QuoteRequestAgentsRestApi\Api\Storefront\Exception;

use Generated\Shared\Transfer\RestErrorMessageTransfer;
use Spryker\ApiPlatform\Exception\GlueApiException;
use Spryker\Glue\QuoteRequestAgentsRestApi\QuoteRequestAgentsRestApiConfig;
use Symfony\Component\HttpFoundation\Response;

/**
 * Builds pre-configured `GlueApiException` instances for every agent-quote-request error scenario.
 *
 * Uses {@see QuoteRequestAgentsRestApiConfig::getErrorIdentifierToRestErrorMapping()} as the source
 * of truth for glossary-key → REST error translation, keeping JSON:API responses byte-equivalent
 * to the legacy stack.
 */
class QuoteRequestAgentsExceptionFactory
{
    public function __construct(
        protected QuoteRequestAgentsRestApiConfig $quoteRequestAgentsRestApiConfig,
    ) {
    }

    public function createQuoteRequestNotFoundException(): GlueApiException
    {
        return new GlueApiException(
            Response::HTTP_NOT_FOUND,
            QuoteRequestAgentsRestApiConfig::RESPONSE_CODE_QUOTE_REQUEST_NOT_FOUND,
            QuoteRequestAgentsRestApiConfig::RESPONSE_DETAIL_QUOTE_REQUEST_NOT_FOUND,
        );
    }

    public function createQuoteRequestReferenceMissingException(): GlueApiException
    {
        return new GlueApiException(
            Response::HTTP_BAD_REQUEST,
            QuoteRequestAgentsRestApiConfig::RESPONSE_CODE_QUOTE_REQUEST_REFERENCE_MISSING,
            QuoteRequestAgentsRestApiConfig::RESPONSE_DETAIL_QUOTE_REQUEST_REFERENCE_MISSING,
        );
    }

    public function createCompanyUserNotFoundException(): GlueApiException
    {
        return new GlueApiException(
            Response::HTTP_NOT_FOUND,
            QuoteRequestAgentsRestApiConfig::RESPONSE_CODE_COMPANY_USER_NOT_FOUND,
            QuoteRequestAgentsRestApiConfig::RESPONSE_DETAIL_COMPANY_USER_NOT_FOUND,
        );
    }

    public function createProblemCreatingQuoteRequestByAgentException(): GlueApiException
    {
        return new GlueApiException(
            Response::HTTP_UNPROCESSABLE_ENTITY,
            QuoteRequestAgentsRestApiConfig::RESPONSE_CODE_PROBLEM_CREATING_REQUEST_FOR_QUOTE_BY_AGENT,
            QuoteRequestAgentsRestApiConfig::RESPONSE_DETAILS_PROBLEM_CREATING_REQUEST_FOR_QUOTE_BY_AGENT,
        );
    }

    public function createQuoteRequestWrongStatusException(): GlueApiException
    {
        return new GlueApiException(
            Response::HTTP_UNPROCESSABLE_ENTITY,
            QuoteRequestAgentsRestApiConfig::RESPONSE_CODE_QUOTE_REQUEST_WRONG_STATUS,
            QuoteRequestAgentsRestApiConfig::RESPONSE_DETAIL_QUOTE_REQUEST_WRONG_STATUS,
        );
    }

    public function createMetadataDeliveryDateInvalidException(): GlueApiException
    {
        return new GlueApiException(
            Response::HTTP_UNPROCESSABLE_ENTITY,
            QuoteRequestAgentsRestApiConfig::RESPONSE_CODE_METADATA_DELIVERY_DATE_IS_INVALID,
            QuoteRequestAgentsRestApiConfig::RESPONSE_DETAILS_METADATA_DELIVERY_DATE_IS_INVALID,
        );
    }

    /**
     * Builds a `GlueApiException` from a glossary-key carried in a `MessageTransfer.value`.
     * Looks the key up in {@see QuoteRequestAgentsRestApiConfig::getErrorIdentifierToRestErrorMapping()}
     * and falls back to the supplied default `(status, code, detail)` triple when no mapping matches.
     */
    public function createExceptionFromErrorIdentifier(
        ?string $errorIdentifier,
        int $fallbackStatus = Response::HTTP_UNPROCESSABLE_ENTITY,
        string $fallbackCode = QuoteRequestAgentsRestApiConfig::RESPONSE_CODE_PROBLEM_CREATING_REQUEST_FOR_QUOTE_BY_AGENT,
        string $fallbackDetail = QuoteRequestAgentsRestApiConfig::RESPONSE_DETAILS_PROBLEM_CREATING_REQUEST_FOR_QUOTE_BY_AGENT,
    ): GlueApiException {
        if ($errorIdentifier === null) {
            return new GlueApiException($fallbackStatus, $fallbackCode, $fallbackDetail);
        }

        $mapping = $this->quoteRequestAgentsRestApiConfig->getErrorIdentifierToRestErrorMapping();

        if (!isset($mapping[$errorIdentifier])) {
            return new GlueApiException($fallbackStatus, $fallbackCode, $fallbackDetail);
        }

        $entry = $mapping[$errorIdentifier];

        return new GlueApiException(
            (int)$entry[RestErrorMessageTransfer::STATUS],
            (string)$entry[RestErrorMessageTransfer::CODE],
            (string)$entry[RestErrorMessageTransfer::DETAIL],
        );
    }
}
