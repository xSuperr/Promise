<?php

namespace xSuperr\Promise;

class PromiseUtils
{
    /**
     * Returns a promise that will resolve only once all the Promises in
     * `$promises` have resolved. The resolution value of the returned promise
     * will be an array containing the resolution values of each Promises in
     * `$promises` indexed by the respective Promises' array keys.
     *
     * @template TPromiseValue
     * @template TKey of array-key
     * @phpstan-param array<TKey, BetterPromise<TPromiseValue>> $promises
     *
     * @phpstan-return BetterPromise<array<TKey, TPromiseValue>>
     */
    public static function promiseAll(array $promises) : BetterPromise {
        /** @phpstan-var BetterPromiseResolver<array<TKey, TPromiseValue>> $resolver */
        $resolver = new BetterPromiseResolver();
        $values = [];
        $toResolve = count($promises);
        $continue = true;

        foreach($promises as $key => $promise){
            $promise->then(
                function(mixed $value) use ($resolver, $key, &$toResolve, &$continue, &$values) : void{
                    $values[$key] = $value;

                    if(--$toResolve === 0 && $continue){
                        $resolver->resolve($values);
                    }
                }
            );

            $promise->fail(
                function() use ($resolver, &$continue) : void{
                    if($continue){
                        $continue = false;
                        $resolver->reject();
                    }
                }
            );

            if(!$continue){
                break;
            }
        }

        if($toResolve === 0){
            $continue = false;
            $resolver->resolve($values);
        }

        return $resolver->getPromise();
    }
}