<?php

namespace LaravelModular\Abstracts;

use LaravelModular\Traits\Injectable;
use LaravelModular\Traits\EmitsEvents;
use LaravelModular\Traits\HasCaching;
use LaravelModular\Traits\HasPipeline;
use LaravelModular\Traits\HasHooks;

/**
 * Base Service — all module services extend this.
 *
 * Provides: injection, event emission, caching, pipeline, hooks
 */
abstract class AbstractService
{
    use Injectable, EmitsEvents, HasCaching, HasPipeline, HasHooks;
}
