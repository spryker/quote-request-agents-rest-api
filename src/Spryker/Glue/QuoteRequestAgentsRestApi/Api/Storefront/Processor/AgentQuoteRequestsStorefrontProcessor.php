<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\QuoteRequestAgentsRestApi\Api\Storefront\Processor;

use DateTime;
use Generated\Api\Storefront\AgentQuoteRequestsStorefrontResource;
use Generated\Shared\Transfer\CompanyUserTransfer;
use Generated\Shared\Transfer\QuoteRequestFilterTransfer;
use Generated\Shared\Transfer\QuoteRequestTransfer;
use Generated\Shared\Transfer\QuoteRequestVersionTransfer;
use Spryker\Client\CompanyUserStorage\CompanyUserStorageClientInterface;
use Spryker\Client\QuoteRequestAgent\QuoteRequestAgentClientInterface;
use Spryker\Glue\QuoteRequestAgentsRestApi\Api\Storefront\Exception\QuoteRequestAgentsExceptionFactory;
use Spryker\Glue\QuoteRequestAgentsRestApi\Api\Storefront\Mapper\AgentQuoteRequestResourceMapper;
use Spryker\Service\Serializer\SerializerServiceInterface;

class AgentQuoteRequestsStorefrontProcessor extends AbstractAgentQuoteRequestStorefrontProcessor
{
    protected const string KEY_DELIVERY_DATE = 'delivery_date';

    protected const string MAPPING_TYPE_UUID = 'uuid';

    /**
     * @uses \Spryker\Shared\Price\PriceConfig::PRICE_MODE_GROSS
     */
    protected const string PRICE_MODE_GROSS = 'GROSS_MODE';

    public function __construct(
        QuoteRequestAgentClientInterface $quoteRequestAgentClient,
        SerializerServiceInterface $serializer,
        QuoteRequestAgentsExceptionFactory $exceptionFactory,
        protected AgentQuoteRequestResourceMapper $agentQuoteRequestResourceMapper,
        protected CompanyUserStorageClientInterface $companyUserStorageClient,
    ) {
        parent::__construct($quoteRequestAgentClient, $serializer, $exceptionFactory);
    }

    /**
     * @param \Generated\Api\Storefront\AgentQuoteRequestsStorefrontResource $data
     *
     * @throws \Spryker\ApiPlatform\Exception\GlueApiException
     */
    protected function processPost(mixed $data): mixed
    {
        if ($data->companyUserUuid === null || $data->companyUserUuid === '') {
            throw $this->exceptionFactory->createCompanyUserNotFoundException();
        }

        $companyUserStorageTransfer = $this->companyUserStorageClient
            ->findCompanyUserByMapping(static::MAPPING_TYPE_UUID, $data->companyUserUuid);

        if ($companyUserStorageTransfer === null) {
            throw $this->exceptionFactory->createCompanyUserNotFoundException();
        }

        $companyUserTransfer = (new CompanyUserTransfer())
            ->setIdCompanyUser($companyUserStorageTransfer->getIdCompanyUser());

        $quoteRequestTransfer = $this->mapResourceToQuoteRequestTransfer($data, new QuoteRequestTransfer());

        $quoteRequestTransfer->setCompanyUser($companyUserTransfer);

        $this->validateDeliveryDate($quoteRequestTransfer);

        $quoteRequestResponseTransfer = $this->quoteRequestAgentClient->createQuoteRequest($quoteRequestTransfer);

        $this->assertSuccessful($quoteRequestResponseTransfer);

        return $this->agentQuoteRequestResourceMapper->denormalizeAgentQuoteRequestResource(
            $quoteRequestResponseTransfer->getQuoteRequestOrFail(),
            AgentQuoteRequestsStorefrontResource::class,
        );
    }

