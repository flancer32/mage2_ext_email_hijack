<?php
/**
 * Process recipients emails and replace its by developer's emails.
 *
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2021
 */

namespace Flancer32\EmailHijack\Plugin\Magento\Framework\Mail\Template;

class TransportBuilder {

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

    public function aroundAddBcc(
        \Magento\Framework\Mail\Template\TransportBuilder $subject,
        \Closure $proceed,
        $address
    ) {
        $enabled = $this->hlpConfig->getHijackEnabled();
        if ($enabled) {
            // skip processing
            return $subject;
        } else {
            return $proceed($address);
        }
    }

    public function aroundAddCc(
        \Magento\Framework\Mail\Template\TransportBuilder $subject,
        \Closure $proceed,
        $address,
        $name = ''
    ) {
        $enabled = $this->hlpConfig->getHijackEnabled();
        if ($enabled) {
            // skip processing
            return $subject;
        } else {
            return $proceed($address, $name);
        }
    }

    public function aroundAddTo(
        \Magento\Framework\Mail\Template\TransportBuilder $subject,
        \Closure $proceed,
        $address,
        $name = ''
    ) {
        $enabled = $this->hlpConfig->getHijackEnabled();
        if ($enabled) {
            /* get developers emails */
            $addrsDev = $this->hlpConfig->getHijackEmails();
            if (\count($addrsDev) > 0) {
                /* use original address as name for dev. address: Customer First::cust1.at.gmail.com */
                $devName = trim("$name::$address");
                $devName = str_replace('@', '.at.', $devName);
                foreach ($addrsDev as $devAddr) {
                    $result = $proceed($devAddr, $devName);
                }
            } else {
                /* call parent method with original arguments */
                $result = $proceed($address, $name);
            }
        } else {
            /* call parent method with original arguments */
            $result = $proceed($address, $name);
        }
        return $result;
    }

    /**
     * Use [email => name] or [name => email]
     */
    private function useDirectOrder() {
        $result = true;
        $version = $this->metadata->getVersion();
        if ($version >= '2.3.') {
            $result = false;
        }
        return $result;
    }
}
