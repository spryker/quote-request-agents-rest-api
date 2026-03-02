<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\QuoteRequestAgentsRestApi\Dependency\Client;

use Generated\Shared\Transfer\CompanyUserStorageTransfer;

interface QuoteRequestAgentsRestApiToCompanyUserStorageClientInterface
{
    public function findCompanyUserByMapping(string $mappingType, string $identifier): ?CompanyUserStorageTransfer;
}
