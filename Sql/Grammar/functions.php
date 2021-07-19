<?php

namespace Sql\Grammar;

use Closure;

function rule( Entry|callable $entry ): Entry {
    return Entries::rule($entry);
}

function makeEntry( $value ): Entry {
    return new Entry( $value );
}

function oneOfChars( string $chars ): Entry {
    $maker = fn( $e ) => char( $e );
    $chars = str_split( $chars, 1 );
    $chars = array_map( $maker, $chars );
    return oneOf( ...$chars );
}

function wordSequence( string $words ): Entry {
    $words = preg_split( '#\s+#', $words, - 1, PREG_SPLIT_NO_EMPTY );
    foreach ( $words as $index => $word ) {
        $words[ $index ] = word( $word );
    }
    $entry = makeEntry( $words );
    $entry->type = _extractEntryType( __FUNCTION__ );
    //Entries::register($entry);
    return $entry;
}

function word( string $word ): Entry {
    $entry = makeEntry( $word );
    assignEntryType( $entry );
    return $entry;
}

function char( string $ch ): Entry {
    $entry = makeEntry( $ch );
    assignEntryType( $entry );
    return $entry;
}

function sigil( string $ch ): Entry {
    $entry = makeEntry( $ch );
    assignEntryType( $entry );
    return $entry;
}

function optionalWord( string $word ): Entry {
    $entry             = word( $word );
    $entry->optional = true;
    $entry->type = _extractEntryType( __FUNCTION__ );
    //Entries::register($entry);
    return $entry;
}

function oneOrMore( mixed ...$entries ): Entry {
    if ( 1 == count( $entries ) ) {
        $entries = $entries[ 0 ];
    }
    $entry = makeEntry( $entries );
    $entry->multiple = true;
    assignEntryType( $entry );
    return $entry;

}

function _inferCall( int $offset = 0, array &$stack = null ): array {
    $stack = debug_backtrace( null, 5 + $offset );
    $depth = 1 + $offset;
    $call = $stack[ $depth ];
    $call['depth'] = $depth;
    return $call;
}

function _inferEntryType( array $call ): string {
    $type  = _extractEntryType( $call[ 'function' ] ?? null );
    return $type;
}

function _inferEntryKey( int $offset = 0, string &$type = null, string $value = null, ): object {
    $call  = _inferCall( $offset +1, $stack );
    $type  = _inferEntryType( $call );
    $flags = new Flags();
    for ( $i = 2 + $offset; $i < count( $stack ); $i ++ ) {
        $func = _extractEntryType( $stack[ $i ][ 'function' ] ?? null );
        switch ( $func ) {
            case '{closure}':
                break;
            case 'optional':
                $flags->optional = true;
                break;
            case 'multiple':
                $flags->multiple = true;
                break;
            case 'caseSensitive':
                $flags->caseSensitive = true;
                break;
            default:
                break 2;
        }
    }
    return (object) array(
        'key'  => makeKey( $type, $flags, $value ),
        'call' => $call,
    );
}
function makeKey(string $type, Flags $flags, mixed $value=null):?string {
    begin:
    {
        if ( is_null($value) ) {
            goto end;
        }
        switch ( $type ) {
            case 'char':
                $value = "'" !== $value
                    ? sprintf( "('%s')", $value )
                    : sprintf( '("%s")', $value );
                break;
            /** @noinspection PhpMissingBreakStatementInspection */
            case 'optionalWord':
                $type = 'word';
            case 'sigil':
            case 'word':
                break;
            /** @noinspection PhpMissingBreakStatementInspection */
            case 'digit':
                $value = $value->value;
            case 'oneOf':
                $_value = [];
                foreach($value as $entry) {
                    if ( !is_string($entry->value)) {
                        break;
                    }
                    if ( 1< strlen($entry->value)) {
                        break;
                    }
                    $_value[] = $entry->value;
                }
                $value = ! empty($_value)
                    ? implode('/',$_value)
                    : null;
                break;
            case 'sequence':
            case 'oneOfWords':
                $value = null;
                break;
            case 'zeroOrMore':
            case 'oneOrMore':
            case 'oneOfWords':
                if ( $value instanceof Entry ) {
                    $value = $value->getKey();
                }
                break;
            default:
                if ( $value instanceof Closure ) {
                    $value = '{closure}';
                    break;
                }
                $value = null;
                break;
        }

    }
    end:
    return _formatKey($type,$flags,$value);
}

function _formatKey(string $type, Flags $flags, mixed $value=null):?string {
    begin:
    {
        if ( empty($type) ) {
            $key = null;
            goto end;
        }
        if ( is_array( $value ) ) {
            $value = '{array}';
        }
        if ( !empty( $value ) ) {
            $value = sprintf(':%s',$value);
        }
        $_flags = $flags->getFlags();
        if ( empty( $_flags ) ) {
            $key = sprintf( '%s%s', $type, $value );
            goto end;
        }
        $key =sprintf( '%s[%s]%s', $type, $_flags, $value );
    }
    end:
    return $key;
}

