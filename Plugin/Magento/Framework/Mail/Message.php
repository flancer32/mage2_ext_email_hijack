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

    public function __construct(
        \Flancer32\EmailHijack\Helper\Config $hlpConfig
    ) {
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
                $replaced["_{$key}_" . $allNames] = trim($one);
            }
            /**
             * Result in common case:
             *
             * _0_Customer First::cust1.at.gmail.com Customer Second::cust2.at.mail.com => dev1@mail.com,
             * _1_Customer First::cust1.at.gmail.com Customer Second::cust2.at.mail.com => dev2@mail.com
             */
            /* call parent method with replaced argument */
            $result = $proceed($replaced);
        } else {
            /* call parent method with original arguments */
            $result = $proceed($email, $name);
        }
        return $result;
    }
}