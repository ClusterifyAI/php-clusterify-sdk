<?php

declare(strict_types=1);

namespace Clusterify\DTO\Profile;

use Clusterify\Support\Payload;
use JsonSerializable;

/**
 * Tenant user profile details.
 */
final readonly class ProfileData implements JsonSerializable
{
    /**
     * @param string|null $name User full name (null if not provided)
     * @param string $email User account email
     * @param string|null $country User country name
     * @param string|null $timezone User timezone (e.g. 'America/New_York')
     */
    public function __construct(
        public ?string $name,
        public string $email,
        public ?string $country,
        public ?string $timezone
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: Payload::nullableString($data, 'name'),
            email: Payload::string($data, 'email', ''),
            country: Payload::nullableString($data, 'country'),
            timezone: Payload::nullableString($data, 'timezone')
        );
    }

    /**
     * @return array{name: string|null, email: string, country: string|null, timezone: string|null}
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'country' => $this->country,
            'timezone' => $this->timezone,
        ];
    }

    /**
     * @return array{name: string|null, email: string, country: string|null, timezone: string|null}
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
