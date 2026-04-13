<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

abstract class Controller
{
    /**
     * @return list<int>
     */
    protected function perPageOptions(): array
    {
        return [10, 25, 50, 100];
    }

    protected function resolvePerPage(Request $request, int $default = 25): int
    {
        $perPage = $request->integer('per_page');

        return in_array($perPage, $this->perPageOptions(), true)
            ? $perPage
            : $default;
    }
}
