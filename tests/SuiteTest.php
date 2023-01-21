<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class SuiteTest extends TestCase
{

    public function testEnvironmentIsSetToTesting(): void
    {
        $this->assertTrue(IS_TEST, 'environment is not set to TESTING while tests are running');
        $this->assertFalse(IS_DEV, 'environment is set to DEVELOPMENT while tests are running');
        $this->assertFalse(IS_PRODUCTION, 'environment is set to PRODUCTION while tests are running');
        $this->assertFalse(IS_STAGING, 'environment is set to STAGING while tests are running');
    }

}