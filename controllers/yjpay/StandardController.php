<?php
/* 
 * 易极付
 */
namespace kaykay012\paymentsk\controllers\yjpay;

use fecshop\app\appfront\modules\AppfrontController;
use Yii;

/**
 * Description of StandardController
 *
 * @author kai cui <kaykay012@sina.cn>
 */
class StandardController extends AppfrontController{
    public $enableCsrfValidation = false;
    public $PAYMENT_METHOD = 'yjpay_standard';
    public $gatewayUrl='https://openapiglobal.yiji.com/gateway.html';
    public $notifyUrl;
    public $returnUrl;
    public $endReturnURL;
    
    protected $partnerId;
    protected $signature;
    protected $_order;
    protected $currentOrderInfo;

    public function init() {
        parent::init();
        
        $this->currentOrderInfo = Yii::$service->order->getCurrentOrderInfo();
        
        $payment_method = $this->PAYMENT_METHOD;
        Yii::$service->payment->setPaymentMethod($payment_method);
        
        $this->gatewayUrl = Yii::$service->payment->getStandardWebscrUrl();
        $this->notifyUrl = Yii::$service->payment->getStandardIpnUrl();
        $this->returnUrl = Yii::$service->payment->getStandardReturnUrl();
        $this->partnerId = Yii::$service->payment->getStandardAccount();
        $this->signature = Yii::$service->payment->getStandardSignature();
    }
    /**
     * 在网站下单页面，选择支付宝支付方式后，
     * 跳转到支付宝支付页面前准备的部分。
     */
    public function actionStart()
    {
//        echo "<pre>";        
//        echo $payment_method = Yii::$service->payment->paypal->standard_payment_method;
//        exit;

        $currentOrderInfo = $this->currentOrderInfo;
        foreach($currentOrderInfo['products'] as $goods){
            $goodsInfoList[] = array(
                'goodsNumber' => $goods['sku'], //货号
                'goodsName' => $goods['name'], //货物名称
                'goodsCount' => (string) $goods['qty'], //货物数量
                'itemSharpProductcode' => 'Product Category', //商品分类
                'itemSharpUnitPrice' => (string) $goods['price'], //商品单价
            );
        }
        
        //账单、收货等其它信息
        $orderDetail = array(
            'ipAddress' => empty($_SERVER['REMOTE_ADDR']) ? '' : $_SERVER['REMOTE_ADDR'], //IP地址
            
            'billtoCountry'         => $currentOrderInfo['customer_address_country'], //账单国家
            'billtoState'           => $currentOrderInfo['customer_address_country'], //账单州
            'billtoCity'            => $currentOrderInfo['customer_address_city'], //账单城市
            'billtoStreet'          => $currentOrderInfo['customer_address_street1'], //账单街道
            'billtoFirstname'       => $currentOrderInfo['customer_lastname'], //接收账单人员姓
            'billtoLastname'        => $currentOrderInfo['customer_firstname'], //接收账单人员名
            'billtoEmail'           => $currentOrderInfo['customer_email'], //账单邮箱
            'billtoPhonenumber'     => $currentOrderInfo['customer_telephone'], //账单电话
            'billtoPostalcode'      => $currentOrderInfo['customer_address_zip'], //账单邮编
            
            'shiptoCountry'         => $currentOrderInfo['customer_address_country'], //收货国家
            'shiptoState'           => $currentOrderInfo['customer_address_country'], //收货州
            'shiptoCity'            => $currentOrderInfo['customer_address_city'], //收货城市
            'shiptoStreet'          => $currentOrderInfo['customer_address_street1'], //收货街道
            'shiptoFirstname'       => $currentOrderInfo['customer_lastname'], //收货人姓
            'shiptoLastname'        => $currentOrderInfo['customer_firstname'], //收货人名
            'shiptoEmail'           => $currentOrderInfo['customer_email'], //收货邮箱
            'shiptoPhonenumber'     => $currentOrderInfo['customer_telephone'], //收货电话
            'shiptoPostalcode'      => $currentOrderInfo['customer_address_zip'], //收货邮编
            
            'logisticsFee'          => (string) $currentOrderInfo['shipping_total'], //物流费
            'logisticsMode'         => 'EMS', //物流方式
            'cardType'              => 'Visa', //卡类型
            'customerEmail'         => $currentOrderInfo['customer_email'], //购买者邮箱
            'customerPhonenumber'   => $currentOrderInfo['customer_telephone'], //购买者电话
            // 'merchantEmail' => 'jeremyck@gmail.com', //商户邮箱
            // 'merchantName' => '', //商户名
            // 'addressLine1' => '',	//卡地址1
            // 'addressLine2' => ''	//卡地址2
        );

        $data = array(
            //基本参数
            'orderNo'       => $currentOrderInfo['increment_id'] . date('His'), // order log_id
            'merchOrderNo'  => $currentOrderInfo['increment_id'], // order_sn
            'service'       => 'espOrderPay',
            'notifyUrl'     => $this->notifyUrl,
            'returnUrl'     => $this->returnUrl,
            'signType'      => 'MD5',
            'partnerId'     => $this->partnerId,
            //业务参数
            'goodsInfoList' => json_encode($goodsInfoList), //商品列表
            'orderDetail'   => json_encode($orderDetail), //订单扩展信息	
            'userId'        => $this->partnerId, //
            'currency'      => $currentOrderInfo['order_currency_code'], //原始订单币种
            'amount'        => (string) $currentOrderInfo['grand_total'],
            'webSite'       => $_SERVER['SERVER_NAME'], //所属网站
            'deviceFingerprintId'   => md5($currentOrderInfo['increment_id']), //设备指纹
            'acquiringType'         => 'CRDIT', //收单类型,CRDIT：信用卡；YANDEX： 网银方式
            'endReturnURL'          => $this->returnUrl,
        );

        //按参数名排序
        ksort($data);

        $signSrc = "";
        foreach ($data as $k => $v) {
            if (empty($v) || $v === "")
                unset($data[$k]);
            else
                $signSrc.= $k . '=' . $v . '&';
        }
        $signSrc = trim($signSrc, '&') . $this->signature;

        $data['sign'] = md5($signSrc);
        
        $html = '<form name="paymentsubmit" class="clickpay" method="POST" action="'.$this->gatewayUrl.'" >';
        foreach ($data as $key => $val) {
            $val = str_replace("'","&apos;",$val);
            $html .= "<input type='hidden' name='".$key."' value='".$val."'/>";
        }
        $html .= "<input type='submit' value='ok' style='display:none;''></form>";
//        echo $html;
//        exit;
        $html .= "<script>document.forms['paymentsubmit'].submit();</script>";
        
        return '支付跳转中...' . $html;
    }
    /**
     * 支付完成后，跳转返回 fec-shop 的部分
     */
    public function actionReview()
    {
        $status = isset($_GET['status']) ? $_GET['status'] : '';
        $reviewStatus = $status==='success';
        if($reviewStatus){
            $successRedirectUrl = Yii::$service->payment->getStandardSuccessRedirectUrl();
            return Yii::$service->url->redirect($successRedirectUrl);
        }else{
            /*
            $innerTransaction = Yii::$app->db->beginTransaction();
		try {
            if(Yii::$service->order->cancel()){
                $innerTransaction->commit();
            }else{
                $innerTransaction->rollBack();
            }
		} catch (Exception $e) {
			$innerTransaction->rollBack();
		}
            return Yii::$service->url->redirectByUrlKey('checkout/onepage');
             */
            
            echo Yii::$service->helper->errors->get('<br/>');
            return;
        }
    }
    /**
     * IPN，消息接收部分
     */
    public function actionIpn()
    {
        \Yii::info('yjpay ipn begin', 'fecshop_debug');
       
        $post = Yii::$app->request->post();
        if (is_array($post) && !empty($post)) {
            \Yii::info('', 'fecshop_debug');
            $post = \Yii::$service->helper->htmlEncode($post);
            ob_start();
            ob_implicit_flush(false);
            var_dump($post);
            $post_log = ob_get_clean();
            \Yii::info($post_log, 'fecshop_debug');
            $ipnStatus = $this->respond($post);
            if($ipnStatus){
                echo 'success';
                return;
            }
        }
    }
    
