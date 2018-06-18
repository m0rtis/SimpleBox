<?php
declare(strict_types=1);


namespace m0rtis\SimpleBox;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Class Container
 * @package m0rtis\SimpleBox
 */
class Container implements ContainerInterface, \ArrayAccess, \Iterator, \Countable
{
    /**
     * @var iterable
     */
    protected $data;
    /**
     * @var bool
     */
    private $returnShared;
    /**
     * @var array
     */
    private $retrieved;

    /**
     * Container constructor.
     * @param iterable $data
     */
    public function __construct(iterable $data = [])
    {
        $this->data = $data;

        $config = $data['config'][ContainerInterface::class] ?? [];
        $this->returnShared = (bool)($config['return_shared'] ?? true);
    }

    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @throws NotFoundExceptionInterface  No entry was found for **this** identifier.
     * @throws ContainerExceptionInterface Error while retrieving the entry.
     *
     * @return mixed Entry.
     */
    public function get($id)
    {
        if ($this->has($id)) {
            if ($this->returnShared) {
                return $this->getRetrieved($id);
            }
            return $this->retrieve($id);
        }

        throw new NotFoundException($id);
    }

    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     *
     * `has($id)` returning true does not mean that `get($id)` will not throw an exception.
     * It does however mean that `get($id)` will not throw a `NotFoundExceptionInterface`.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @return bool
     */
    public function has($id): bool
    {
        $id = (string)$id;
        return  isset($this->data[$id])
                ?: $this->isCallable($id)
                ?: (\class_exists($id) && $this->canInstantiate($id));
    }

    /**
     * @param string $id
     * @return mixed
     * @throws ContainerException
     */
    public function create(string $id)
    {
        if (isset($this->retrieved[$id])) {
            $storeData = $this->data[$id];
            $shared = $this->returnShared;
            $this->data[$id] = $this->retrieved[$id];
            $this->returnShared = false;

            $result = $this->get($id);

            $this->data[$id] = $storeData;
            $this->returnShared = $shared;
        } else {
            $result = $this->retrieve($id);
        }
        return $result;
    }

    /**
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     * @since 5.0.0
     */
    public function current()
    {
        return \current($this->data);
    }

    /**
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function next(): void
    {
        \next($this->data);
    }

    /**
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     * @since 5.0.0
     */
    public function key()
    {
        return \key($this->data);
    }

    /**
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     * @since 5.0.0
     */
    public function valid(): bool
    {
        return $this->key() !== null;
    }

    /**
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function rewind(): void
    {
        \reset($this->data);
    }

    /**
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     * @since 5.0.0
     */
    public function offsetExists($offset): bool
    {
        return $this->has($offset);
    }

    /**
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     * @since 5.0.0
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetSet($offset, $value): void
    {
        $this->data[$offset] = $value;
    }

    /**
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetUnset($offset): void
    {
        unset($this->data[$offset]);
    }

    /**
     * Count elements of an object
     * @link http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * </p>
     * <p>
     * The return value is cast to an integer.
     * @since 5.1.0
     */
    public function count(): int
    {
        return \count($this->data);
    }

    /**
     * @param string $className
     * @return bool
     */
    protected function canInstantiate(string $className): bool
    {
        /**
         * Can be overridden in child classes
         */
        return false;
    }

    /**
     * @param string $className
     * @return object
     */
    protected function instantiate(string $className): object
    {
        throw new \RuntimeException('Method Container::instantiate is not allowed and should be overridden');
    }

    /**
     * @param string $id
     * @return mixed
     * @throws ContainerException
     */
    private function resolve(string $id)
    {
        $resolved = $id;
        if (\is_callable($id)) {
            $resolved = $id($this);
        } elseif (isset($this->data[$id])) {
            $resolved = $this->retrieve($id);
        } elseif ($this->canInstantiate($id)) {
            $resolved = $this->instantiate($id);
        }
        return $resolved;
    }

    /**
     * @param string $id
     * @return mixed
     * @throws ContainerException
     */
    private function retrieve(string $id)
    {
        $item = $this->data[$id] ?? $id;
        try {
            if (\is_string($item)) {
                $resolved = $this->resolve($item);
                if ($this->isCallable($resolved)
                    && false !== \stripos($item, 'factory')
                ) {
                    $resolved = $this->call($resolved);
                }
                $item = $resolved;
            } elseif (\is_callable($item)) {
                $item = $this->call($item);
            }
        } catch (\Exception $e) {
            throw new ContainerException($e);
        }

        return $item;
    }

    /**
     * @param string $id
     * @return mixed
     * @throws ContainerException
     */
    private function getRetrieved(string $id)
    {
        if (!isset($this->retrieved[$id]) && isset($this->data[$id])) {
            $this->retrieved[$id] = $this->data[$id];
        }
        $this->data[$id] = $this->retrieve($id);
        return $this->data[$id];
    }

    /**
     * @param mixed $var
     * @return bool
     */
    private function isCallable($var): bool
    {
        return \is_callable($var) || (\is_string($var) && $this->isInvokable($var));
    }

    /**
     * @param $callable
     * @return mixed
     * @throws \RuntimeException
     */
    private function call($callable)
    {
        if (\is_callable($callable)) {
            $result = $callable($this);
        } elseif (\is_string($callable) && $this->isInvokable($callable)) {
            $result = (new $callable())($this);
        } else {
            throw new \RuntimeException(
                sprintf(
                    'Unable to call. The type of given callable is %s',
                    \gettype($callable)
                )
            );
        }
        return $result;
    }

    /**
     * @param string $class
     * @return bool
     */
    private function isInvokable(string $class): bool
    {
        return \class_exists($class) && \method_exists($class, '__invoke');
    }
}
