<?php /** @noinspection ALL */

namespace SovicCms\Email;

use RuntimeException;
use Symfony\Component\Mime\Crypto\DkimSigner;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Message;

class EmailHelper
{
    public static function signMessage(Email $message, string $domainName = 'mixdrive.sk'): Message
    {
        $keyPath = ROOT_PATH . '/dkim.private.key';
        if (!file_exists($keyPath)) {
            // no key, no sign
            throw new RuntimeException('invalid private key');
        }

        $key = file_get_contents($keyPath);

        return (new DkimSigner($key, $domainName, 'web'))->sign($message);
    }
}