    /*
    public function actionCancel()
    {
        $innerTransaction = Yii::$app->db->beginTransaction();
		try {
            if(Yii::$service->order->cancel()){
                $innerTransaction->commit();
            }else{
                $innerTransaction->rollBack();
            }
		} catch (Exception $e) {
			$innerTransaction->rollBack();
		}
        return Yii::$service->url->redirectByUrlKey('checkout/onepage');
    }
    */
    
   protected function respond($post)
   {
        $payment_method = $this->PAYMENT_METHOD;
        Yii::$service->payment->setPaymentMethod($payment_method);
        
        // 验证加密
        $post_sign = $post['sign'];
        unset($post['sign']);
        $data = $post;
        ksort($data);
        $signSrc = "";
        foreach ($data as $k => $v) {
            if (empty($v) || $v === ""){
                unset($data[$k]);
            }
            else{
                $signSrc.= $k . '=' . $v . '&';
            }
        }
        $signSrc = trim($signSrc, '&') . $this->signature;
        $_sign = md5($signSrc);

        $status = $post['status'];
        
        // 如果支付成功(成功||预授权)
        if ($post['resultCode'] === 'EXECUTE_SUCCESS' && $status === 'success' && $_sign === $post_sign) {
            return $this->paymentSuccess($post['merchOrderNo'], '');
        }
        
        return false;
   }
   
