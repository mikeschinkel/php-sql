<?php /** @noinspection RequiredAttributes */

/** @noinspection HtmlUnknownAttribute */

namespace Sql\Grammar;

class Entry extends Flags {

    public mixed $value = null;
    public ?string $type = null;
    private ?string $_key = null;

    function __construct( ...$args ) {
        $this->value = 1 === count($args)
            ? $args[0]
            : $args;
    }

    function getKey():?string {
        if ( is_null($this->_key)) {
            $this->_key = makeKey($this->type, $this, $this->value );
        }
        return $this->_key;
    }


}
