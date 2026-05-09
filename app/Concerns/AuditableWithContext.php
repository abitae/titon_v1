<?php

namespace App\Concerns;

use App\Services\Audit\AuditContextResolver;
use OwenIt\Auditing\Auditable;

trait AuditableWithContext
{
    use Auditable;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function transformAudit(array $data): array
    {
        return array_merge(
            $data,
            app(AuditContextResolver::class)->metadataFor($this, $data),
        );
    }

    /**
     * @return list<string>
     */
    public function generateTags(): array
    {
        return [
            app(AuditContextResolver::class)->moduleFor($this),
            class_basename($this),
            (string) $this->getAuditEvent(),
        ];
    }
}
