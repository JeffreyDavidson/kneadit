<?php

declare(strict_types=1);

namespace App\Services\Platform\Contracts;

interface ForgeClient
{
    public function addDomainAlias(string $domain): bool;

    public function obtainSslCertificate(string $domain): bool;

    public function removeDomainAlias(string $domain): bool;
}
