<?php

declare(strict_types = 1);

namespace Rush\Network;

use Closure;
use Event;
use EventBase;

/**
 * Class Reactor
 * @package Rush
 */
class Reactor
{
    /**
     * Read Event
     * @var int
     */
    public const READ = Event::READ | Event::PERSIST;

    /**
     * Write Event
     * @var int
     */
    public const WRITE = Event::WRITE | Event::PERSIST;

    /**
     * Signal Event
     * @var int
     */
    public const SIGNAL = Event::SIGNAL | Event::PERSIST;

    /**
     * Timer Event
     * @var int
     */
    public const TIMER = Event::TIMEOUT | Event::PERSIST;

    /**
     * Reactor Instance
     * @var Reactor
     */
    protected static Reactor $instance;

    /**
     * Base Event
     * @var EventBase
     */
    protected EventBase $base;

    /**
     * Listeners Of I/O Event
     * @var array
     */
    protected array $ios = [];

    /**
     * Listeners Of Signal Event
     * @var array
     */
    protected array $signals = [];

    /**
     * Listeners Of Timer Event
     * @var array
     */
    protected array $timers = [];

    /**
     * ID Recorder Of Timer Event
     * @var int
     */
    protected int $timer_recorder = 1;

    /**
     * Reactor constructor
     * @access private
     */
    private function __construct() {}

    /**
     * Reactor cloner
     * @access private
     */
    private function __clone() {}

    /**
     * Get the reactor instance
     * @return Reactor
     */
    public static function getInstance(): Reactor
    {
        if (isset(static::$instance) === false) {
            static::$instance = new static();
            static::$instance->init();
        }

        return static::$instance;
    }

    /**
     * Init base event
     * @return void
     */
    public function init(): void
    {
        $this->base = new EventBase();
    }

    /**
     * Register a event
     * @param mixed $fd Socket resource, signal num or timeout.
     * @param int $what Type of event.
     * @param Closure $cb The function that is emitted when the event is triggered.
     * @param mixed $arg The function arguments, and the first param is fd in i/o event.
     * @return bool|int
     * @throws NetworkException
     */
    public function add(mixed $fd, int $what, Closure $cb, mixed $arg = null): bool|int
    {
        switch ($what) {
            case static::READ:
            case static::WRITE:
            $event = new Event($this->base, $fd, $what, $cb, $arg);
            if ($event->add() === false) {
                return false;
            }

            $this->ios[(int) $fd][$what] = $event;
            return true;
            case static::SIGNAL:
                $event = Event::signal($this->base, $fd, $cb, $arg);
                if ($event->addSignal() === false) {
                    return false;
                }

                $this->signals[$fd] = $event;

                return true;
            case static::TIMER:
                $event = Event::timer($this->base, $cb, $arg);
                if ($event->addTimer($fd) === false) {
                    return 0;
                }

                $this->timers[$this->timer_recorder] = $event;

                return $this->timer_recorder++;
            default:
                throw new NetworkException("Event type is not exist");
        }
    }

    /**
     * Remove a event
     * @param mixed $fd Socket resource, signal num or timeout.
     * @param int $what Type of event.
     * @return bool
     * @throws NetworkException
     */
    public function del(mixed $fd, int $what): bool
    {
        switch ($what) {
            case static::READ:
            case static::WRITE:
                if (isset($this->ios[(int) $fd][$what]) === true) {
                    if ($this->ios[(int) $fd][$what]->del() === false) {
                        return false;
                    }

                    unset($this->ios[(int) $fd][$what]);
                }

                if (empty($this->ios[(int) $fd]) === true) {
                    unset($this->ios[(int) $fd]);
                }

                return true;
            case static::SIGNAL:
                if (isset($this->signals[$fd]) === true) {
                    if ($this->signals[$fd]->del() === false) {
                        return false;
                    }

                    unset($this->signals[$fd]);
                }

                return true;
            case static::TIMER:
                if (isset($this->timers[$fd]) === true) {
                    if ($this->timers[$fd]->del() === false) {
                        return false;
                    }

                    unset($this->timers[$fd]);
                }

                return true;
            default:
                throw new NetworkException("Event type is not exist");
        }
    }

    /**
     * Dispatch event
     * @return void
     */
    public function loop(): void
    {
        $this->base->loop(EventBase::EPOLL_USE_CHANGELIST);
    }

    /**
     * Delete all event and re-init base event
     * @param int $timeout Time to exit event dispatch.
     * @return void
     */
    public function destroy(int $timeout = 0): void
    {
        foreach ($this->ios as $io) {
            foreach ($io as $event) {
                $event->del();
            }
        }

        foreach ($this->signals as $event) {
            $event->del();
        }

        foreach ($this->timers as $event) {
            $event->del();
        }

        $this->ios = $this->signals = $this->timers = [];

        $this->base->exit($timeout);

        $this->base->reInit();
    }
}
