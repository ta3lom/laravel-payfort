<?php


use Moeen\Payfort\Payfort;

class Controller
{
    public $provider;

    public function __construct(Payfort $provider)
    {
        $this->provider = $provider;
    }

    public function getPaymentPage()
    {
        $returnUrl = $this->getUrl('route.php?r=merchantPageReturn');
        if (isset($_GET['3ds']) && $_GET['3ds'] == 'no') {
            $returnUrl = $this->getUrl('route.php?r=merchantPageReturn&3ds=no');
        }

        $this->log('calling: prepareMerchantPageData');

        try {
            $params = $this->provider->prepareMerchantPageData([
                'service_command' => 'TOKENIZATION',
                'merchant_identifier' => $this->provider->merchant_identifier,
                'access_code' => $this->provider->access_code,
                'language' => $this->provider->language,

                'merchant_reference' => (string)rand(0, getrandmax()),
                'token_name' => 'ajar-pay-' . time(),
                'return_url' => $returnUrl,
            ]);

            $gatewayUrl = $this->provider->gateway_url . 'FortAPI/paymentPage';

            $form = $this->getPaymentForm($gatewayUrl, $params);

            $debugMsg = "data for the form \n" . print_r($params, 1);
            $this->log($debugMsg);

            echo json_encode([
                'form' => $form,
                'url' => $gatewayUrl,
                'params' => $params,
                'paymentMethod' => 'cc_merchantpage2',
            ]);
            exit;
        } catch (\Exception $exception) {
            $this->showErrorPage($exception);
        }
    }

    public function getUrl($path)
    {
        $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
        $url = $scheme . $_SERVER['HTTP_HOST'] . '/' . $path;
        return $url;
    }

    public function log($messages)
    {
        $messages = "========================================================\n\n" . $messages . "\n\n";
        $file = __DIR__ . '/trace.log';
        if (filesize($file) > 907200) {
            $fp = fopen($file, "r+");
            ftruncate($fp, 0);
            fclose($fp);
        }

        $myfile = fopen($file, "a+");
        fwrite($myfile, $messages);
        fclose($myfile);
    }

    public function getPaymentForm($gatewayUrl, $postData)
    {
        $debugMsg = "get the payment form \n" . print_r($postData, 1);
        $this->log($debugMsg);

        $form = '<form style="display:none" name="payfort_payment_form" id="payfort_payment_form" method="post" action="' . $gatewayUrl . '">';
        foreach ($postData as $k => $v) {
            $form .= '<input type="hidden" name="' . $k . '" value="' . $v . '">';
        }
        $form .= '<input type="submit" id="submit">';
        return $form;
    }

    public function showErrorPage($e)
    {
        $error_msg = $e->getMessage();

        if (method_exists($e, 'errors')) {
            $this->log('failed validation \n' . print_r($e->errors()));

            $error_msg = 'check the logs for errors details';
        }

        $url = $this->getUrl('error.php?error_msg=' . $error_msg);

        $this->redirectUser($url);
    }

    public function redirectUser(string $url)
    {
        echo "<html><body onLoad=\"javascript: window.top.location.href='" . $url . "'\"></body></html>";
        exit;
    }

    public function merchantPageReturn(array $data)
    {
        $debugMsg = "Fort Merchant Page Response Parameters \n" . print_r($data, 1);
        $this->log($debugMsg);

        try {
            $this->provider->verifyResponse($data);

            $returnUrl = $this->getUrl('route.php?r=processResponse');

            $data = [
                'command' => 'AUTHORIZATION',
                'merchant_identifier' => $this->provider->merchant_identifier,
                'access_code' => $this->provider->access_code,
                'language' => $this->provider->language,
                'merchant_reference' => $data['merchant_reference'],
                'token_name' => $data['token_name'],
                'return_url' => $returnUrl,

                'currency' => 'KWD',
                'amount' => $this->provider->convertAmountToPayfortFormat('20'),
                'customer_name' => 'Moeen Basra',
                'customer_email' => 'test@payfort.com',
                'customer_ip' => '::1',
            ];

            $signature = $this->provider->createSignature($data);

            $data['signature'] = $signature;

            $debugMsg = "Prepared authorizaton data \n" . print_r($data, 1);
            $this->log($debugMsg);

            $data = $this->provider->authorization($data);

            $debugMsg = "Fort authorized data \n" . print_r($data, 1);
            $this->log($debugMsg);

            // verify authorization response
            $this->provider->verifyResponse($data);

            if (!empty($data['3ds_url'])) {
                $this->redirectUser($data['3ds_url']);
            }

            $url = $this->getUrl('success.php?' . http_build_query($data));
            $this->redirectUser($url);

        } catch (\Exception $exception) {
            $this->showErrorPage($exception);
        }
    }

    public function processResponse($data)
    {
        $debugMsg = "final Fort Redirect Response Parameters \n" . print_r($data, 1);
        $this->log($debugMsg);

        try {
            $this->provider->verifyResponse($data);
        } catch (\Exception $exception) {
            $this->showErrorPage($exception);
        }

        $url = $this->getUrl('success.php?' . http_build_query($data));

        $this->redirectUser($url);
    }
}
