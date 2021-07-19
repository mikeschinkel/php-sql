<?php


namespace Sql\Grammar;

use Closure;

class Entries {

    private static array $_entries = array();
    private static array $_callStack = array();

    static function rule( Entry|callable $entry ): Entry {
        begin:
        {
            if ( $entry instanceof Entry) {
                $entry = clone $entry;
            }
            $state = _inferEntryKey( 2,$type );
            Entries::pushCall($state->call);

            if ( Entries::exists( $state->key ) ) {
                goto end;
            }
            if ( $entry instanceof Closure) {
                $entry = new Entry( $entry );
            }
            $entry->type = $type;
            Entries::add( $state->key, $entry );
        }
        end:
        $entry = Entries::get($state->key);
        if ( $entry->value instanceof Closure ) {
            Entries::maybeDereference( $entry );
        }
        Entries::popCall();
        return $entry;

    }

    static function maybeDereference( Entry $entry ) {
        begin:
        {
            if ( is_scalar( $entry->value ) ) {
                goto end;
            }

            if ( is_array( $entry->value ) ) {
                foreach( $entry->value as $i => $e ) {
                    Entries::maybeDereference($e);
                }
                goto end;
            }

            if ( $entry->value instanceof Entry ) {
                Entries::maybeDereference($entry->value);
                goto end;
            }

            if ( ! $entry->value instanceof Closure ) {
                goto end;
            }

            if ( self::_is_recursing() ) {
                goto end;
            }

            $callable     = $entry->value;
            $entry->value = $callable();
        }
        end:
    }

    static function pushCall(array $call):array {
        self::$_callStack[] = $call;
        return $call;
    }

    static function topCall():object {
        $top = end( self::$_callStack );
        return self::_ensureCallProps(false !== $top ? $top : []);
    }

    static function popCall():array {
        return array_pop( self::$_callStack );
    }

    static function getAll(): array {
        return self::$_entries;
    }

    static function get( string $key = null ): ?Entry {

        begin:
        {
            $entry = self::$_entries[ $key ] ?? null;

            if ( is_null( $entry ) ) {
                trigger_error("Entry for key '%s' is null",$key);
                die(1);
            }
        }
        end:
        return $entry;
    }

    private static function _ensureCallProps( array $call ): object {
        $defaults = array(
            'function' => null,
            'depth' => 0,
        );
        return (object)array_merge($defaults,$call);
    }

    private static function _is_recursing(): bool {
        begin:
        {
            $is_recursing = false;
            $latest = Entries::topCall();
            $stack = debug_backtrace();
            if (0 === $latest->depth) {
                goto end;
            }
            if (count($stack)-1 === $latest->depth) {
                goto end;
            }
            if (is_null(self::_findRecursion($stack,$latest))) {
                goto end;
            }

            $is_recursing = true;
        }
        end:
        return $is_recursing;
    }

    private static function _findRecursion(array $stack, object $call ): ?int {
        $found = null;
        for( $i = $call->depth+1; $i < count($stack); $i++ ) {
            $caller = self::_ensureCallProps($stack[$i]);
            if ( $call->function !== $caller->function ) {
                continue;
            }
            $found = $i;
            break;
        }
        return $found;
    }

    static function add( string $key, callable|Entry $entry ): void {
        self::$_entries[ $key ] = $entry;
    }

    static function exists( string $key ): bool {
        return isset( self::$_entries[ $key ] );
    }

    static function register(Entry $entry) {
        begin:
        {
            $key = $entry->getKey();
            if (empty($key)) {
                goto end;
            }
            if (self::exists($key)) {
                goto end;
            }
            self::add($key,$entry);
        }
        end:
    }

    static function printAll() {
        foreach(Entries::getAll() as $entry) {
            Entries::print( $entry );
        }
    }

    static function print(Entry $entry,$depth=0) {
        begin:
        {
            $record = false;
            $key = $entry->getKey();

            $value = $entry->value;
            if ( in_array(substr($key,0,6),['oneOf:','digit:'])) {
                // Don't print children like char('x')...
                $value = [];
            }

            printf("\n%s— %s",str_repeat(' ',$depth),$key);

            if (2< $depth && 'A' <= $key[0] && 'Z' >= $key[0]) {
                goto end;
            }
            if ( $value instanceof Entry ) {
                self::print($value, $depth + 1);
                goto end;
            }
            if ( is_array($value) ) {
                foreach( $value as $each ) {
                    self::print( $each, $depth + 1 );
                }
                goto end;
            }
        }
        end:

    }
}
