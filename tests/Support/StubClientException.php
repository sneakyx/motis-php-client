<?php

declare(strict_types=1);

namespace Sneakyx\MotisPhpClient\Tests\Support;

use Psr\Http\Client\ClientExceptionInterface;

final class StubClientException extends \Exception implements ClientExceptionInterface
{
}
