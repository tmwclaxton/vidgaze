<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        foreach ([
            dirname(__DIR__).'/storage/framework/cache/data',
            dirname(__DIR__).'/storage/framework/sessions',
            dirname(__DIR__).'/storage/framework/testing',
            dirname(__DIR__).'/storage/framework/views',
            dirname(__DIR__).'/storage/app/public',
        ] as $dir) {
            if (! is_dir($dir)) {
                mkdir($dir, 0775, true);
            }
        }
    }
}
