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
        $published = $this->source->publish();
        $subscription = $published->subscribe($observer);
        $connection = new EmptyDisposable();

        $pausable = $this->pauser
            ->startWith(!$this->paused)
            ->distinctUntilChanged()
            ->subscribeCallback(function ($value) use (&$published, &$connection) {
                if ($value) {
                    $connection = $published->connect();
                } else {
                    $connection->dispose();
                    $connection = new EmptyDisposable();
                }
            });

        return new CompositeDisposable([$subscription, $connection, $pausable]);
    }

}