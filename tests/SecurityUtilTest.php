<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Simplon\Mysql\SecurityUtil;

class SecurityUtilTest extends TestCase
{
    public function testCreatePasswordHash()
    {
        $password = 'testPassword123';
        $hash = SecurityUtil::createPasswordHash($password);

        $this->assertNotNull($hash);
        $this->assertIsString($hash);
        $this->assertNotEquals($password, $hash);
    }

    public function testVerifyPasswordHash()
    {
        $password = 'testPassword123';
        $hash = SecurityUtil::createPasswordHash($password);

        $this->assertTrue(SecurityUtil::verifyPasswordHash($password, $hash));
        $this->assertFalse(SecurityUtil::verifyPasswordHash('wrongPassword', $hash));
    }

    public function testCreateRandomToken()
    {
        $token = SecurityUtil::createRandomToken();
        $this->assertEquals(12, strlen($token));

        $token = SecurityUtil::createRandomToken(20);
        $this->assertEquals(20, strlen($token));

        $token = SecurityUtil::createRandomToken(15, 'prefix_');
        $this->assertEquals(15, strlen($token));
        $this->assertStringStartsWith('prefix_', $token);

        $token = SecurityUtil::createRandomToken(10, null, 'ABC123');
        $this->assertEquals(10, strlen($token));
        $this->assertMatchesRegularExpression('/^[ABC123]+$/', $token);
    }

    public function testCreateSessionId()
    {
        $sessionId = SecurityUtil::createSessionId();
        $this->assertEquals(36, strlen($sessionId));

        $sessionId = SecurityUtil::createSessionId(50);
        $this->assertEquals(50, strlen($sessionId));
    }

    public function testCreateRandomTokenWithLongPrefix()
    {
        $this->expectException(\InvalidArgumentException::class);
        SecurityUtil::createRandomToken(10, 'very_long_prefix_');
    }

    public function testCreatePasswordHashWithCustomAlgorithm()
    {
        $password = 'testPassword123';
        $hash = SecurityUtil::createPasswordHash($password, PASSWORD_ARGON2I);

        $this->assertNotNull($hash);
        $this->assertIsString($hash);
        $this->assertStringStartsWith('$argon2i$', $hash);
    }

    public function testCreatePasswordHashWithCustomOptions()
    {
        $password = 'testPassword123';
        $options = ['cost' => 10];
        $hash = SecurityUtil::createPasswordHash($password, PASSWORD_BCRYPT, $options);

        $this->assertNotNull($hash);
        $this->assertIsString($hash);
    }
}
