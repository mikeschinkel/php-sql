<?php


namespace Sql\Grammar;

class Flags {
    public bool $optional      = false;
    public bool $multiple      = false;
    public bool $caseSensitive = false;

    function getFlags(): string {
        $string = '';
        if ( $this->optional ) {
            $string .= 'o';
        }
        if ( $this->multiple ) {
            $string .= 'm';
        }
        if ( $this->caseSensitive ) {
            $string .= 's';
        }
        return $string;
    }
}
