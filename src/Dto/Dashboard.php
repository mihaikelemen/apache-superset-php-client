<?php

declare(strict_types=1);

namespace Superset\Dto;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class Dashboard
{
    public function __construct(
        public int $id,

        #[SerializedName('dashboard_title')]
        public ?string $title = null,

        public ?string $slug = null,

        public ?string $url = null,

        #[SerializedName('published')]
        public ?bool $isPublished = null,

        #[SerializedName('css')]
        public ?string $css = null,

        #[SerializedName('position_json')]
        public ?string $position = null,

        #[SerializedName('json_metadata')]
        public ?string $metadata = null,

        /** @var array<int, array{id: int, first_name: string, last_name: string}> */
        #[SerializedName('owners')]
        public array $owners = [],

        /** @var array{id: int, first_name: string, last_name: string}|null */
        #[SerializedName('created_by')]
        public ?array $createdBy = null,

        /** @var array{id: int, first_name: string, last_name: string}|null */
        #[SerializedName('changed_by')]
        public ?array $updatedBy = null,

        #[SerializedName('changed_on_utc')]
        public ?\DateTimeImmutable $updatedAt = null,

        /** @var array<int, array{id: int, name: string, type: int}> */
        #[SerializedName('tags')]
        public array $tags = [],

        /** @var array<int, array{id: int, name: string}> */
        #[SerializedName('roles')]
        public array $roles = [],

        #[SerializedName('thumbnail_url')]
        public ?string $thumbnail = null,

        #[SerializedName('is_managed_externally')]
        public ?bool $isManagedExternally = null,
    ) {
    }
}
