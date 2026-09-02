<?php

declare(strict_types=1);

namespace Clusterify\Auth;

/**
 * Authentication mechanisms supported by Clusterify API.
 */
enum AuthMethod: string
{
    /**
     * Dedicated custom headers:
     * x-public-key: pk_...
     * x-secret-key: sk_...
     * (Recommended default)
     */
    case CustomHeaders = 'custom_headers';

    /**
     * Bearer token with public key header:
     * x-public-key: pk_...
     * Authorization: Bearer sk_...
     */
    case Bearer = 'bearer';

    /**
     * Standard HTTP Basic Authentication:
     * Authorization: Basic base64(publicKey:secretKey)
     */
    case Basic = 'basic';
}
