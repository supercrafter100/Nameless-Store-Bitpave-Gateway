<?php
/**
 * Bitpave_Gateway class
 *
 * @package Modules\Store
 * @author Supercrafter100
 * @version 2.0.2
 */
class Bitpave_Gateway extends GatewayBase {

    public function __construct()
    {
        $name = 'Bitpave';
        $settings = ROOT_PATH . '/modules/Store/gateways/Bitpave/gateway_settings/settings.php';
        $author = '<a href="https://github.com/supercrafter100/" target="_blank" rel="nofollow noopener">Supercrafter100</a>';
        $gateway_version = '1.5.2';
        $store_version = '1.5.2';

        parent::__construct($name, $author, $gateway_version, $store_version, $settings);
    }

    public function onCheckoutPageLoad(TemplateBase $template, Customer $customer): void
    {
        // Not necessary
    }

    public function processOrder(Order $order): void
    {
        $client_id = StoreConfig::get('bitpave/client');
        $client_secret = StoreConfig::get('bitpave/secret');
        $wallet = StoreConfig::get('bitpave/wallet');
        $name = Output::getClean(SITE_NAME);
        $custom_data = json_encode(['invoiceNumber' => $order->data()->id]);
        $currentURL = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

        $response = HttpClient::post('https://bitpave.com/api/checkout/create', [
            'client' => $client_id,
            'client_secret' => $client_secret,
            'name' => $name,
            'wallet' => $wallet,
            'price' => Store::fromCents($order->getAmount()->getTotalCents()),
            'custom_data' => $custom_data,
            'success_url' => '$currentURL/store/process/gateway=Bitpave&do=success',
            'cancel_url' => '$currentURL/store/process/gateway=Bitpave&do=cancel',
            'callback_url' => '$currentURL/store/process/gateway=Bitpave'
        ]);

        if ($response->hasError()) {
            ErrorHandler::logCustomError($response->getError());
            return;
        }

        $json = $response->json();
        Redirect::to($json->checkout_url);
    }

    public function handleReturn(): bool
    {
        if (isset($_GET['do']) && $_GET['do'] == 'success') {
            return true;
        }
        return false;
    }

    public function handleListener(): void
    {
        $bodyReceived = file_get_contents('php://input');
        $response = json_decode($bodyReceived);

        if (is_dir(ROOT_PATH . '/cache/bitpave_logs/')) {
            file_put_contents(ROOT_PATH . '/cache/bitpave_logs/'. $this->getName() . '_' .$response->status.'_'.date('U').'.txt', $bodyReceived);
        }
        $client_secret = StoreConfig::get('bitpave/secret');
        if ($response->signature == $client_secret) {
            switch($response->status) {
                case 'completed':
                    $data = json_decode($response->custom_data);
                    $order = $response->session->session;
                    $payment = new Payment($data->invoiceNumber, 'payment_id');
                    $payment->handlePaymentEvent('COMPLETED', [
                        'order_id' => $data->invoiceNumber,
                        'gateway_id' => $this->getId(),
                        'payment_id' => $order,
                        'transaction' => $order,
                        'amount_cents' => Store::toCents($response->price_usd),
                        'currency' => 'USD',
                    ]);
                    break;
            }
        } else {
            http_response_code(400);
            ErrorHandler::logCustomError('Invalid signature from bitpave gateway!');
            return;
        }

    }
}

$gateway = new Bitpave_Gateway();
