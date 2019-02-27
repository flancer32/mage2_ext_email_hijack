<?php
/**
 * Process recipients emails and replace its by developer's emails.
 *
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2018
 */

namespace Flancer32\EmailHijack\Plugin\Magento\Framework\Mail;

class Message
{

    /** @var \Flancer32\EmailHijack\Helper\Config */
    private $hlpConfig;
    /** @var \Magento\Framework\App\ProductMetadataInterface */
    private $metadata;

    public function __construct(
        \Magento\Framework\App\ProductMetadataInterface $metadata,
        \Flancer32\EmailHijack\Helper\Config $hlpConfig
    ) {
        $this->metadata = $metadata;
        $this->hlpConfig = $hlpConfig;
    }

    public function aroundAddTo(
        \Magento\Framework\Mail\Message $subject,
        \Closure $proceed,
        $email,
        $name = ''
    ) {
        $enabled = $this->hlpConfig->getHijackEnabled();
        if ($enabled) {
            $useDirect = $this->useDirectOrder();
            /* convert input to universal form (see \Zend_Mail::addTo) */
            if (!is_array($email)) {
                $email = array($name => $email);
            }
            /* get developers emails */
            $addrsDev = $this->hlpConfig->getHijackEmails();
            /* replace original emails by developers ones */
            $replaced = [];
            /* compose recipient name from all emails (from one email in the most cases) */
            $allNames = '';
            foreach ($email as $nameOrig => $emailOrig) {
                $part = trim("$nameOrig::$emailOrig");
                $part = str_replace('@', '.at.', $part);
                $allNames .= "$part ";
            }
            $allNames = trim($allNames); // remove trailing space

            /* compose array with developers emails, add original recipients as a name */
            foreach ($addrsDev as $key => $one) {
                $newEmail = trim($one);
                if ($useDirect) {
                    $replaced["_{$key}_{$allNames}"] = $newEmail;
                } else {
                    $replaced[$newEmail] = $allNames;
                }
            }
            /**
             * Result in common case:
             *
             * dev1@mail.com => Customer First::cust1.at.gmail.com Customer Second::cust2.at.mail.com,
             * dev2@mail.com => Customer First::cust1.at.gmail.com Customer Second::cust2.at.mail.com
             */
            /* call parent method with replaced argument */
            $result = $proceed($replaced);
        } else {
            /* call parent method with original arguments */
            $result = $proceed($email, $name);
        }
        return $result;
    }

    /**
     * Use [email => name] or [name => email]
     */
    private function useDirectOrder()
    {
        $result = true;
        $version = $this->metadata->getVersion();
        if ($version >= '2.3.') {
            $result = false;
        }
        return $result;
    }
}