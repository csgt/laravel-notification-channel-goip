<?php
namespace NotificationChannels\GoIP;

use GuzzleHttp\Psr7;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use NotificationChannels\GoIP\Exceptions\CouldNotSendNotification;

class GoIP
{
    /**
     * @var GoIPConfig
     */
    private $config;

    /**
     * GoIP constructor.
     *
     * @param GoIPConfig   $config
     */
    public function __construct(GoIPConfig $config)
    {
        $this->config = $config;
    }

    /**
     * Send an sms message using the GoIP Service.
     *
     * @param GoIPMessage $message
     * @param string           $to
     * @return \GoIP\MessageInstance
     */
    public function sendMessage(GoIPMessage $message, $to)
    {
        $params = [
            'l' => 1,
            'u' => 'admin',
            'p' => 'admin',
            'n' => $to,
            'm' => trim($message->content),
        ];

        if (!$serviceURL = $this->config->getAccountURL()) {
            throw CouldNotSendNotification::missingURL();
        }
        $cliente = new Client;
        try {
            $response = $cliente->request('GET', $serviceURL, ['query' => $params, 'timeout' => 25, 'verify' => false]);
            $html     = (string) $response->getBody();
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                throw CouldNotSendNotification::errorSending(Psr7\str($e->getResponse()));
            }
            throw CouldNotSendNotification::errorSending($e->getMessage());
        }

        return $response;
    }

}