function _getArrayDescription( array $value ): string {
    //$values = array_map( fn( Entry $e ) => $e->getDescription(in_array($e->type,['char','digit'])), $value );
    $values = array_map( fn( Entry $e ) => $e->getKey(), $value );
    return sprintf( '(%s)', implode( '/', array_filter($values) ) );
}


function _extractEntryType( string $func ): string {
    begin:
    {
        if ( is_null( $func ) ) {
            $type = 'unknown';
            goto end;
        }
        $pos  = strrpos( $func, '\\' );
        $type = false !== $pos
            ? substr( $func, $pos + 1 )
            : $func;
    }
    end:
    return $type;
}



function assignEntryType( Entry $entry, int $offset = 0 ): void {
    $stack = debug_backtrace( null, 2 + $offset + 5 );
    $type  = _extractEntryType( $stack[ 1 + $offset ][ 'function' ] ?? null );
    $entry->type = $type;
    //Entries::register($entry);
}


//===[ These all accept entries as callables ]===

function caseSensitive( Entry $entry ): Entry {
    return setEntryFlags( $entry,
        fn() => $entry->caseSensitive = true );
}

function zeroOrOne( Entry $entry ): Entry {
    $entry->optional = true;
    $entry->type = 'sequence';
    //Entries::register($entry);
    return $entry;
}

function optional( Entry $entry ): Entry {
    return setEntryFlags( $entry,
        fn() => $entry->optional = true );
}

function setEntryFlags( Entry $entry,callable $flagSetter ): Entry {
    $flagSetter($entry);
    //Entries::register($entry);
    return $entry;
}

function oneOf( Entry ...$entries ): Entry {
    $entry = makeEntry( $entries );
    assignEntryType( $entry );
    return $entry;
}

function oneOfWords( string ...$words ): Entry {
    $maker = fn( $e ) => word( trim( $e ) );
    $words = array_map( $maker, $words );
    $entry = oneOf( ...$words );
    $entry->type = _extractEntryType( __FUNCTION__ );
    //Entries::register($entry);
    return $entry;
}

function sequence( Entry|string ...$entries ): Entry {
    $entry = makeEntry( $entries );
    assignEntryType( $entry );
    return $entry;

}

function quoted( Entry $entry ): Entry {
    return sequence(
        char( '"' ),
        $entry,
        char( '"' ),
    );
}

function parenEnclosed( Entry $entry ): Entry {
    return sequence(
        char( '(' ),
        $entry,
        char( ')' ),
    );
}

function customParser(): Entry {
    $entry = makeEntry( null );
    assignEntryType( $entry );
    return $entry;

}

function unsupported(): Entry {
    $entry = makeEntry( null );
    assignEntryType( $entry );
    return $entry;

}

function optionalSequence( ...$entries ): Entry {
    $entry = sequence( ...$entries );
    $entry->optional = true;
    //Entries::register( $entry );
    return $entry;

}

function commaList( Entry $entry ): Entry {
    return sequence(
        $entry,
        zeroOrMore(
            char( ',' ),
            $entry,
        ),
    );

}

function zeroOrMore( mixed ...$entries ): Entry {
    if ( 1 == count( $entries ) ) {
        $entries = $entries[ 0 ];
    }
    $entry           = makeEntry( $entries );
    $entry->optional = true;
    $entry->multiple = true;
    $entry->type = 'sequence';
    //Entries::register($entry);
    return $entry;

}

function splitWords( string $words ): array {
    $words = preg_replace( '#\s+#', ' ', $words );
    $words = str_replace( ' ', '|', $words );
    return array_filter( explode( '|', $words ) );
}









//function key( int $offset, callable $entryCallable ): ?string {
//    $type = getEntryType( 1 + $offset );
//    switch ( $type ) {
//        case 'char':
//            $entry = $entryCallable();
//            $value = "'" !== $entry->value
//                ? sprintf( "('%s')", $entry->value )
//                : sprintf( '("%s")', $entry->value );
//            break;
//        case 'word':
//            $value = $entryCallable()->value;
//            break;
//        case 'oneOfWords':
//            if ( is_string( $entryCallable()->value ) ) {
//                $value = $entryCallable()->value;
//                break;
//            }
//            if ( is_array( $entryCallable()->value ) ) {
//                $values = array_map( fn( $e ) => $e->value, $entryCallable()->value );
//                $value  = implode( '/', $values );
//                break;
//            }
//            break;
//        default:
//            $value = null;
//    }
//    if ( ! is_null( $value ) ) {
//        $value = sprintf( ':%s', $value );
//    }
//    $flags = '';
//    return ! empty( $flags )
//        ? sprintf( '%s[%s]%s', $type, $flags, $value )
//        : sprintf( '%s%s', $type, $value );
//}

