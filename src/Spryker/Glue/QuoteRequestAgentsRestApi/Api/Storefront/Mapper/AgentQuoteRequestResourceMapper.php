<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\QuoteRequestAgentsRestApi\Api\Storefront\Mapper;

use Generated\Shared\Transfer\QuoteRequestTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Spryker\Service\Serializer\SerializerServiceInterface;

/**
 * Builds the agent `AgentQuoteRequests` resource from a `QuoteRequestTransfer`. Always uses
 * the latest working version regardless of `isLatestVersionVisible` (the key difference from
 * the storefront mapper) and has no expander-plugin stack — no project registers an agent
 * `RestQuoteRequestAttributesExpanderPluginInterface` chain. Uses the Serializer for the
 * typed shape.
 */
class AgentQuoteRequestResourceMapper
{
    public function __construct(
        protected SerializerServiceInterface $serializer,
    ) {
    }

    /**
     * @template TResource of object
     *
     * @param class-string<TResource> $resourceClass
     *
     * @return TResource
     */
    public function denormalizeAgentQuoteRequestResource(
        QuoteRequestTransfer $quoteRequestTransfer,
        string $resourceClass,
    ): object {
        return $this->serializer->denormalize(
            $this->mapQuoteRequestTransferToResourceData($quoteRequestTransfer),
            $resourceClass,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function mapQuoteRequestTransferToResourceData(QuoteRequestTransfer $quoteRequestTransfer): array
    {
        $data = $quoteRequestTransfer->toArray(false, true);

        $data['versions'] = $this->extractVersionReferences($quoteRequestTransfer);
        $data['customer'] = $this->flattenCustomer($quoteRequestTransfer);
        $data['shownVersion'] = $this->resolveShownVersion($quoteRequestTransfer);

        return $data;
    }

    /**
     * @return array<string>
     */
    public function extractVersionReferences(QuoteRequestTransfer $quoteRequestTransfer): array
    {
        return array_values(array_filter(array_map(
            fn ($version) => $version->getVersionReference(),
            $quoteRequestTransfer->getQuoteRequestVersions()->getArrayCopy(),
        )));
    }

    /**
     * @return array<string, mixed>|null
     */
    public function flattenCustomer(QuoteRequestTransfer $quoteRequestTransfer): ?array
    {
        $companyUserTransfer = $quoteRequestTransfer->getCompanyUser();

        if ($companyUserTransfer === null) {
            return null;
        }

        $customerData = [
            'idCompanyUser' => $companyUserTransfer->getIdCompanyUser(),
            'idCompany' => $companyUserTransfer->getFkCompany(),
            'idCompanyBusinessUnit' => $companyUserTransfer->getFkCompanyBusinessUnit(),
        ];

        $customerTransfer = $companyUserTransfer->getCustomer();

        if ($customerTransfer !== null) {
            $customerData['idCustomer'] = $customerTransfer->getIdCustomer();
            $customerData['email'] = $customerTransfer->getEmail();
            $customerData['firstName'] = $customerTransfer->getFirstName();
            $customerData['lastName'] = $customerTransfer->getLastName();
        }

        return $customerData;
    }

    /**
     * Agents always see the latest working version regardless of the visibility flag.
     *
     * @return array<string, mixed>|null
     */
    public function resolveShownVersion(QuoteRequestTransfer $quoteRequestTransfer): ?array
    {
        $quoteRequestVersionTransfer = $quoteRequestTransfer->getLatestVersion();

        if ($quoteRequestVersionTransfer === null) {
            return null;
        }

        $versionData = [
            'version' => $quoteRequestVersionTransfer->getVersion(),
            'versionReference' => $quoteRequestVersionTransfer->getVersionReference(),
            'metadata' => $quoteRequestVersionTransfer->getMetadata(),
        ];

        $quoteTransfer = $quoteRequestVersionTransfer->getQuote();

        if ($quoteTransfer !== null) {
            $versionData['cart'] = $this->mapCart($quoteTransfer);
        }

        return $versionData;
    }

    /**
     * @return array<string, mixed>
     */
    public function mapCart(QuoteTransfer $quoteTransfer): array
    {
        return [
            'priceMode' => $quoteTransfer->getPriceMode(),
            'currency' => $quoteTransfer->getCurrency()?->getCode(),
            'store' => $quoteTransfer->getStore()?->getName(),
            'totals' => $this->mapTotals($quoteTransfer),
            'items' => $this->mapItems($quoteTransfer),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function mapTotals(QuoteTransfer $quoteTransfer): ?array
    {
        $totalsTransfer = $quoteTransfer->getTotals();

        if ($totalsTransfer === null) {
            return null;
        }

        $taxTotal = $totalsTransfer->getTaxTotal()?->getAmount();

        return [
            'subtotal' => $totalsTransfer->getSubtotal(),
            'grandTotal' => $totalsTransfer->getGrandTotal(),
            'discountTotal' => $totalsTransfer->getDiscountTotal(),
            'taxTotal' => $taxTotal,
            'expenseTotal' => $totalsTransfer->getExpenseTotal(),
            'priceToPay' => $totalsTransfer->getPriceToPay(),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function mapItems(QuoteTransfer $quoteTransfer): array
    {
        $items = [];

        foreach ($quoteTransfer->getItems() as $itemTransfer) {
            $items[] = [
                'sku' => $itemTransfer->getSku(),
                'groupKey' => $itemTransfer->getGroupKey(),
                'abstractSku' => $itemTransfer->getAbstractSku(),
                'quantity' => $itemTransfer->getQuantity(),
                'unitPrice' => $itemTransfer->getUnitPrice(),
                'sumPrice' => $itemTransfer->getSumPrice(),
                'unitGrossPrice' => $itemTransfer->getUnitGrossPrice(),
                'sumGrossPrice' => $itemTransfer->getSumGrossPrice(),
            ];
        }

        return $items;
    }
}
