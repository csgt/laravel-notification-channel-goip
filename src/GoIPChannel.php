<?php
namespace NotificationChannels\GoIP;

use Exception;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Notifications\Events\NotificationFailed;
use NotificationChannels\GoIP\Exceptions\CouldNotSendNotification;

class GoIPChannel
{
    /**
     * @var GoIP
     */
    protected $GoIP;

    /**
     * @var Dispatcher
     */
    protected $events;

    /**
     * GoIPChannel constructor.
     *
     * @param GoIP     $GoIP
     * @param Dispatcher $events
     */
    public function __construct(GoIP $GoIP, Dispatcher $events)
    {
        $this->GoIP   = $GoIP;
        $this->events = $events;
    }

    /**
     * Send the given notification.
     *
     * @param  mixed                                  $notifiable
     * @param  \Illuminate\Notifications\Notification $notification
     * @return mixed
     * @throws CouldNotSendNotification
     */
    public function send($notifiable, Notification $notification)
    {
        try {
            $to      = $this->getTo($notifiable);
            $message = $notification->toGoIP($notifiable);
            if (is_string($message)) {
                $message = new GoIPMessage($message);
            }
            if (!$message instanceof GoIPMessage) {
                throw CouldNotSendNotification::invalidMessageObject($message);
            }

            return $this->GoIP->sendMessage($message, $to);
        } catch (Exception $exception) {
            $this->events->fire(
                new NotificationFailed($notifiable, $notification, 'GoIP', ['message' => $exception->getMessage()])
            );
        }
    }

    /**
     * Get the address to send a notification to.
     *
     * @param mixed $notifiable
     * @return mixed
     * @throws CouldNotSendNotification
     */
    protected function getTo($notifiable)
    {
        if ($notifiable->routeNotificationFor('GoIP')) {
            return $notifiable->routeNotificationFor('GoIP');
        }
        if (isset($notifiable->celular)) {
            return $notifiable->celular;
        }
        throw CouldNotSendNotification::invalidReceiver();
    }

    /**
     * Get the alphanumeric sender.
     *
     * @param $notifiable
     * @return mixed|null
     * @throws CouldNotSendNotification
     */
    protected function canReceiveAlphanumericSender($notifiable)
    {
        return false;
    }
}
