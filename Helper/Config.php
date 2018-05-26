<?php
/**
 * Helper to get Store Configuration parameters related to the module.
 *
 * User: Alex Gusev <alex@flancer64.com>
 * Since: 2018
 */

namespace Flancer32\EmailHijack\Helper;

class Config
{

    /** @var \Magento\Framework\App\Config\ScopeConfigInterface */
    private $scopeConfig;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * List of email addresses to get all hijacked emails.
     *
     * @return array of string
     */
    public function getHijackEmails()
    {
        $value = $this->scopeConfig->getValue('system/smtp/hijack_emails');
        $emails = explode(',', $value);
        $result = [];
        foreach ($emails as $email) {
            $normalized = trim(strtolower($email));
            if ($normalized) {
                $result[] = $normalized;
            }
        }
        return $result;
    }

    /**
     * Return 'true' if hijack mode is enabled for emails.
     *
     * @return bool
     */
    public function getHijackEnabled()
    {
        $result = $this->scopeConfig->getValue('system/smtp/hijack_enabled');
        $result = filter_var($result, FILTER_VALIDATE_BOOLEAN);
        return $result;
    }

}