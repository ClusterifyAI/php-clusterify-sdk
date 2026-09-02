<?php

declare(strict_types=1);

namespace Clusterify\DTO\Profile;

/**
 * Tenant user profile details.
 */
final readonly class ProfileData
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
            name: isset($data['name']) && is_string($data['name']) ? $data['name'] : null,
            email: (string) ($data['email'] ?? ''),
            country: isset($data['country']) && is_string($data['country']) ? $data['country'] : null,
            timezone: isset($data['timezone']) && is_string($data['timezone']) ? $data['timezone'] : null
        );
    }

    /**
     * @return array<string, string|null>
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
}
