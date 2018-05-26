<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 * Since: 2018
 */

namespace Test\Flancer32\EmailHijack\Helper;

include_once(__DIR__ . '/../phpunit_bootstrap.php');

class ConfigTest
    extends \PHPUnit\Framework\TestCase
{
    /** @var \Flancer32\EmailHijack\Helper\Config */
    private $obj;

    protected function setUp()
    {
        /** Get object to test */
        $obm = \Magento\Framework\App\ObjectManager::getInstance();
        $this->obj = $obm->get(\Flancer32\EmailHijack\Helper\Config::class);
    }


    public function test_all()
    {
        $res = $this->obj->getHijackEmails();
        $this->assertTrue(is_array($res));

        $res = $this->obj->getHijackEnabled();
        $this->assertTrue(is_bool($res));
    }
}