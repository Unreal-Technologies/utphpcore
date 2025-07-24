<?php

namespace Utphpcore\Data\Collections;

class Linq implements ILinq
{
    private const WHERE = 1;
    private const SELECT = 2;
    private const GROUPBY = 4;
    private const SUM = 8;
    private const AVG = 16;
    private const ORDERBY = 32;
    private const SKIP = 64;

    /**
     * @var array
     */
    private array $aInCollection;

    /**
     * @var array
     */
    private array $aOutCollection = [];

    /**
     * @var array
     */
    private array $aQuery = [];

    /**
     * @var bool
     */
    private bool $bIsGrouped = false;

    /**
     * @var int[]
     */
    private array $aCounts = [];

    /**
     * @param array $collection
     */
    public function __construct(array $collection)
    {
        $this -> aInCollection = $collection;
    }

    /**
     * @param int $count
     * @return ILinq
     */
    #[\Override]
    public function skip(int $count): ILinq
    {
        $this -> aQuery[] = [$this::SKIP, $count];
        return $this;
    }

    /**
     * @param \Closure $lambda
     * @return Linq
     */
    #[\Override]
    public function where(\Closure $lambda): Linq
    {
        $count = count($this -> aQuery);
        $index = $count - 1;
        if ($count > 0 && $this -> aQuery[$index][0] == $this::WHERE) {
            $l1 = $this -> aQuery[$index][1];
            $this -> aQuery[$index] = [$this::WHERE, function ($x) use ($l1, $lambda) {
                return $l1($x) && $lambda($x);
            }];
        } else {
            $this -> aQuery[] = [$this::WHERE, $lambda];
        }
        return $this;
    }

    /**
     * @param \Closure $lambda
     * @return Linq
     */
    #[\Override]
    public function select(\Closure $lambda): Linq
    {
        $this -> aQuery[] = [$this::SELECT, $lambda];
        return $this;
    }

    /**
     * @param \Closure $lambda
     * @return Linq
     */
    #[\Override]
    public function groupBy(\Closure $lambda): Linq
    {
        $this -> aQuery[] = [$this::GROUPBY, $lambda];
        $this -> bIsGrouped = true;
        return $this;
    }

    /**
     * @param  \Closure $lambda
     * @return array
     */
    #[\Override]
    public function toArray(\Closure $lambda = null, bool $keepKeys = false): array
    {
        $self = $lambda === null ? $this : $this -> where($lambda);
        if (count($self -> aOutCollection) === 0) {
            $self -> execute();
        }
        return $keepKeys ? $self -> aOutCollection : array_values($self -> aOutCollection);
    }

    /**
     * @param  \Closure $lambda
     * @return mixed
     */
    #[\Override]
    public function firstOrDefault(\Closure $lambda = null): mixed
    {
        $self = $lambda === null ? $this : $this -> where($lambda);
        if (count($self -> aOutCollection) === 0) {
            $self -> execute();
        }
        if (count($self -> aOutCollection) === 0) {
            return null;
        }
        $key = array_keys($self -> aOutCollection)[0];
        return $self -> aOutCollection[$key];
    }

    /**
     * @return int
     */
    #[\Override]
    public function count(): int
    {
        if (count($this -> aOutCollection) === 0) {
            $this -> execute();
        }
        return count($this -> aOutCollection);
    }

    /**
     * @param \Closure $lambda
     * @return ILinq
     */
    #[\Override]
    public function sum(\Closure $lambda = null): ILinq
    {
        $this -> aQuery[] = [$this::SUM, $lambda];
        return $this;
    }

    /**
     * @param \Closure $lambda
     * @return ILinq
     */
    #[\Override]
    public function avg(\Closure $lambda = null): ILinq
    {
        $self = $this -> sum($lambda);
        $self -> aQuery[] = [$this::AVG, null];
        return $self;
    }

    /**
     * @param \Closure $lambda
     * @param SortDirections $direction
     * @return Linq
     */
    #[\Override]
    public function orderBy(
        \Closure $lambda = null,
        SortDirections $direction = SortDirections::Asc
    ): Linq {
        $this -> aQuery[] = [$this::ORDERBY, $lambda, $direction];
        return $this;
    }

    /**
     * @param  int|string      $index
     * @param  array    $buffer
     * @param  \Closure $lambda
     * @param  mixed    $item
     * @return void
     */
    private function executeSwitchWhere(mixed $index, array &$buffer, \Closure $lambda, mixed $item): void
    {
        if ($lambda($item)) {
            $buffer[$index] = $item;
        }
    }

