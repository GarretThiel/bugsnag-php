<?php

namespace Bugsnag\Tests;

use Bugsnag\Internal\GuzzleCompat;
use phpmock\phpunit\PHPMock;
use PHPUnit\Framework\TestCase as BaseTestCase;
use PHPUnit\Runner\Version as PhpUnitVersion;

abstract class TestCase extends BaseTestCase
{
    use PHPMock;

    public function expectedException($class, $message = null)
    {
        if ($this->isPhpUnit4()) {
            $this->setExpectedException($class, $message);

            return;
        }

        $this->expectException($class);

        if ($message !== null) {
            $this->expectExceptionMessage($message);
        }
    }

    protected function isPhpUnit7()
    {
        return version_compare($this->phpUnitVersion(), '7.0.0', '>=')
            && version_compare($this->phpUnitVersion(), '8.0.0', '<');
    }

    protected function isPhpUnit4()
    {
        return version_compare($this->phpUnitVersion(), '4.0.0', '>=')
            && version_compare($this->phpUnitVersion(), '5.0.0', '<');
    }

    /**
     * @return string
     */
    protected static function getGuzzleMethod()
    {
        return GuzzleCompat::isUsingGuzzle5() ? 'post' : 'request';
    }

    private function phpUnitVersion()
    {
        // Support versions of PHPUnit before 6.0.0 when native namespaces were
        // introduced for the Version class
        if (class_exists(\PHPUnit_Runner_Version::class)) {
            return \PHPUnit_Runner_Version::id();
        }

        return PhpUnitVersion::id();
    }

    protected function getPayloadFromGuzzleOptions(array $options)
    {
        $this->assertArrayHasKey('body', $options);
        Assert::isType('string', $options['body']);

        $payload = json_decode($options['body'], true);

        $this->assertSame(JSON_ERROR_NONE, json_last_error(), json_last_error_msg());

        return $payload;
    }
}
