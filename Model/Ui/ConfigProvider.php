<?php
/**
 * See LICENSE for license details.
 */

namespace Apexx\HostedPayment\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Apexx\HostedPayment\Helper\Data as Config;
use Magento\Framework\Locale\ResolverInterface;

/**
 * Class ConfigProvider
 */
class ConfigProvider implements ConfigProviderInterface
{
    const CODE = 'hostedpayment_gateway';
    const iFrameHeight = 'iFrameHeight' ;
    const iFrameWidth = 'iFrameWidth' ;
    private $config;

    public function __construct(
      Config $config,
        ResolverInterface $localeResolver
    ) {
    
        $this->config = $config;
    }


    public function getConfig()
    {
        $requestConfig = [
            'payment' => [
                self::CODE => [
                    'iFrameHeight' => $this->config->getIframeHeight(),
                    'iFrameWidth' => $this->config->getIframeWidth()
                ]
            ]
        ];

        return $requestConfig ; 
    }
}