    /**
     * @param  mixed    $buffer
     * @param  \Closure $lambda
     * @param  mixed    $item
     * @return void
     */
    private function executeSwitchGroupBy(mixed &$buffer, \Closure $lambda, mixed $item): void
    {
        $key = $lambda($item);
        if (is_array($buffer)) {
            $buffer = new Dictionary();
        }
        
        if (!$buffer -> add($key, $item, true)) {
            $list = $buffer -> get($key);
            $list[] = $item;
            
            $buffer -> remove($key);
            $buffer -> add($key, $list);
        }
    }

    /**
     * @param  int      $index
     * @param  mixed    $buffer
     * @param  \Closure $lambda
     * @param  mixed    $item
     * @param  array    $collection
     * @return void
     */
    private function executeSwitchSum(
        int $index,
        mixed &$buffer,
        \Closure $lambda,
        mixed $item,
        array $collection
    ): void {
        if (!isset($this -> aCounts[$index])) {
            $this -> aCounts[$index] = 0;
        }

        if ($this -> bIsGrouped) {
            if (count($buffer) === 0) {
                $buffer = array_fill_keys(array_keys($collection), 0);
            }
            $this -> aCounts[$index] += count($item);
            foreach ($item as $v) {
                $value = $lambda == null ? $v : $lambda($v);
                $buffer[$index] += $value;
            }
        } else {
            if (is_array($buffer)) {
                $buffer = 0;
            }

            $buffer += $lambda == null ? $item : $lambda($item);
            $this -> aCounts[$index]++;
        }
    }

    /**
     * @param mixed         $index
     * @param mixed         $buffer
     * @param \Closure|null $lambda
     * @param mixed         $item
     */
    private function executeSwitchOrderBy(mixed $index, mixed &$buffer, ?\Closure $lambda, mixed $item)
    {
        if ($lambda === null) {
            $buffer[$index] = [ $item ];
        } else {
            $key = $lambda($item);
            if (!isset($buffer[$key])) {
                $buffer[$key] = [];
            }

            $buffer[$key][] = $item;
        }
    }

    /**
     * @param  int           $type
     * @param  int|string           $index
     * @param  mixed         $buffer
     * @param  \Closure|null $lambda
     * @param  mixed         $item
     * @param  array         $collection
     * @return void
     * @throws \UT_Php_Core\Exceptions\NotImplementedException
     */
    private function executeSwitch(
        int $type,
        mixed $index,
        mixed &$buffer,
        ?\Closure $lambda,
        mixed $item,
        array $collection
    ): void {
        switch ($type) {
            case $this::SKIP:
                break;
            case $this::WHERE:
                $this -> executeSwitchWhere($index, $buffer, $lambda, $item);
                break;

            case $this::SELECT:
                $buffer[$index] = $lambda($item, $index);
                break;

            case $this::GROUPBY:
                $this -> executeSwitchGroupBy($buffer, $lambda, $item);
                break;

            case $this::SUM:
                $this -> executeSwitchSum($index, $buffer, $lambda, $item, $collection);
                break;

            case $this::AVG:
                $buffer[$index] = $item / $this -> aCounts[$index];
                unset($this -> aCounts[$index]);
                break;

            case $this::ORDERBY:
                $this -> executeSwitchOrderBy($index, $buffer, $lambda, $item);
                break;

            default:
                throw new \UT_Php_Core\Exceptions\NotImplementedException($type);
        }
    }

    /**
     * @return void
     */
    private function execute(): void
    {
        $collection = $this -> aInCollection;
        foreach ($this -> aQuery as $query) {
            $type = $query[0];
            $lambda = $query[1];

            if ($type === $this::SUM) {
                $this -> aCounts = [];
            }

            if ($type === $this::SKIP) {
                $collection = array_slice($collection, $lambda);
                continue;
            }

            $buffer = [];
            foreach ($collection as $i => $item) 
            {
                $this -> executeSwitch($type, $i, $buffer, $lambda, $item, $collection);
            }

            if ($type === $this::ORDERBY) 
            {
                $direction = $query[2];
                if ($direction == SortDirections::Asc) 
                {
                    ksort($buffer);
                } else 
                {
                    krsort($buffer);
                }

                $buffer = $this -> multiToSingleArray($buffer);
            }

            if (is_int($buffer)) {
                $buffer = [ $buffer ];
            }
            $collection = is_array($buffer) ? $buffer : $buffer -> toArray();
        }
        $this -> aOutCollection = $collection;
    }

    /**
     * @param  array $data
     * @return array
     */
    private function multiToSingleArray(array $data): array
    {
        $buffer = [];

        foreach ($data as $items) {
            foreach ($items as $item) {
                $buffer[] = $item;
            }
        }

        return $buffer;
    }
}
