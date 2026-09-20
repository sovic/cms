<?php

namespace Sovic\Cms\Tests\Ai;

use PHPUnit\Framework\TestCase;
use Sovic\Cms\Ai\ApiKeyEncryptor;
use Sovic\Cms\Ai\Exception\ApiKeyDecryptException;

final class ApiKeyEncryptorTest extends TestCase
{
    public function testRoundTrip(): void
    {
        $encryptor = new ApiKeyEncryptor('secret-a');
        $encrypted = $encryptor->encrypt('sk-ant-api03-abcdef');

        self::assertStringNotContainsString('sk-ant', $encrypted);
        self::assertSame('sk-ant-api03-abcdef', $encryptor->decrypt($encrypted));
    }

    public function testUsesRandomNonce(): void
    {
        $encryptor = new ApiKeyEncryptor('secret-a');

        self::assertNotSame($encryptor->encrypt('key'), $encryptor->encrypt('key'));
    }

    public function testDifferentSecretFails(): void
    {
        $encrypted = (new ApiKeyEncryptor('secret-a'))->encrypt('key');

        $this->expectException(ApiKeyDecryptException::class);
        (new ApiKeyEncryptor('secret-b'))->decrypt($encrypted);
    }

    public function testTamperedValueFails(): void
    {
        $encryptor = new ApiKeyEncryptor('secret-a');
        $raw = base64_decode($encryptor->encrypt('key'));
        $raw[strlen($raw) - 1] = chr(ord($raw[strlen($raw) - 1]) ^ 1);

        $this->expectException(ApiKeyDecryptException::class);
        $encryptor->decrypt(base64_encode($raw));
    }

    public function testInvalidEncodingFails(): void
    {
        $this->expectException(ApiKeyDecryptException::class);
        (new ApiKeyEncryptor('secret-a'))->decrypt('not base64 !!');
    }

    public function testHint(): void
    {
        self::assertSame('wxyz', ApiKeyEncryptor::hint('sk-ant-abcdwxyz'));
    }
}