    /**
     * @param \Generated\Api\Storefront\AgentQuoteRequestsStorefrontResource $data
     *
     * @throws \Spryker\ApiPlatform\Exception\GlueApiException
     */
    protected function processPatch(mixed $data): mixed
    {
        $quoteRequestReference = $this->getUriVariables()['quoteRequestReference'] ?? null;

        $quoteRequestFilterTransfer = (new QuoteRequestFilterTransfer())
            ->setQuoteRequestReference($quoteRequestReference)
            ->setWithVersions(true);

        $quoteRequestTransfer = $this->quoteRequestAgentClient->findQuoteRequest($quoteRequestFilterTransfer);

        if ($quoteRequestTransfer === null) {
            throw $this->exceptionFactory->createQuoteRequestNotFoundException();
        }

        $quoteRequestTransfer = $this->mapResourceToQuoteRequestTransfer($data, $quoteRequestTransfer);

        $this->validateDeliveryDate($quoteRequestTransfer);

        $quoteRequestResponseTransfer = $this->quoteRequestAgentClient->updateQuoteRequest($quoteRequestTransfer);

        $this->assertSuccessful($quoteRequestResponseTransfer);

        return $this->agentQuoteRequestResourceMapper->denormalizeAgentQuoteRequestResource(
            $quoteRequestResponseTransfer->getQuoteRequestOrFail(),
            AgentQuoteRequestsStorefrontResource::class,
        );
    }

    protected function mapResourceToQuoteRequestTransfer(
        AgentQuoteRequestsStorefrontResource $resource,
        QuoteRequestTransfer $quoteRequestTransfer,
    ): QuoteRequestTransfer {
        $quoteRequestVersionTransfer = $quoteRequestTransfer->getLatestVersion() ?? new QuoteRequestVersionTransfer();

        if ($resource->metadata !== null) {
            $quoteRequestVersionTransfer->setMetadata($resource->metadata->toArray());
        }

        $quoteRequestTransfer->setLatestVersion($quoteRequestVersionTransfer);

        if ($resource->isLatestVersionVisible !== null) {
            $quoteRequestTransfer->setIsLatestVersionVisible($resource->isLatestVersionVisible);
        }

        if ($resource->validUntil !== null) {
            $quoteRequestTransfer->setValidUntil($resource->validUntil);
        }

        if (!empty($resource->unitPriceMap)) {
            $this->mapUnitPriceMapToQuoteItems($resource->unitPriceMap, $quoteRequestTransfer);
        }

        return $quoteRequestTransfer;
    }

    /**
     * @param array<string, mixed> $unitPriceMap
     */
    protected function mapUnitPriceMapToQuoteItems(array $unitPriceMap, QuoteRequestTransfer $quoteRequestTransfer): void
    {
        $latestVersion = $quoteRequestTransfer->getLatestVersion();

        if ($latestVersion === null) {
            return;
        }

        $quoteTransfer = $latestVersion->getQuote();

        if ($quoteTransfer === null) {
            return;
        }

        $isGrossMode = $quoteTransfer->getPriceMode() === static::PRICE_MODE_GROSS;

        foreach ($quoteTransfer->getItems() as $itemTransfer) {
            $groupKey = $itemTransfer->getGroupKey();

            if ($groupKey === null || !isset($unitPriceMap[$groupKey])) {
                continue;
            }

            $price = (int)$unitPriceMap[$groupKey];

            if ($isGrossMode) {
                $itemTransfer->setSourceUnitGrossPrice($price);

                continue;
            }

            $itemTransfer->setSourceUnitNetPrice($price);
        }
    }

    /**
     * @throws \Spryker\ApiPlatform\Exception\GlueApiException
     */
    protected function validateDeliveryDate(QuoteRequestTransfer $quoteRequestTransfer): void
    {
        $latestVersion = $quoteRequestTransfer->getLatestVersion();

        if ($latestVersion === null) {
            return;
        }

        $deliveryDate = $latestVersion->getMetadata()[static::KEY_DELIVERY_DATE] ?? null;

        if ($deliveryDate === null) {
            return;
        }

        if (strtotime((string)$deliveryDate) === false) {
            throw $this->exceptionFactory->createMetadataDeliveryDateInvalidException();
        }

        if ((new DateTime())->setTime(0, 0) > new DateTime((string)$deliveryDate)) {
            throw $this->exceptionFactory->createMetadataDeliveryDateInvalidException();
        }
    }
}
