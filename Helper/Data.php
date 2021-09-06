<?php
/**
 * Custom payment method in Magento 2
 * @category    HostedPayment
 * @package     Apexx_HostedPayment
 */
namespace Apexx\HostedPayment\Helper;

use \Magento\Framework\App\Helper\AbstractHelper;
use \Magento\Framework\App\Helper\Context;
use \Magento\Store\Model\ScopeInterface;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Framework\Encryption\EncryptorInterface ;
use \Magento\Framework\Controller\Result\JsonFactory;
use \Magento\Framework\Serialize\Serializer\Json as SerializeJson;
use \Magento\Framework\HTTP\Adapter\CurlFactory;
use \Magento\Framework\HTTP\Header as HttpHeader;
use \Magento\Sales\Model\OrderRepository;
use Magento\Sales\Api\Data\TransactionInterface;
use Magento\Sales\Api\TransactionRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\FilterBuilder;
use \Psr\Log\LoggerInterface;

/**
 * Class Data
 * @package Apexx\HostedPayment\Helper
 */
class Data extends AbstractHelper
{
    /**
     * Config paths
     */
    const XML_CONFIG_PATH_HOSTEDPAYMENT  = 'payment/hostedpayment_gateway';
    const XML_PATH_PAYMENT_HOSTEDPAYMENT = 'payment/apexx_section/apexxpayment/hostedpayment_gateway';
    const XML_PATH_RETURN_URL = '/redirect_url';
    const XML_PATH_PAYMENT_ACTION = '/payment_action';
    const XML_PATH_DYNAMIC_DESCRIPTOR = '/dynamic_descriptor';
    const XML_PATH_CAPTURE_MODE = '/capture_mode';
    const XML_PATH_PAYMENT_MODES = '/payment_modes';
    const XML_PATH_PAYMENT_TYPE = '/payment_type';
    const XML_PATH_TRANSACTION_TYPE = '/transaction_type';
    const XML_PATH_ALLOW_CURRENCY        = '/allow';
    /**
     * Custom Fields
     */
    const XML_PATH_CARD_HOLDER_NAME = '/card_holder_name';
    const XML_PATH_CARD_ADDRESS = '/address';
    const XML_PATH_CARD_ADDRESS_REQ = '/address_required';
    const XML_PATH_DISPLAY_HORIZONTAL = '/display_horizontal';
    const XML_PATH_EXPIRY_DATE = '/expiry_date';
    const XML_PATH_CVV = '/cvv';
    const XML_PATH_ORDER_SUMMARY = '/order_summary';
    const XML_PATH_CSS_TEMPLATE = '/transaction_css_template';
    /**
     * 3D Secure
     */
    const XML_PATH_3DS_STATUS = '/three_d_status';
    const XML_PATH_3DS_VERSION = '/three_d_version';
    const XML_PATH_WEBHOOK_URL = '/webhook_transaction_update';
    const XML_IFRAME_HEIGHT = '/iframe_height' ;
    const XML_IFRAME_WIDTH = '/iframe_width' ;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var EncryptorInterface
     */
    protected $encryptor;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var SerializeJson
     */
    protected $serializeJson;

    /**
     * @var CurlFactory
     */
    protected $curlFactory;

    /**
     * @var HttpHeader
     */
    protected $httpHeader;

    /**
     * @var OrderRepository
     */
    protected $orderRepository;

