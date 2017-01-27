<?php
/**
 * Created by Sergey Gorodnichev
 * @email sergey.gor@livetex.ru
 */

namespace RxResque\Rx\Observable;

use Rx\Disposable\CompositeDisposable;
use Rx\Disposable\EmptyDisposable;
use Rx\Observable;
use Rx\ObservableInterface;
use Rx\ObserverInterface;
use Rx\Subject\Subject;

class PausableObservable extends Observable
{
    /** @var Observable */
    protected $source;

    /** @var Subject */
    protected $pauser;

    /** @var bool */
    protected $paused;

    public function  __construct(ObservableInterface $source, Subject $pauser)
    {
        $this->source = $source;
        $this->pauser = $pauser;
        $this->paused = true;
    }

    public function subscribe(ObserverInterface $observer, $scheduler = null)
    {
        $disposableEmpty = new EmptyDisposable();
        $published = $this->source->publish();
        $subscription = $published->subscribe($observer, $scheduler);
        $connection = $disposableEmpty;

        $pausable = $this->pauser
            ->distinctUntilChanged()
            ->subscribeCallback(
                function ($value) use ($published, &$connection, $disposableEmpty) {
                    if ($value) {
                        $connection = $published->connect();
                    } else {
                        $connection->dispose();
                        $connection = $disposableEmpty;
                    }
                }
            );

        return new CompositeDisposable([$subscription, $connection, $pausable]);
    }
}