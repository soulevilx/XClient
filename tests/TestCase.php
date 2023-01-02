<?php

namespace JOOservices\XClient\Tests;

use Faker\Factory;
use Faker\Generator;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    protected Generator $faker;

    public function setUp(): void
    {
        parent::setUp();

        $this->faker = Factory::create();
    }
}