   /**
    * 订单支付成功后，需要更改订单支付状态等一系列的处理。
    * @param type $increment_id 订单号
    * @param type $trade_no 支付平台交易流水号
    * @param type $sendEmail 是否发送邮件
    * @return boolean
    */
   protected function paymentSuccess($increment_id,$trade_no,$sendEmail = true)
    {
        Yii::$service->store->currentLangCode = 'zh';
        if (!$this->_order) {
            $this->_order = Yii::$service->order->getByIncrementId($increment_id);
            Yii::$service->payment->setPaymentMethod($this->_order['payment_method']);
        }
        // 【优化后的代码 ##】
        $orderstatus = Yii::$service->order->payment_status_confirmed;
        $updateArr['order_status']  = $orderstatus;
        $updateArr['txn_id']        = $trade_no;
        $updateColumn = $this->_order->updateAll(
            $updateArr,
            [
                'and',
                ['order_id' => $this->_order['order_id']],
                ['in','order_status',$this->_allowChangOrderStatus]
            ]
        );
        if (!empty($updateColumn)) {
            // 发送邮件，以及其他的一些操作（订单支付成功后的操作）
            Yii::$service->order->orderPaymentCompleteEvent($this->_order['increment_id']);
        }
         // 【优化后的代码 ##】
        
        /* 注释掉的原来代码，上面进行了优化，保证更改只有一次，这样发邮件也就只有一次了
        // 如果订单状态已经是processing，那么，不需要更改订单状态了。
        if ($this->_order['order_status'] == Yii::$service->order->payment_status_confirmed){
            
            return true;
        }
        $order = $this->_order;        
        if (isset($order['increment_id']) && $order['increment_id']) {
            // 如果支付成功，则更改订单状态为支付成功
            $order->order_status = Yii::$service->order->payment_status_confirmed;
            $order->txn_id = $trade_no; // 支付宝的交易号
            // 更新订单信息
            $order->save();
            Yii::$service->order->orderPaymentCompleteEvent($order['increment_id']);
            // 上面的函数已经执行下面的代码，因此注释掉。
            // 得到当前的订单信息
            //$orderInfo = Yii::$service->order->getOrderInfoByIncrementId($order['increment_id']);
            // 发送新订单邮件
            //Yii::$service->email->order->sendCreateEmail($orderInfo);
        
            return true;
        }
        */
        return true;
    }
}