    /**
     * @var TransactionRepositoryInterface
     */
    protected $transactionRepository;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchBuilder;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Data constructor.
     * @param Context $context
     * @param ScopeConfigInterface $scopeConfig
     * @param EncryptorInterface $encryptor
     * @param JsonFactory $resultJsonFactory
     * @param SerializeJson $serializeJson
     * @param CurlFactory $curlFactory
     * @param HttpHeader $httpHeader
     * @param OrderRepository $orderRepository
     * @param TransactionRepositoryInterface $transactionRepository
     * @param SearchCriteriaBuilder $searchBuilder
     * @param FilterBuilder $filterBuilder
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig,
        EncryptorInterface $encryptor,
        JsonFactory $resultJsonFactory,
        SerializeJson $serializeJson,
        curlFactory $curlFactory,
        HttpHeader $httpHeader,
        OrderRepository $orderRepository,
        TransactionRepositoryInterface $transactionRepository,
        SearchCriteriaBuilder $searchBuilder,
        FilterBuilder $filterBuilder,
        LoggerInterface $logger
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->encryptor = $encryptor;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->serializeJson = $serializeJson;
        $this->curlFactory = $curlFactory;
        $this->httpHeader = $httpHeader;
        $this->orderRepository = $orderRepository;
        $this->transactionRepository = $transactionRepository;
        $this->searchBuilder = $searchBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->logger = $logger;
    }

    /**
     * @param $key
     * @return mixed
     */
    public function getConfigPathValue($key)
    {
        return $this->scopeConfig->getValue(
            self::XML_CONFIG_PATH_HOSTEDPAYMENT . $key,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get config value at the specified key
     *
     * @param string $key
     * @return mixed
     */
    public function getConfigValue($key)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_PAYMENT_HOSTEDPAYMENT . $key,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return mixed
     */
    public function getReturnUrl()
    {
        return $this->getConfigValue(self::XML_PATH_RETURN_URL);
    }

    /**
     * @return string
     */
    public function getHostedPaymentAction()
    {
        $hostPaymentAction = $this->getConfigPathValue(self::XML_PATH_PAYMENT_ACTION);
        if ($hostPaymentAction == 'authorize') {
            return 'false';
        } else {
            return 'true';
        }
    }

    /**
     * @return mixed
     */
    public function getDynamicDescriptor()
    {
        return $this->getConfigPathValue(self::XML_PATH_DYNAMIC_DESCRIPTOR);
    }

    /**
     * @return string
     */
    public function getThreeDsRequired()
    {
        $threeDReq = $this->getConfigValue(self::XML_PATH_3DS_STATUS);
        if ($threeDReq) {
            return 'true';
        } else {
            return 'false';
        }
    }

    /**
     * @return string
     */
    public function getThreeDsVersion()
    {
        $threeDVersion = $this->getConfigPathValue(self::XML_PATH_3DS_VERSION);
        if ($threeDVersion) {
            return $threeDVersion;
        }else{
            return '1.0';
        }
    }

    /**
     * @return mixed
     */
    public function getCaptureMode()
    {
        return $this->getConfigValue(self::XML_PATH_CAPTURE_MODE);
    }

    /**
     * @return string
     */
    public function getCustomPaymentType()
    {
        return $this->getConfigValue(self::XML_PATH_PAYMENT_TYPE);
    }

    /**
     * @return string
     */
    public function getCardHolderName()
    {
        $cardHolderName = $this->getConfigValue(self::XML_PATH_CARD_HOLDER_NAME);
        if ($cardHolderName) {
            return 'true';
        } else {
            return 'false';
        }
    }

    /**
     * @return string
     */
    public function getCardAddress()
    {
        $cardAddress = $this->getConfigValue(self::XML_PATH_CARD_ADDRESS);
        if ($cardAddress) {
            return 'true';
        } else {
            return 'false';
        }
    }

    /**
     * @return string
     */
    public function getCardAddReq()
    {
        $cardAddReq = $this->getConfigValue(self::XML_PATH_CARD_ADDRESS_REQ);
        if ($cardAddReq) {
            return 'true';
        } else {
            return 'false';
        }
    }

    /**
     * @return string
     */
    public function getDisplayHorizontal()
    {
        $displayHorizontal = $this->getConfigValue(self::XML_PATH_DISPLAY_HORIZONTAL);
        if ($displayHorizontal) {
            return 'true';
        } else {
            return 'false';
        }
    }

    /**
     * @return string
     */
    public function getCardExpiryDate()
    {
        return $this->getConfigValue(self::XML_PATH_EXPIRY_DATE);
    }
    /**
     * @return string
     */
    public function getIframeHeight()
    {
        return $this->getConfigPathValue(self::XML_IFRAME_HEIGHT);
    }
      /**
     * @return string
     */
    public function getIframeWidth()
    {
        return $this->getConfigPathValue(self::XML_IFRAME_WIDTH);
    }

    /**
     * @return string
     */
    public function getCardCvv()
    {
        return $cardCvv = $this->getConfigPathValue(self::XML_PATH_CVV);
    }

    /**
     * @return string
     */
    public function getOrderSummary()
    {
        $threeDReq = $this->getConfigValue(self::XML_PATH_ORDER_SUMMARY);
        if ($threeDReq) {
            return 'true';
        } else {
            return 'false';
        }
    }

    /**
     * @return string
     */
    public function getCssTemplate()
    {
        return $this->getConfigValue(self::XML_PATH_CSS_TEMPLATE);
    }

    /**
     * @return mixed
     */
    public function getTransType()
    {
        return $this->getConfigValue(self::XML_PATH_TRANSACTION_TYPE);
    }

    /**
     * @return mixed
     */
    public function getWebhookUrl()
    {
        return $this->getConfigValue(self::XML_PATH_WEBHOOK_URL);
    }

    /**
     * @param $currency
     * @return array
     */
    public function getAllowPaymentCurrency($currency) {
        $allowCurrencyList = $this->getConfigValue(self::XML_PATH_ALLOW_CURRENCY);
        if (!empty($allowCurrencyList)) {
            $currencyList = explode(",", $allowCurrencyList);
            if (!empty($currencyList)) {
                $currencyInfo = [];
                foreach ($currencyList as $key => $value) {
                    if ($value == $currency) {
                        $currencyInfo['currency_code'] = $value;
                    }
                }

                return $currencyInfo;
            }
        }
    }
}
