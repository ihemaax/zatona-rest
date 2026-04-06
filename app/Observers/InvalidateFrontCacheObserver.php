<?php

namespace App\Observers;

use Illuminate\Support\Facades\Cache;

class InvalidateFrontCacheObserver
{
    protected array $keys = [
        'front.home.setting.v2',
        'front.home.products.v2',
        'front.home.popup.v2',
    ];

    public function saved($model): void
    {
        $this->flushKeys();
    }

    public function deleted($model): void
    {
        $this->flushKeys();
    }

    protected function flushKeys(): void
    {
        foreach ($this->keys as $key) {
            Cache::forget($key);
        }
    }
}
