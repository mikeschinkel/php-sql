<?php

namespace Sql\Grammar;

use Attribute;

#[Attribute]
abstract class Grammar {

    /**
     * @var string|null
     */
    private $_grammar;

    function __construct( ?string $grammar = null ) {
        $this->_grammar = $grammar;
    }

    function build() {
        $root = $this->getTree();
    }

    function getConcreteSyntaxTree(): Entry {
        return $this->getTree();
    }

    abstract function getTree(): Entry;

    function getGrammar(): ?string {
        return $this->_grammar ?? null;
    }

}

