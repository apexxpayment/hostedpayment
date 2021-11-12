<?php
namespace Apexx\HostedPayment\Controller\Index;

use \Magento\Framework\App\Action\Context;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Checkout\Model\Session;
use Magento\Sales\Model\OrderRepository;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Psr\Log\LoggerInterface;
use Magento\Sales\Model\Order\Payment\Transaction\Builder;
use Apexx\HostedPayment\Helper\InvoiceGenerate as CustomInvoice;
use Magento\Framework\Session\SessionManagerInterface;
use Apexx\Base\Helper\Logger\Logger as CustomLogger;

/**
 * Class Response
 * @package Apexx\HostedPayment\Controller\Index
 */
class Response extends \Magento\Framework\App\Action\Action implements CsrfAwareActionInterface
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $pageFactory;

    /**
     * @var Http
     */
    protected $request;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var Session
     */
    protected $checkoutSession;

    /**
     * @var UrlInterface
     */
    protected $urlInterface;

    /**
     * @var OrderRepository
     */
    protected $orderRepository;

    /**
     * @var OrderInterface
     */
    protected $order;

    /**
     * @var OrderFactory
     */
    protected $orderFactory;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Builder
     */
    private $transactionBuilder;

    /**
     * @var CustomInvoice
     */
    protected $customInvoice;

    /**
     * @var SessionManagerInterface
     */
    protected $sessionManager;

    /**
     * @var CustomLogger
     */
    protected $customLogger;

    /**
     * @var OrderSender
     */
    protected $orderSender;

   /**
     * Response constructor.
     * @param Context $context
     * @param PageFactory $pageFactory
     * @param RedirectFactory $resultRedirectFactory
     * @param Http $request
     * @param ManagerInterface $messageManager
     * @param UrlInterface $urlInterface
     * @param Session $checkoutSession
     * @param OrderRepository $orderRepository
     * @param OrderInterface $order
     * @param OrderFactory $orderFactory
     * @param LoggerInterface $logger
     * @param Builder $transactionBuilder
     * @param CustomInvoice $customInvoice
     * @param SessionManagerInterface $sessionManager
     * @param CustomLogger $customLogger
     * @param OrderSender $orderSender
     */
    public function __construct(
        Context $context,
        PageFactory $pageFactory,
        RedirectFactory $resultRedirectFactory,
        Http $request,
        ManagerInterface $messageManager,
        UrlInterface $urlInterface,
        Session $checkoutSession,
        OrderRepository $orderRepository,
        OrderInterface $order,
        OrderFactory $orderFactory,
        LoggerInterface $logger,
        Builder $transactionBuilder,
        CustomInvoice $customInvoice,
        SessionManagerInterface $sessionManager,
        CustomLogger $customLogger,
        OrderSender $orderSender
    )
    {
        parent::__construct($context);
        $this->pageFactory = $pageFactory;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->request = $request;
        $this->messageManager = $messageManager;
        $this->checkoutSession = $checkoutSession;
        $this->urlInterface = $urlInterface;
        $this->orderRepository = $orderRepository;
        $this->order           = $order;
        $this->orderFactory = $orderFactory;
        $this->logger = $logger;
        $this->transactionBuilder = $transactionBuilder;
        $this->customInvoice = $customInvoice;
        $this->sessionManager = $sessionManager;
        $this->customLogger = $customLogger;
        $this->orderSender = $orderSender;

    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        try {
            $post = $this->request->getPostValue();
            if ($post) {
                $response = $this->request->getParams();
                $this->customLogger->debug('Hostedpayment Success Response:', $response);
                $transactionId = $response['_id'];
                $status=$this->request->getParam("status");
                $reason_message=$this->request->getParam("reason_message");
                $amount = $this->request->getParam("amount");
                $total = ((int)$amount / 100);
                $expiry_month = $this->request->getParam("expiry_month");
                $expiry_year = $this->request->getParam("expiry_year");
                $card_number = $this->request->getParam("card_number");
                $incrId = $this->request->getParam("merchant_reference");
                $authorization_code = $this->request->getParam("authorization_code");
                //$order = $this->checkoutSession->getLastRealOrder();
                //$orderObj = $this->orderRepository->get($order->getId());

                $order = $this->order->loadByIncrementId($incrId);
                //$orderId = $order->getId();
                $orderObj = $this->orderRepository->get($order->getId());
                /** @var \Magento\Sales\Model\Order\Payment $payment */
                $payment = $order->getPayment();

                if ($status == 'AUTHORISED') {

                    $payment->setLastTransId($transactionId);
                    $payment->setParentTransactionId(null);
                    $payment->setCcType('VI');
                    $payment->setCcExpMonth($expiry_month);
                    $payment->setCcExpYear($expiry_year);
                    $payment->setCcNumberEnc($card_number);
                    $payment->setCcLast4(substr($card_number, -4));
                    $payment->setCcApproval($authorization_code);
                    // Set Response into sales_order_payment table
                    if (isset($response['reason_code'])) {
                        $payment->setAdditionalInformation('reason_code', $response['reason_code']);
                    }
                    if (isset($response['_id'])) {
                        $payment->setAdditionalInformation('_id', $response['_id']);
                    }
                    if (isset($response['authorization_code'])) {
                        $payment->setAdditionalInformation('authorization_code', $response['authorization_code']);
                    }
                    if (isset($response['merchant_reference'])) {
                        $payment->setAdditionalInformation('merchant_reference', $response['merchant_reference']);
                    }
                    if (isset($response['amount'])) {
                        $payment->setAdditionalInformation('amount', $response['amount']);
                    }
                    if (isset($response['status'])) {
                        $payment->setAdditionalInformation('status', $response['status']);
                    }
                    if (isset($response['card_number'])) {
                        $payment->setAdditionalInformation('card_number', $response['card_number']);
                    }
                    if (isset($response['expiry_month'])) {
                        $payment->setAdditionalInformation('expiry_month', $response['expiry_month']);
                    }
                    if (isset($response['expiry_year'])) {
                        $payment->setAdditionalInformation('expiry_year', $response['expiry_year']);
                    }

                    $transaction = $this->transactionBuilder->setPayment($payment)
                        ->setOrder($order)
                        ->setTransactionId($transactionId)
                        ->addAdditionalInformation('raw_details_info', $response)
                        ->setFailSafe(true)
                        ->build('authorization');
                    $transaction->setIsClosed(false);

                    $payment->addTransactionCommentsToOrder($transaction, __('Authorized amount of %1.', $order->getBaseCurrency()->formatTxt($total)));

                    // $order->setStatus('processing');
                    $order->setStatus('authorised');
                    $order->setState('processing');
                    if (!$order->getEmailSent()) {
                        $this->orderSender->send($order);
                    }
                    $payment->save();
                    $order->save();
                    $transaction->save();

                    $redirectUrl = $this->urlInterface->getUrl() . 'checkout/onepage/success';
                    $resultPage = $this->pageFactory->create();
                    $block = $resultPage->getLayout()
                        ->createBlock('Magento\Framework\View\Element\Template')
                        ->setTemplate('Apexx_HostedPayment::redirect.phtml')
                        ->setData('redirectUrl',$redirectUrl)
                        ->toHtml();
                    $this->getResponse()->setBody($block);

                } elseif ($status == 'CAPTURED') {
                    $payment->setLastTransId($transactionId);
                    $payment->setTransactionId($transactionId);
                    $payment->setIsTransactionClosed(true);
                    $payment->setCcType('VI');
                    $payment->setCcExpMonth($expiry_month);
                    $payment->setCcExpYear($expiry_year);
                    $payment->setCcNumberEnc($card_number);
                    $payment->setCcLast4(substr($card_number, -4));
                    $payment->setCcApproval($authorization_code);
                    // Set Response into sales_order_payment table
                    if (isset($response['reason_code'])) {
                        $payment->setAdditionalInformation('reason_code', $response['reason_code']);
                    }
                    if (isset($response['_id'])) {
                        $payment->setAdditionalInformation('_id', $response['_id']);
                    }
                    if (isset($response['authorization_code'])) {
                        $payment->setAdditionalInformation('authorization_code', $response['authorization_code']);
                    }
                    if (isset($response['merchant_reference'])) {
                        $payment->setAdditionalInformation('merchant_reference', $response['merchant_reference']);
                    }
                    if (isset($response['amount'])) {
                        $payment->setAdditionalInformation('amount', $response['amount']);
                    }
                    if (isset($response['status'])) {
                        $payment->setAdditionalInformation('status', $response['status']);
                    }
                    if (isset($response['card_number'])) {
                        $payment->setAdditionalInformation('card_number', $response['card_number']);
                    }
                    if (isset($response['expiry_month'])) {
                        $payment->setAdditionalInformation('expiry_month', $response['expiry_month']);
                    }
                    if (isset($response['expiry_year'])) {
                        $payment->setAdditionalInformation('expiry_year', $response['expiry_year']);
                    }

                    $transaction = $this->transactionBuilder->setPayment($payment)
                        ->setOrder($order)
                        ->setTransactionId($transactionId)
                        ->addAdditionalInformation('raw_details_info', $response)
                        ->setFailSafe(true)
                        ->build('capture');
                    $transaction->setIsClosed(true);

                    $payment->addTransactionCommentsToOrder($transaction, __('Captured amount of %1.', $order->getBaseCurrency()->formatTxt($total)));
                    
                    if (!$order->getEmailSent()) {
                        $this->orderSender->send($order);
                    }
                    $payment->save();
                    $order->save();
                    $transaction->save();

                    $this->customInvoice->createInvoice($order->getId(), $total,$transactionId);

                    $redirectUrl = $this->urlInterface->getUrl() . 'checkout/onepage/success';
                    $resultPage = $this->pageFactory->create();
                    $block = $resultPage->getLayout()
                        ->createBlock('Magento\Framework\View\Element\Template')
                        ->setTemplate('Apexx_HostedPayment::redirect.phtml')
                        ->setData('redirectUrl',$redirectUrl)
                        ->toHtml();
                    $this->getResponse()->setBody($block);
                } else {

                    $payment->setLastTransId($transactionId);
                    $payment->setTransactionId($transactionId);
                    // $transaction = $this->transactionBuilder->setPayment($payment)
                    //     ->setOrder($order)
                    //     ->setTransactionId($transactionId)
                    //     ->addAdditionalInformation('raw_details_info', $response)
                    //     ->setFailSafe(true)
                    //     ->build('void');
                    // $transaction->setIsClosed(true);

                    // $payment->addTransactionCommentsToOrder($transaction, __('Canceled order online %1.', $order->getBaseCurrency()->formatTxt($total)));

                    // $this->cancelTransactionOrder();
                    if(isset($response['status'])){
                        $orderStatus = strtolower($response['status']);
                        $order->setStatus($orderStatus);
                        
                        $order->save();
                    }
                    if(isset($response['message'])){
                        $response['hostedfailure']['status']= 'failed';
                        $response['hostedfailure']['reason_message']= $response['message'];
                    }
                    $payment->save();
                  //  $transaction->save();

                    $this->setHostedFailureMessage($response,$order->getIncrementId());

                    $redirectUrl = $this->urlInterface->getUrl() . 'apexxhosted/payment/failure';
                    $resultPage = $this->pageFactory->create();
                    $block = $resultPage->getLayout()
                        ->createBlock('Magento\Framework\View\Element\Template')
                        ->setTemplate('Apexx_HostedPayment::redirect.phtml')
                        ->setData('redirectUrl',$redirectUrl)
                        ->toHtml();
                    $this->getResponse()->setBody($block);
                }
            }else{
                    $response['hostedfailure']['status']= 'failed';
                    $response['hostedfailure']['reason_message']= "Wrong Data";
                    $this->setHostedFailureMessage($response,'Wrong data - No Order');
                    $redirectUrl = $this->urlInterface->getUrl() . 'apexxhosted/payment/failure';
                    $resultPage = $this->pageFactory->create();
                    $block = $resultPage->getLayout()
                        ->createBlock('Magento\Framework\View\Element\Template')
                        ->setTemplate('Apexx_HostedPayment::redirect.phtml')
                        ->setData('redirectUrl',$redirectUrl)
                        ->toHtml();
                    $this->getResponse()->setBody($block);

            }

        } catch (\Exception $e){
            $this->logger->critical($e);
        }

    }

    /**
     * @param RequestInterface $request
     * @return InvalidRequestException|null
     */
    public function createCsrfValidationException(
        RequestInterface $request
    ): ?InvalidRequestException {
        return null;
    }

    /**
     * @param RequestInterface $request
     * @return bool|null
     */
    public function validateForCsrf(RequestInterface $request): ? bool
    {
        return true;
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function cancelTransactionOrder() {
        $this->cancelCurrentOrder('');
        //$this->restoreQuote();
    }

    /**
     * @param $comment
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function cancelCurrentOrder($comment)
    {
        $order = $this->checkoutSession->getLastRealOrder();
        if ($order->getId() && $order->getState() != \Magento\Sales\Model\Order::STATE_CANCELED) {
            $order->registerCancellation($comment)->save();
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public function restoreQuote()
    {
        return $this->checkoutSession->restoreQuote();
    }

    /**
     * @param $response
     * @param $orderid
     */
    public function setHostedFailureMessage($response,$orderid)
    {
        $this->sessionManager->start();
        $this->sessionManager->setData('hostedfailure',$response);
        $this->sessionManager->setData('orderid',$orderid);
    }
}
