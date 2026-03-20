<?php

declare(strict_types=1);

namespace App\Application\User\Exceptions;

use App\Application\Shared\Exceptions\ApplicationNotFoundException;

final class CannotLeaveImpersonation extends ApplicationNotFoundException {}
