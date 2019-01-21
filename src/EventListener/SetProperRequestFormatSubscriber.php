<?php declare(strict_types=1);

namespace App\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

class SetProperRequestFormatSubscriber implements EventSubscriberInterface
{
    /**
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $event->getRequest()->setRequestFormat('json');
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $event->getRequest()->setRequestFormat('json');
    }


    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'kernel.request' => 'onKernelRequest',
            'kernel.exception' => 'onKernelException'
        ];
    }
}
