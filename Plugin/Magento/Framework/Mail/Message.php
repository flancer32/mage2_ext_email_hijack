<?php
/**
 * Process recipients emails and replace its with developer's emails.
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
            /*
             *  convert input to universal form
             *      - \Zend\Mail\Message::addTo($emailOrAddressOrList, $name = null)
             *          - \Zend\Mail\AddressList::add($emailOrAddress, $name = null)
             * */
            if (!is_array($email)) {
                $email = array($name => $email);
            }
            /* get developers emails */
            $addrsDev = $this->hlpConfig->getHijackEmails();
            /* replace original emails by developers ones */
            $replaced = [];
            /* compose recipient name from all emails (from one email in the most cases) */
            $allNamesAsOne = '';
            foreach ($email as $nameOrig => $emailOrig) {
                $part = trim("$nameOrig::$emailOrig");
                $part = str_replace('@', '.at.', $part);
                $allNamesAsOne .= "$part ";
            }
            $allNamesAsOne = trim($allNamesAsOne); // remove trailing space

            /* compose array with developers emails, add original recipients as a name */
            foreach ($addrsDev as $devEmail) {
                /* starting from M2.3 (Zend2) we need compose [email=>name] array instead of [$name=>$email] */
                $replaced[trim($devEmail)] = trim($allNamesAsOne);
            }
            /**
             * Result in common case:
             *
             * dev1@mail.com => "Customer First::cust1.at.gmail.com Customer Second::cust2.at.mail.com",
             * dev2@mail.com => "Customer First::cust1.at.gmail.com Customer Second::cust2.at.mail.com"
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