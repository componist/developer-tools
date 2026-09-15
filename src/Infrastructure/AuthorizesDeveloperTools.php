<?php

declare(strict_types=1);

namespace Componist\DeveloperTools\Infrastructure;

use Illuminate\Support\Facades\Gate;

trait AuthorizesDeveloperTools
{
    public function bootAuthorizesDeveloperTools(): void
    {
        abort_unless(Gate::allows('manage-developer-tools'), 403);
    }
}
