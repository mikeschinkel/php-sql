<?php /** @noinspection RequiredAttributes */

/** @noinspection HtmlUnknownAttribute */

namespace Sql\Grammar\Sql92 {

    use Sql\Grammar\Entry;
    use Sql\Grammar\Grammar;
    use function Sql\Grammar\rule;
    use function Sql\Grammar\oneOf;
    use function Sql\Grammar\sequence;
    use function Sql\Grammar\char;
    use function Sql\Grammar\commaList;
    use function Sql\Grammar\customParser;
    use function Sql\Grammar\oneOfWords;
    use function Sql\Grammar\oneOrMore;
    use function Sql\Grammar\zeroOrOne;
    use function Sql\Grammar\zeroOrMore;
    use function Sql\Grammar\word;
    use function Sql\Grammar\wordSequence;
    use function Sql\Grammar\oneOfChars;
    use function Sql\Grammar\optional;
    use function Sql\Grammar\optionalSequence;
    use function Sql\Grammar\optionalWord;
    use function Sql\Grammar\parenEnclosed;
    use function Sql\Grammar\splitWords;
    use function Sql\Grammar\unsupported;
    use function Sql\Grammar\caseSensitive;
    use function Sql\Grammar\quoted;
    use function Sql\Grammar\sigil;


    /**
     * Class Sql92
     * @package Sql\Grammar;
     * @see https://ronsavage.github.io/SQL/
     * @see https://github.com/ronsavage/SQL/blob/master/Syntax.rules.txt
     */
    class Sql92 extends Grammar {

        function getTree(): Entry {
            return rule( fn() => oneOf(
                AllocateCursorStatement(),
                AlterDomainStatement(),
                AlterTableStatement(),
                CloseStatement(),
                DynamicCloseStatement(),
                CommitStatement(),
                ConnectStatement(),
                AssertionDefinition(),
                CharacterSetDefinition(),
                CollationDefinition(),
                DomainDefinition(),
                SchemaDefinition(),
                TableDefinition(),
                TranslationDefinition(),
                ViewDefinition(),
                DeallocatePreparedStatement(),
                DeclareCursor(),
                DynamicDeclareCursor(),
                TemporaryTableDeclaration(),
                DeleteStatementPositioned(),
                DeleteStatementSearched(),
                DynamicDeleteStatementPositioned(),
                DescribeStatement(),
                SystemDescriptorStatement(),
                DisconnectStatement(),
                ExecuteStatement(),
                ExecuteImmediateStatement(),
                FetchStatement(),
                DynamicFetchStatement(),
                GetDiagnosticsStatement(),
                GrantStatement(),
                InsertStatement(),
                Module(),
                OpenStatement(),
                DynamicOpenStatement(),
                PrepareStatement(),
                RevokeStatement(),
                RollbackStatement(),
                QuerySpecification(),
                SetCatalogStatement(),
                SetConnectionStatement(),
                SetConstraintsModeStatement(),
                SetNamesStatement(),
                SetSchemaStatement(),
                SetSessionAuthorizationIdentifierStatement(),
                SetLocalTimeZoneStatement(),
                SetTransactionStatement(),
                UpdateStatementPositioned(),
                UpdateStatementSearched(),
                DynamicUpdateStatementPositioned(),
            ) );
        }
    }


    #[Grammar( '<comment> ::=
        <comment introducer> 
        [ <comment character>... ] 
        <newline>' )]
    function Comment(): Entry {
        return rule( fn() => sequence(
            CommentIntroducer(),
            zeroOrMore( CommentCharacter() ),
            NewLine(),
        ) );
    }

    #[Grammar( '<comment introducer> ::=
            <minus sign>
            <minus sign> 
            [<minus sign>...]' )]
    function CommentIntroducer(): Entry {
        return rule( fn() => sequence(
            MinusSign(),
            MinusSign(),
            zeroOrMore( MinusSign() ),
        ) );
    }

    #[Grammar( '<minus sign> ::= -' )]
    function MinusSign(): Entry {
        return rule( fn() => char( '-' ) );
    }

    #[Grammar( '<comment character> ::=
            <nonquote character> 
        |   <quote>' )]
    function CommentCharacter(): Entry {
        return rule( fn() => oneOf(
            NonQuoteCharacter(),
            Quote(),
        ) );
    }

    #[Grammar( '<nonquote character> ::= !! See the Syntax rules' )]
    function NonQuoteCharacter(): Entry {
        return rule( fn() => customParser() );
    }

    #[Grammar( '<simple Latin letter> ::=
            <simple Latin upper case letter> 
        |   <simple Latin lower case letter>' )]
    function SimpleLatinLetter(): Entry {
        return rule( fn() => oneOf(
            SimpleLatinUpperCaseLetter(),
            SimpleLatinLowerCaseLetter(),
        ) );
    }

    #[Grammar( '<simple Latin upper case letter> ::= 
        A | B | C | D | E | F | G | H | I | J | K | L | M | N | O | P | Q | R | S | T | U | V | W | X | Y | Z' )]
    function SimpleLatinUpperCaseLetter(): Entry {
        return rule( fn() => oneOfChars( 'ABCDEFGHIJKLMNOPQRSTUVWXYZ' ) );
    }

    #[Grammar( '<simple Latin lower case letter> ::= 
        a | b | c | d | e | f | g | h | i | j | k | l | m | n | o | p | q | r | s | t | u | v | w | x | y | z' )]
    function SimpleLatinLowerCaseLetter(): Entry {
        return rule( fn() => oneOfChars( 'abcdefghijklmnopqrstuvwxyz' ) );
    }

    #[Grammar( '<digit> ::= 0 | 1 | 2 | 3 | 4 | 5 | 6 | 7 | 8 | 9' )]
    function digit(): Entry {
        return rule( fn() => oneOfChars( '0123456789' ) );
    }

    #[Grammar( "<quote> ::= '" )]
    function Quote(): Entry {
        return rule( fn() => char( "'" ) );
    }

    #[Grammar( '<newline> ::= !! implementation defined end of line indicator' )]
    function NewLine(): Entry {
        return rule( fn() => customParser() );
    }

    #[Grammar( '<column constraint> ::= 
            NOT NULL 
        |   <unique specification> 
        |   <references specification> 
        |   <check constraint definition>' )]
    function ColumnConstraint(): Entry {
        return rule( fn() => oneOf(
            wordSequence( 'NOT NULL' ),
            UniqueSpecification(),
            ReferencesSpecification(),
            CheckConstraintDefinition(),
        ) );
    }

    #[Grammar( '<unique specification> ::= 
            UNIQUE 
        |   PRIMARY KEY' )]
    function UniqueSpecification(): Entry {
        return rule( fn() => oneOf(
            word( 'UNIQUE' ),
            wordSequence( 'PRIMARY KEY' ),
        ) );
    }

    #[Grammar( '<references specification> ::= 
        REFERENCES 
        <referenced table and columns> 
        [ 
            MATCH 
            <match type> 
        ] 
        [ <referential triggered action> ]' )]
    function ReferencesSpecification(): Entry {
        return rule( fn() => sequence(
            word( 'REFERENCES' ),
            ReferencedTableAndColumns(),
            optionalSequence(
                word( 'MATCH' ),
                MatchType(),
            ),
            optional( ReferentialTriggeredAction() ),
        ) );
    }

    #[Grammar( '<referenced table and columns> ::= 
            <table name> 
            [ <left paren> <reference column list> <right paren> ]' )]
    function ReferencedTableAndColumns(): Entry {
        return rule( fn() => sequence(
            TableName(),
            optional( parenEnclosed( ReferenceColumnList() ) ),
        ) );
    }

    #[Grammar( '<table name> ::=
            <qualified name> 
        |   <qualified local table name>' )]
    function TableName(): Entry {
        return rule( fn() => oneOf(
            QualifiedName(),
            QualifiedLocalTableName(),
        ) );
    }

    #[Grammar( '<qualified name> ::= 
        [ 
            <schema name> 
            <period> 
        ] 
        <qualified identifier>' )]
    function QualifiedName(): Entry {
        return rule( fn() => sequence(
            optionalSequence(
                SchemaName(),
                Period(),
            ),
            QualifiedIdentifier(),
        ) );
    }

    #[Grammar( '<schema name> ::= 
        [   <catalog name> 
            <period> ] 
        <unqualified schema name>' )]
    function SchemaName(): Entry {
        return rule( fn() => sequence(
            optionalSequence(
                CatalogName(),
                Period(),
            ),
            UnqualifiedSchemaName()
        ) );
    }

    #[Grammar( '<catalog name> ::= <identifier>' )]
    function CatalogName(): Entry {
        return rule( fn() => identifier() );
    }

    #[Grammar( '<identifier> ::= 
        [   <introducer>
            <character set specification> 
        ] 
        <actual identifier>' )]
    function Identifier(): Entry {
        return rule( fn() => sequence(
            optionalSequence(
                Introducer(),
                CharacterSetSpecification(),
            ),
            ActualIdentifier(),
        ) );
    }

    #[Grammar( '<introducer> ::= <underscore>' )]
    function Introducer(): Entry {
        return rule( fn() => Underscore() );
    }

    #[Grammar( '<underscore> ::= _' )]
    function Underscore(): Entry {
        return rule( fn() => char( '_' ) );
    }

    #[Grammar( '<character set specification> ::= 
            <standard character repertoire name> 
        |   <implementation - defined character repertoire name> 
        |   <user - defined character repertoire name> 
        |   <standard universal character form - of -use name> 
        |   <implementation - defined universal character form - of -use name>' )]
    function CharacterSetSpecification(): Entry {
        return rule( fn() => oneOf(
            ImplementationDefinedCharacterRepertoireName(),
            UserDefinedCharacterRepertoireName(),
            StandardUniversalCharacterFormOfUseName(),
            ImplementationDefinedUniversalCharacterFormOfUseName(),
            StandardCharacterRepertoireName(),
        ) );
    }


    #[Grammar( '<standard character repertoire name> ::= <character set name>' )]
    function StandardCharacterRepertoireName(): Entry {
        return rule( fn() => CharacterSetName() );
    }

    #[Grammar( '<character set name> ::= 
        [ <schema name> <period> ] 
        <SQL language identifier>' )]
    function CharacterSetName(): Entry {
        return rule( fn() => sequence(
            optionalSequence(
                SchemaName(),
                Period(),
            ),
            SqlLanguageIdentifier()
        ) );
    }

    #[Grammar( '<period> ::= .' )]
    function Period(): Entry {
        return rule( fn() => char( '.' ) );
    }

    #[Grammar( '<SQL language identifier> ::= 
        <SQL language identifier start> 
        {       <underscore> 
            |   <SQL language identifier part> }*' )]
    function SqlLanguageIdentifier(): Entry {
        return rule( fn() => sequence(
            SqlLanguageIdentifierStart(),
            zeroOrMore(
                oneOf(
                    Underscore(),
                    SqlLanguageIdentifierPart(),
                )
            )
        ) );
    }

    #[Grammar( '<SQL language identifier start> ::= 
            <simple Latin letter>' )]
    function SqlLanguageIdentifierStart(): Entry {
        return rule( fn() => SimpleLatinLetter() );
    }

    #[Grammar( '<SQL language identifier part> ::= 
            <simple Latin letter> 
        |   <digit>' )]
    function SqlLanguageIdentifierPart(): Entry {
        return rule( fn() => oneOf(
            SimpleLatinLetter(),
            digit(),
        ) );
    }

    #[Grammar( '<implementation-defined character repertoire name> ::=
        <character set name>' )]
    function ImplementationDefinedCharacterRepertoireName(): Entry {
        return rule( fn() => CharacterSetName() );
    }

    #[Grammar( '<user-defined character repertoire name> ::=
        <character set name>' )]
    function UserDefinedCharacterRepertoireName(): Entry {
        return rule( fn() => CharacterSetName() );
    }

    #[Grammar( '<standard universal character form-of-use name> ::=
        <character set name>' )]
    function StandardUniversalCharacterFormOfUseName(): Entry {
        return rule( fn() => CharacterSetName() );
    }

    #[Grammar( '<implementation-defined universal character form-of-use name> ::=
        <character set name>' )]
    function ImplementationDefinedUniversalCharacterFormOfUseName(): Entry {
        return rule( fn() => CharacterSetName() );
    }

    #[Grammar( '<actual identifier> ::=
            <regular identifier> 
        |   <delimited identifier>' )]
    function ActualIdentifier(): Entry {
        return rule( fn() => oneOf(
            RegularIdentifier(),
            DelimitedIdentifier(),
        ) );
    }

    #[Grammar( '<regular identifier> ::=
        <identifier body>' )]
    function RegularIdentifier(): Entry {
        return rule( fn() => IdentifierBody() );

    }

    #[Grammar( '<identifier body> ::=
            <identifier start> 
            { <underscore> | <identifier part> }*' )]
    function IdentifierBody(): Entry {
        return rule( fn() => sequence(
            IdentifierStart(),
            zeroOrMore(
                oneOf( Underscore(), IdentifierPart() )
            )
        ) );
    }

    #[Grammar( '<identifier start> ::= 
        !! See the Syntax rules' )]
    function IdentifierStart(): Entry {
        return rule( fn() => customParser() );
    }

    #[Grammar( '<identifier part> ::=
            <identifier start> 
        |   <digit>' )]
    function IdentifierPart(): Entry {
        return rule( fn() => oneOf(
            IdentifierStart(),
            digit(),
        ) );
    }

    #[Grammar( '<delimited identifier> ::=
            <double quote> 
            <delimited identifier body> 
            <double quote>' )]
    function DelimitedIdentifier(): Entry {
        return rule( fn() => sequence(
            DoubleQuote(),
            DelimitedIdentifierBody(),
            DoubleQuote(),
        ) );
    }

    #[Grammar( '<double quote> ::= "' )]
    function DoubleQuote(): Entry {
        return rule( fn() => char( '"' ) );
    }

    #[Grammar( '<delimited identifier body> ::=
            <delimited identifier part>...' )]
    function DelimitedIdentifierBody(): Entry {
        return rule( fn() => oneOrMore(
            DelimitedIdentifierPart(),
        ) );
    }

    #[Grammar( '<delimited identifier part> ::=
            <nondoublequote character> 
        |   <doublequote symbol>' )]
    function DelimitedIdentifierPart(): Entry {
        return rule( fn() => oneOf(
            NonDoubleQuoteCharacter(),
            DoubleQuoteSymbol(),
        ) );
    }

    #[Grammar( '<nondoublequote character> ::= 
        !! See the Syntax rules' )]
    function NonDoubleQuoteCharacter(): Entry {
        return rule( fn() => customParser() );
    }

    #[Grammar( '<doublequote symbol> ::=
        <double quote>
        <double quote>' )]
    function DoubleQuoteSymbol(): Entry {
        return rule( fn() => sequence(
            DoubleQuote(),
            DoubleQuote(),
        ) );
    }

    #[Grammar( '<unqualified schema name> ::=
        <identifier>' )]
    function UnqualifiedSchemaName(): Entry {
        return rule( fn() => identifier() );
    }

    #[Grammar( '<qualified identifier> ::=
        <identifier>' )]
    function QualifiedIdentifier(): Entry {
        return rule( fn() => identifier() );
    }

    #[Grammar( '<qualified local table name> ::= 
        MODULE 
        <period> 
        <local table name>' )]
    function QualifiedLocalTableName(): Entry {
        return rule( fn() => oneOf(
            word( 'MODULE' ),
            Period(),
            LocalTableName()
        ) );
    }

    #[Grammar( '<local table name> ::=
        <qualified identifier>' )]
    function LocalTableName(): Entry {
        return rule( fn() => QualifiedIdentifier() );
    }

    #[Grammar( '<left paren> ::= (' )]
    function LeftParen(): Entry {
        return rule( fn() => char( '(' ) );
    }

    #[Grammar( '<reference column list> ::=
        <column name list>' )]
    function ReferenceColumnList(): Entry {
        return rule( fn() => ColumnNameList() );
    }

    #[Grammar( '<column name list> ::=
        <column name> 
        [ { <comma> <column name> }... ]' )]
    function ColumnNameList(): Entry {
        return rule( fn() => commaList( ColumnName() ) );
    }

    #[Grammar( '<column name> ::=
        <identifier>' )]
    function ColumnName(): Entry {
        return rule( fn() => identifier() );
    }

    #[Grammar( '<comma> ::= ,' )]
    function Comma(): Entry {
        return rule( fn() => char( ',' ) );
    }

    #[Grammar( '<right paren> ::= )' )]
    function RightParen(): Entry {
        return rule( fn() => char( ')' ) );
    }

    #[Grammar( '<match type> ::= FULL | PARTIAL' )]
    function MatchType(): Entry {
        return rule( fn() => oneOf(
            word( 'FULL' ),
            word( 'PARTIAL' ),
        ) );
    }

    #[Grammar( '<referential triggered action> ::= 
            <update rule> 
            [ <delete rule> ] 
        |   <delete rule> 
            [ <update rule> ]' )]
    function ReferentialTriggeredAction(): Entry {
        return rule( fn() => oneOf(
            sequence(
                UpdateRule(),
                optional( DeleteRule() ),
            ),
            sequence(
                DeleteRule(),
                optional( UpdateRule() ),
            ),
        ) );
    }

    #[Grammar( '<update rule> ::= 
        ON UPDATE 
        <referential action>' )]
    function UpdateRule(): Entry {
        return rule( fn() => sequence(
            wordSequence( 'ON UPDATE' ),
            ReferentialAction(),
        ) );
    }

    #[Grammar( '<referential action> ::= CASCADE | SET NULL | SET DEFAULT | NO ACTION' )]
    function ReferentialAction(): Entry {
        return rule( fn() => oneOf(
            word( 'CASCADE' ),
            wordSequence( 'SET NULL' ),
            wordSequence( 'SET DEFAULT' ),
            wordSequence( 'NO ACTION' ),
        ) );
    }

    #[Grammar( '<delete rule> ::= 
        ON DELETE 
        <referential action>' )]
    function DeleteRule(): Entry {
        return rule( fn() => sequence(
            wordSequence( 'ON DELETE' ),
            ReferentialAction(),
        ) );
    }

    #[Grammar( '<check constraint definition> ::= 
        CHECK 
        <left paren> <search condition> <right paren>' )]
    function CheckConstraintDefinition(): Entry {
        return rule( fn() => sequence(
            word( 'CHECK' ),
            parenEnclosed( SearchCondition() ),
        ) );
    }

    #[Grammar( '<search condition> ::=
            <boolean term> 
        |   <search condition> 
            OR 
            <boolean term>' )]
    function SearchCondition(): Entry {
        return rule( fn() => oneOf(
            BooleanTerm(),
            sequence(
                SearchCondition(),
                word( 'OR' ),
                BooleanTerm(),
            )
        ) );
    }

    /**
     * @note Modified grammar to eliminate recursion
     */
    #[Grammar( '<boolean term> ::=
                <boolean factor> 
                AND 
                <boolean factor>
            |   <boolean factor>' )]
    function BooleanTerm(): Entry {
        return rule( fn() => oneOf(
            sequence(
                BooleanFactor(),
                word( 'AND' ),
                BooleanFactor(),
            ),
            BooleanFactor(),
        ) );
    }

    #[Grammar( '<boolean factor> ::= 
        [ NOT ] 
        <boolean test>' )]
    function BooleanFactor(): Entry {
        return rule( fn() => sequence(
            optionalWord( 'NOT' ),
            BooleanTest(),
        ) );
    }

    #[Grammar( '<boolean test> ::=
            <boolean primary> 
            [   IS 
                [ NOT ] 
                <truth value> ]' )]
    function BooleanTest(): Entry {
        return rule( fn() => sequence(
            BooleanPrimary(),
            optionalSequence(
                word( 'IS' ),
                optionalWord( 'NOT' ),
                TruthValue(),
            )
        ) );
    }

    #[Grammar( '<boolean primary> ::=
            <predicate> 
        |   <left paren> <search condition> <right paren>' )]
    function BooleanPrimary(): Entry {
        return rule( fn() => oneOf(
            Predicate(),
            optional( parenEnclosed( SearchCondition() ) ),
        ) );
    }

    #[Grammar( '<predicate> ::=
            <comparison predicate> 
        |   <between predicate> 
        |   <in predicate> 
        |   <like predicate> 
        |   <null predicate> 
        |   <quantified comparison predicate> 
        |   <exists predicate> 
        |   <match predicate> 
        |   <overlaps predicate>' )]
    function Predicate(): Entry {
        return rule( fn() => oneOf(
            ComparisonPredicate(),
            BetweenPredicate(),
            InPredicate(),
            LikePredicate(),
            NullPredicate(),
            QuantifiedComparisonPredicate(),
            ExistsPredicate(),
            MatchPredicate(),
            OverlapsPredicate(),
        ) );
    }

    #[Grammar( '<comparison predicate> ::=
            <row value constructor> 
            
            <comp op> 
            <row value constructor>' )]
    function ComparisonPredicate(): Entry {
        return rule( fn() => sequence(
            RowValueConstructor(),
            CompOp(),
            RowValueConstructor(),
        ) );
    }

    #[Grammar( '<row value constructor> ::=
            <row value constructor element> 
        |   <left paren> <row value constructor list> <right paren> 
        |   <row subquery>' )]
    function RowValueConstructor(): Entry {
        return rule( fn() => oneOf(
            parenEnclosed( RowValueConstructorList() ),
            RowValueConstructorElement(),
            RowSubquery(),
        ) );
    }

    #[Grammar( '<row value constructor element> ::=
            <value expression> 
        |   <null specification> 
        |   <default specification>' )]
    function RowValueConstructorElement(): Entry {
        return rule( fn() => oneOf(
            ValueExpression(),
            NullSpecification(),
            DefaultSpecification()
        ) );
    }

    #[Grammar( '<value expression> ::=
            <numeric value expression> 
        |   <string value expression> 
        |   <datetime value expression> 
        |   <interval value expression>' )]
    function ValueExpression(): Entry {
        return rule( fn() => oneOf(
            NumericValueExpression(),
            StringValueExpression(),
            DateTimeValueExpression(),
            IntervalValueExpression(),
        ) );
    }

    #[Grammar( '<numeric value expression> ::=
            <term> 
        |   <numeric value expression> 
            <plus sign> 
            <term> 
        |   <numeric value expression> 
            <minus sign> 
            <term>' )]
    function NumericValueExpression(): Entry {
        return rule( fn() => oneOf(
            Term(),
            sequence(
                NumericValueExpression(),
                PlusSign(),
                Term(),
            ),
            sequence(
                NumericValueExpression(),
                MinusSign(),
                Term(),
            ),
        ) );
    }

    #[Grammar( '<term> ::=
            <factor> 
        |   <term> 
            <asterisk> 
            <factor> 
        |   <term> 
            <solidus> 
            <factor>' )]
    function Term(): Entry {
        return rule( fn() => oneOf(
            Factor(),
            sequence(
                Term(),
                Asterisk(),
                Factor(),
            ),
            sequence(
                Term(),
                Solidus(),
                Factor(),
            ),
        ) );
    }

    #[Grammar( '<factor> ::= 
        [ <sign> ] 
        <numeric primary>' )]
    function Factor(): Entry {
        return rule( fn() => sequence(
            optional( Sign() ),
            NumericPrimary(),
        ) );
    }

    #[Grammar( '<sign> ::=
            <plus sign> 
        |   <minus sign>' )]
    function Sign(): Entry {
        return rule( fn() => oneOf(
            PlusSign(),
            MinusSign(),
        ) );
    }

    #[Grammar( '<plus sign> ::= +' )]
    function PlusSign(): Entry {
        return rule( fn() => char( '+' ) );
    }

    #[Grammar( '<numeric primary> ::=
            <value expression primary> 
        |   <numeric value function>' )]
    function NumericPrimary(): Entry {
        return rule( fn() => oneOf(
            ValueExpressionPrimary(),
            NumericValueFunction(),
        ) );
    }

    #[Grammar( '<value expression primary> ::=
            <unsigned value specification> 
        |   <column reference> 
        |   <set function specification> 
        |   <scalar subquery> 
        |   <case expression> 
        |   <left paren> <value expression> <right paren> 
        |   <cast specification>' )]
    function ValueExpressionPrimary(): Entry {
        return rule( fn() => oneOf(
            parenEnclosed( ValueExpression() ),
            UnsignedValueSpecification(),
            ColumnReference(),
            SetFunctionSpecification(),
            ScalarSubquery(),
            CaseExpression(),
            CastSpecification()
        ) );
    }

    #[Grammar( '<unsigned value specification> ::=
            <unsigned literal> 
        |   <general value specification>' )]
    function UnsignedValueSpecification(): Entry {
        return rule( fn() => oneOf(
            UnsignedLiteral(),
            GeneralValueSpecification(),
        ) );
    }

    #[Grammar( '<unsigned literal> ::=
            <unsigned numeric literal> 
        |   <general literal>' )]
    function UnsignedLiteral(): Entry {
        return rule( fn() => oneOf(
            UnsignedNumericLiteral(),
            GeneralLiteral(),
        ) );
    }

    #[Grammar( '<unsigned numeric literal> ::=
            <exact numeric literal> 
        |   <approximate numeric literal>' )]
    function UnsignedNumericLiteral(): Entry {
        return rule( fn() => oneOf(
            ExactNumericLiteral(),
            ApproximateNumericLiteral(),
        ) );
    }

    #[Grammar( '<exact numeric literal> ::=
            <unsigned integer> 
            [ <period> 
                [ <unsigned integer> ] 
            ] 
        |
            <period> 
            <unsigned integer>' )]
    function ExactNumericLiteral(): Entry {
        return rule( fn() => oneOf(
            sequence(
                UnsignedInteger(),
                optionalSequence(
                    Period(),
                    optional( UnsignedInteger() ),
                )
            ),
            sequence(
                Period(),
                UnsignedInteger(),
            ),
        ) );
    }

    #[Grammar( '<unsigned integer> ::=
            <digit> ...' )]
    function UnsignedInteger(): Entry {
        return rule( fn() => oneOrMore(
            digit(),
        ) );
    }

    #[Grammar( '<approximate numeric literal> ::=
        <mantissa> 
        E 
        <exponent>' )]
    function ApproximateNumericLiteral(): Entry {
        return rule( fn() => sequence(
            Mantissa(),
            char( 'E' ),
            Exponent()
        ) );
    }

    #[Grammar( '<mantissa> ::=
        <exact numeric literal>' )]
    function Mantissa(): Entry {
        return rule( fn() => ExactNumericLiteral() );
    }

    #[Grammar( '<exponent> ::=
        <signed integer>' )]
    function Exponent(): Entry {
        return rule( fn() => SignedInteger() );
    }

    #[Grammar( '<signed integer> ::= 
        [ <sign> ] 
        <unsigned integer>' )]
    function SignedInteger(): Entry {
        return rule( fn() => sequence(
            optional( Sign() ),
            UnsignedInteger(),
        ) );
    }

    #[Grammar( '<general literal> ::=
            <character string literal> |   <national character string literal> |   <bit string literal> |   <hex string literal> |   <datetime literal> |   <interval literal>' )]
    function GeneralLiteral(): Entry {
        return rule( fn() => oneOf(
            CharacterStringLiteral(),
            NationalCharacterStringLiteral(),
            BitStringLiteral(),
            HexStringLiteral(),
            DateTimeLiteral(),
            IntervalLiteral(),
        ) );
    }

    #[Grammar( '<character string literal> ::= 
        [   
            <introducer>
            <character set specification> 
        ] 
        <quote> 
        [ <character representation>... ] 
        <quote> 
        [ {
            <separator>... 
            <quote> 
            [ <character representation>... ] 
            <quote> 
        }... ]' )]
    function CharacterStringLiteral(): Entry {
        return rule( fn() => sequence(
            optionalSequence(
                Introducer(),
                CharacterSetSpecification(),
            ),
            Quote(),
            zeroOrMore( CharacterRepresentation() ),
            Quote(),
            zeroOrMore(
                oneOrMore( Separator() ),
                Quote(),
                zeroOrMore( CharacterRepresentation() ),
                Quote(),
            ),
        ) );
    }

    #[Grammar( '<character representation> ::= 
            <nonquote character> 
        |   <quote symbol>' )]
    function CharacterRepresentation(): Entry {
        return rule( fn() => oneOf(
            NonQuoteCharacter(),
            QuoteSymbol(),
        ) );
    }

    #[Grammar( '<quote symbol> ::= <quote> <quote>' )]
    function QuoteSymbol(): Entry {
        return rule( fn() => sequence(
            Quote(),
            Quote(),
        ) );
    }

    #[Grammar( '<separator> ::= 
        {       <comment> 
            |   <space> 
            |   <newline> }...' )]
    function Separator(): Entry {
        return rule( fn() => oneOrMore(
            oneOf(
                Comment(),
                Space(),
                NewLine(),
            ),
        ) );
    }

    #[Grammar( '<national character string literal> ::= 
        N 
        <quote> 
        [ <character representation> ... ] 
        <quote> 
        [ { 
            <separator>... 
            <quote> 
            [ <character representation>... ] 
            <quote> 
        }... ]' )]
    function NationalCharacterStringLiteral(): Entry {
        return rule( fn() => sequence(
            char( 'N' ),
            Quote(),
            zeroOrMore( CharacterRepresentation() ),
            Quote(),
            zeroOrMore(
                oneOrMore( Separator() ),
                Quote(),
                zeroOrMore( CharacterRepresentation() ),
                Quote(),
            ),
        ) );
    }

    #[Grammar( '<bit string literal> ::= 
        B 
        <quote> 
        [ <bit> ... ] 
        <quote> 
        [ {
            <separator>... 
            <quote> 
            [ <bit>... ] 
            <quote> 
        }... ]' )]
    function BitStringLiteral(): Entry {
        return rule( fn() => sequence(
            char( 'B' ),
            Quote(),
            zeroOrMore( Bit() ),
            Quote(),
            zeroOrMore(
                oneOrMore( Separator() ),
                Quote(),
                zeroOrMore( Bit() ),
                Quote(),
            ),
        ) );
    }

    #[Grammar( '<bit> ::= 0 | 1' )]
    function Bit(): Entry {
        return rule( fn() => oneOfChars( '01' ) );
    }

    #[Grammar( '<hex string literal> ::= 
        X 
        <quote> 
        [ <hexit> ... ] 
        <quote> 
        [ { 
            <separator>... 
            <quote> 
            [ <hexit>... ] 
            <quote> 
        }... ]' )]
    function HexStringLiteral(): Entry {
        return rule( fn() => sequence(
            char( 'X' ),
            Quote(),
            zeroOrMore( Hexit() ),
            Quote(),
            oneOrMore(
                oneOrMore( Separator() ),
                Quote(),
                zeroOrMore( Hexit() ),
                Quote(),
            ),
        ) );
    }

    #[Grammar( '<hexit> ::=
        <digit> 
        A | B | C | D | E | F | a | b | c | d | e | f' )]
    function Hexit(): Entry {
        return rule( fn() => oneOf(
            digit(),
            oneOfChars( 'ABCDEF' ),
            oneOfChars( 'abcdef' ),
        ) );
    }

    #[Grammar( '<datetime literal> ::=
            <date literal> 
        |   <time literal> 
        |   <timestamp literal>' )]
    function DateTimeLiteral(): Entry {
        return rule( fn() => oneOf(
            DateLiteral(),
            TimeLiteral(),
            TimestampLiteral(),
        ) );
    }

    #[Grammar( '<date literal> ::= 
        DATE 
        <date string>' )]
    function DateLiteral(): Entry {
        return rule( fn() => sequence(
            word( 'DATE' ),
            DateString(),
        ) );
    }

    #[Grammar( '<date string> ::=
        <quote> 
        <date value> 
        <quote>' )]
    function DateString(): Entry {
        return rule( fn() => sequence(
            Quote(),
            DateValue(),
            Quote()
        ) );
    }

    #[Grammar( '<date value> ::=
        <years value> 
        <minus sign> 
        <months value> 
        <minus sign> 
        <days value>' )]
    function DateValue(): Entry {
        return rule( fn() => sequence(
            YearsValue(),
            MinusSign(),
            MonthsValue(),
            MinusSign(),
            DaysValue(),
        ) );
    }

    #[Grammar( '<years value> ::=
        <datetime value>' )]
    function YearsValue(): Entry {
        return rule( fn() => DateTimeValue() );
    }

    #[Grammar( '<datetime value> ::=
        <unsigned integer>' )]
    function DateTimeValue(): Entry {
        return rule( fn() => UnsignedInteger() );
    }

    #[Grammar( '<months value> ::=
        <datetime value>' )]
    function MonthsValue(): Entry {
        return rule( fn() => DateTimeValue() );
    }

    #[Grammar( '<days value> ::=
        <datetime value>' )]
    function DaysValue(): Entry {
        return rule( fn() => DateTimeValue() );
    }

    #[Grammar( '<time literal> ::= 
        TIME 
        <time string>' )]
    function TimeLiteral(): Entry {
        return rule( fn() => sequence(
            word( 'TIME' ),
            TimeString(),
        ) );
    }

    #[Grammar( '<time string> ::=
        <quote> 
        <time value> 
        [ <time zone interval> ] 
        <quote>' )]
    function TimeString(): Entry {
        return rule( fn() => sequence(
            Quote(),
            TimeValue(),
            optional( TimeZoneInterval() ),
            Quote(),
        ) );
    }

    #[Grammar( '<time value> ::=
        <hours value> 
        <colon> 
        <minutes value> 
        <colon> 
        <seconds value>' )]
    function TimeValue(): Entry {
        return rule( fn() => sequence(
            HoursValue(),
            Colon(),
            MinutesValue(),
            Colon(),
            SecondsValue(),
        ) );
    }

    #[Grammar( '<hours value> ::=
        <datetime value>' )]
    function HoursValue(): Entry {
        return rule( fn() => DateTimeValue() );
    }

    #[Grammar( '<colon> ::= :' )]
    function Colon(): Entry {
        return rule( fn() => char( ':' ) );
    }

    #[Grammar( '<minutes value> ::=
        <datetime value>' )]
    function MinutesValue(): Entry {
        return rule( fn() => DateTimeValue() );
    }

    #[Grammar( '<seconds value> ::=
        <seconds integer value> 
        [ <period> 
            [ <seconds fraction> ] ]' )]
    function SecondsValue(): Entry {
        return rule( fn() => sequence(
            SecondsIntegerValue(),
            optionalSequence(
                Period(),
                optional(
                    SecondsFraction()
                ),
            ),
        ) );
    }

    #[Grammar( '<seconds integer value> ::=
        <unsigned integer>' )]
    function SecondsIntegerValue(): Entry {
        return rule( fn() => UnsignedInteger() );
    }

    #[Grammar( '<seconds fraction> ::=
        <unsigned integer>' )]
    function SecondsFraction(): Entry {
        return rule( fn() => UnsignedInteger() );
    }

    #[Grammar( '<timestamp literal> ::= 
        TIMESTAMP 
        <timestamp string>' )]
    function TimestampLiteral(): Entry {
        return rule( fn() => oneOf(
            word( 'TIMESTAMP' ),
            TimestampString(),
        ) );
    }

    #[Grammar( '<timestamp string> ::=
        <quote> 
        <date value> 
        <space> 
        <time value> 
        [ <time zone interval> ] 
        <quote>' )]
    function TimestampString(): Entry {
        return rule( fn() => sequence(
            Quote(),
            DateValue(),
            Space(),
            TimeValue(),
            optional( TimeZoneInterval() ),
            Quote(),
        ) );
    }

    #[Grammar( '<space> ::= 
        !! space character in character set in use' )]
    function Space(): Entry {
        return rule( fn() => char( ' ' ) );
    }

    #[Grammar( '<time zone interval> ::=
        <sign> 
        <hours value> 
        <colon> 
        <minutes value>' )]
    function TimeZoneInterval(): Entry {
        return rule( fn() => sequence(
            Sign(),
            HoursValue(),
            Colon(),
            MinutesValue(),
        ) );
    }

    #[Grammar( '<interval literal> ::= 
        INTERVAL 
        [ <sign> ] 
        <interval string> 
        <interval qualifier>' )]
    function IntervalLiteral(): Entry {
        return rule( fn() => sequence(
            word( 'INTERVAL' ),
            optional( Sign() ),
            IntervalString(),
            IntervalQualifier(),
        ) );
    }

    #[Grammar( '<interval string> ::=
        <quote> 
        { <year-month literal> |   <day-time literal> } 
        <quote>' )]
    function IntervalString(): Entry {
        return rule( fn() => sequence(
            Quote(),
            oneOf(
                YearMonthLiteral(),
                DayTimeLiteral(),
            ),
            Quote(),
        ) );
    }

    #[Grammar( '<year-month literal> ::=
            <years value> 
        |   [ <years value> <minus sign> ] 
            <months value>' )]
    function YearMonthLiteral(): Entry {
        return rule( fn() => oneOf(
            YearsValue(),
            sequence(
                optionalSequence(
                    YearsValue(),
                    MinusSign(),
                ),
                MonthsValue(),
            )
        ) );
    }

    #[Grammar( '<day-time literal> ::=
            <day-time interval> 
        |   <time interval>' )]
    function DayTimeLiteral(): Entry {
        return rule( fn() => oneOf(
            DayTimeInterval(),
            TimeInterval(),
        ) );
    }

    #[Grammar( '<day-time interval> ::=
        <days value> 
        [ <space> <hours value> 
            [ <colon> <minutes value> 
                [ <colon> <seconds value> ] ] ]' )]
    function DayTimeInterval(): Entry {
        return rule( fn() => sequence(
            DaysValue(),
            optionalSequence(
                Space(),
                HoursValue(),
                optionalSequence(
                    Colon(),
                    MinutesValue(),
                    optionalSequence(
                        Colon(),
                        SecondsValue(),
                    ),
                ),
            ),
        ) );
    }

    #[Grammar( '<time-interval> ::=
            <hours value> 
            [ <colon> <minutes value> [ <colon> <seconds value> ] ] 
        |   <minutes value> 
            [ <colon> <seconds value> ] 
        |   <seconds value>' )]
    function TimeInterval(): Entry {
        return rule( fn() => oneOf(
            sequence(
                HoursValue(),
                optionalSequence(
                    Colon(),
                    MinutesValue(),
                    optionalSequence(
                        Colon(),
                        SecondsValue(),
                    ),
                )
            ),
            sequence(
                MinutesValue(),
                optionalSequence(
                    Colon(),
                    SecondsValue(),
                ),
            ),
            SecondsValue()
        ) );
    }

    #[Grammar( '<interval qualifier> ::=
            <start field> 
            TO 
            <end field> 
        |   <single datetime field>' )]
    function IntervalQualifier(): Entry {
        return rule( fn() => oneOf(
            sequence(
                StartField(),
                word( 'TO' ),
                EndField(),
            ),
            SingleDateTimeField(),
        ) );
    }

    #[Grammar( '<start field> ::= 
        <non - second datetime field> 
        [   <left paren> <interval leading field precision> 
            <right paren> ]' )]
    function StartField(): Entry {
        return rule( fn() => sequence(
            NonSecondDateTimeField(),
            optional(
                parenEnclosed( IntervalLeadingFieldPrecision() ),
            ),
        ) );
    }

    const NON_SECOND_DATE_TIME_FIELD = 'YEAR | MONTH | DAY | HOUR | MINUTE';
    #[Grammar( '<non - second datetime field> ::= ' .
               NON_SECOND_DATE_TIME_FIELD )]
    function NonSecondDateTimeField(): Entry {
        return rule( fn() => oneOfWords(
            ...splitWords( NON_SECOND_DATE_TIME_FIELD )
        ) );
    }

    #[Grammar( '<interval leading field precision> ::=
        <unsigned integer>' )]
    function IntervalLeadingFieldPrecision(): Entry {
        return rule( fn() => UnsignedInteger() );
    }

    #[Grammar( '<end field> ::= 
            <non - second datetime field> 
        |   SECOND [ <left paren> <interval fractional seconds precision> <right paren> ]' )]
    function EndField(): Entry {
        return rule( fn() => oneOf(
            NonSecondDateTimeField(),
            sequence(
                word( 'SECOND' ),
                optional(
                    parenEnclosed( IntervalFractionalSecondsPrecision() ),
                ),
            ),
        ) );
    }

    #[Grammar( '<interval fractional seconds precision> ::=
        <unsigned integer>' )]
    function IntervalFractionalSecondsPrecision(): Entry {
        return rule( fn() => UnsignedInteger() );
    }

    #[Grammar( '<single datetime field> ::= 
            <non - second datetime field> 
            [ <left paren> <interval leading field precision> <right paren> ] 
        |   SECOND 
            [ <left paren> <interval leading field precision> [ <comma> <interval fractional seconds precision> ] <right paren> ]' )]
    function SingleDateTimeField(): Entry {
        return rule( fn() => oneOf(
            sequence(
                word( 'SECOND' ),
                optional( parenEnclosed( commaList( IntervalLeadingFieldPrecision() ) ) ),
            ),
            sequence(
                NonSecondDateTimeField(),
                optional( parenEnclosed( IntervalLeadingFieldPrecision() ) ),
            ),
        ) );
    }

    const GENERAL_VALUE_SPECIFICATION_WORDS = 'USER | CURRENT_USER | SESSION_USER | SYSTEM_USER | VALUE';
    #[Grammar( '<general value specification> ::= 
            <parameter specification> 
        |   <dynamic parameter specification> 
        |   <variable specification> 
        | ' . GENERAL_VALUE_SPECIFICATION_WORDS )]
    function GeneralValueSpecification(): Entry {
        return rule( fn() => oneOf(
            ParameterSpecification(),
            DynamicParameterSpecification(),
            VariableSpecification(),
            oneOfWords( ...splitWords(
                GENERAL_VALUE_SPECIFICATION_WORDS
            ) ),
        ) );
    }

    #[Grammar( '<parameter specification> ::=
        <parameter name> 
        [ <indicator parameter> ]' )]
    function ParameterSpecification(): Entry {
        return rule( fn() => sequence(
            ParameterName(),
            IndicatorParameter(),
        ) );
    }

    #[Grammar( '<parameter name> ::=
        <colon> 
        <identifier>' )]
    function ParameterName(): Entry {
        return rule( fn() => sequence(
            Colon(),
            identifier(),
        ) );
    }

    #[Grammar( '<indicator parameter> ::= 
        [ INDICATOR ] 
        <parameter name>' )]
    function IndicatorParameter(): Entry {
        return rule( fn() => sequence(
            optionalWord( 'INDICATOR' ),
            ParameterName(),
        ) );
    }

    #[Grammar( '<dynamic parameter specification> ::=
            <question mark>' )]
    function DynamicParameterSpecification(): Entry {
        return rule( fn() => QuestionMark() );
    }

    function QuestionMark(): Entry {
        return rule( fn() => char( '?' ) );
    }

    #[Grammar( '<variable specification> ::=
        <embedded variable name> 
        [ <indicator variable> ]' )]
    function VariableSpecification(): Entry {
        return rule( fn() => sequence(
            EmbeddedVariableName(),
            optional( IndicatorVariable() ),
        ) );
    }

    #[Grammar( '<embedded variable name> ::=
        <colon>
        <host identifier>' )]
    function EmbeddedVariableName(): Entry {
        return rule( fn() => sequence(
            Colon(),
            HostIdentifier(),
        ) );
    }

    #[Grammar( '<host identifier> ::= 
            <Ada host identifier> 
        |   <C host identifier> 
        |   <Cobol host identifier> 
        |   <Fortran host identifier> 
        |   <MUMPS host identifier> 
        |   <Pascal host identifier> 
        |   <PL / I host identifier>' )]
    function HostIdentifier(): Entry {
        return rule( fn() => oneOf(
            AdaHostIdentifier(),
            CHostIdentifier(),
            CobolHostIdentifier(),
            FortranHostIdentifier(),
            MumpsHostIdentifier(),
            PascalHostIdentifier(),
            PLIHostIdentifier(),
        ) );
    }

    #[Grammar( '<Ada host identifier> ::= ! ! See syntax  rules' )]
    function AdaHostIdentifier(): Entry {
        return rule( fn() => customParser() );
    }

    #[Grammar( '<C host identifier> ::= ! ! See syntax  rules' )]
    function CHostIdentifier(): Entry {
        return rule( fn() => customParser() );
    }

    #[Grammar( '<Cobol host identifier> ::= ! ! See syntax   rules' )]
    function CobolHostIdentifier(): Entry {
        return rule( fn() => customParser() );
    }

    #[Grammar( '<Fortran host identifier> ::= ! ! See syntax   rules' )]
    function FortranHostIdentifier(): Entry {
        return rule( fn() => customParser() );
    }

    #[Grammar( '<MUMPS host identifier> ::= ! ! See syntax   rules' )]
    function MumpsHostIdentifier(): Entry {
        return rule( fn() => customParser() );
    }

    #[Grammar( '<Pascal host identifier> ::= ! ! See syntax   rules' )]
    function PascalHostIdentifier(): Entry {
        return rule( fn() => customParser() );
    }

    #[Grammar( '<PL / I host identifier> ::= ! ! See syntax rules' )]
    function PLIHostIdentifier(): Entry {
        return rule( fn() => customParser() );
    }

    #[Grammar( '<indicator variable> ::= 
        [ INDICATOR ] 
        <embedded variable name>' )]
    function IndicatorVariable(): Entry {
        return rule( fn() => sequence(
            optionalWord( 'INDICATOR' ),
            EmbeddedVariableName(),
        ) );
    }

    #[Grammar( '<column reference> ::= 
        [ <qualifier> <period> ] 
        <column name>' )]
    function ColumnReference(): Entry {
        return rule( fn() => sequence(
            optionalSequence(
                Qualifier(),
                Period(),
            ),
            ColumnName(),
        ) );
    }

    #[Grammar( '<qualifier> ::=
            <table name> 
        |   <correlation name>' )]
    function Qualifier(): Entry {
        return rule( fn() => oneOf(
            TableName(),
            CorrelationName(),
        ) );
    }

    #[Grammar( '<correlation name> ::=
            <identifier>' )]
    function CorrelationName(): Entry {
        return rule( fn() => identifier() );
    }

    #[Grammar( '<set function specification> ::= 
            COUNT 
            <left paren> <asterisk> <right paren> 
        |   <general set function>' )]
    function SetFunctionSpecification(): Entry {
        return rule( fn() => oneOf(
            sequence(
                word( 'COUNT' ),
                parenEnclosed( Asterisk() ),
            ),
            GeneralSetFunction(),
        ) );
    }

    function Asterisk(): Entry {
        return rule( fn() => char( '' ) );
    }

    #[Grammar( '<general set function> ::= 
        <set function type> 
        <left paren> 
        [ <set quantifier> ] 
        <value expression> 
        <right paren>' )]
    function GeneralSetFunction(): Entry {
        return rule( fn() => sequence(
            SetFunctionType(),
            LeftParen(),
            optional( SetQuantifier() ),
            ValueExpression(),
            RightParen(),
        ) );
    }

    const SET_FUNCTION_TYPE = 'AVG | MAX | MIN | SUM | COUNT';
    #[Grammar( '<set function type> ::= ' . SET_FUNCTION_TYPE )]
    function SetFunctionType(): Entry {
        return rule( fn() => oneOfWords(
            ...splitWords( SET_FUNCTION_TYPE )
        ) );
    }

    const SET_QUANTIFIER = 'DISTINCT | ALL | DISTINCTROW';
    #[Grammar( '<set quantifier> ::= ' . SET_QUANTIFIER )]
    function SetQuantifier(): Entry {
        return rule( fn() => oneOfWords( ...splitWords( SET_QUANTIFIER ) ) );
    }

    #[Grammar( '<scalar subquery> ::=
            <subquery>' )]
    function ScalarSubquery(): Entry {
        return rule( fn() => Subquery() );
    }

    #[Grammar( '<subquery> ::=
            <left paren> <query expression> <right paren>' )]
    function Subquery(): Entry {
        return rule( fn() => parenEnclosed( QueryExpression() ) );
    }

    #[Grammar( '<query expression> ::=
            <non - join query expression> 
        |   <joined table>' )]
    function QueryExpression(): Entry {
        return rule( fn() => oneOf(
            NonJoinQueryExpression(),
            JoinedTable()
        ) );
    }

    /**
     * @note Implementation deviates from grammar for conciseness and less back - tracking
     */
    #[Grammar( '<non - join query expression> ::=
            <non - join query term> 
        |   <query expression> 
            UNION 
            [ ALL ] 
            [ <corresponding spec> ] 
            <query term> 
        |   <query expression> 
            EXCEPT 
            [ ALL ] 
            [ <corresponding spec> ] 
            <query term>' )]
    function NonJoinQueryExpression(): Entry {
        return rule( fn() => oneOf(
            NonJoinQueryTerm(),
            sequence(
                QueryExpression(),
                word( 'UNION' ),
                optionalWord( 'ALL' ),
                optional( CorrespondingSpec() ),
                QueryTerm(),
            ),
            sequence(
                QueryExpression(),
                word( 'EXCEPT' ),
                optionalWord( 'ALL' ),
                optional( CorrespondingSpec() ),
                QueryTerm(),
            ),
        ) );
    }

    #[Grammar( '<non - join query term> ::= 
            <non - join query primary> 
        |   <query term> 
            INTERSECT 
            [ ALL ] 
            [ <corresponding spec> ] 
            <query primary>' )]
    function NonJoinQueryTerm(): Entry {
        return rule( fn() => oneOf(
            NonJoinQueryPrimary(),
            sequence(
                QueryTerm(),
                word( 'INTERSECT' ),
                optionalWord( 'ALL' ),
                optional( CorrespondingSpec() ),
                QueryPrimary(),
            ),
        ) );
    }

    #[Grammar( '<non - join query primary> ::= 
            <simple table> 
        |   <left paren> <non - join query expression> <right paren>' )]
    function NonJoinQueryPrimary(): Entry {
        return rule( fn() => oneOf(
            SimpleTable(),
            parenEnclosed( NonJoinQueryExpression() ),
        ) );
    }

    #[Grammar( '<simple table> ::= 
            <query specification> 
        |   <table value constructor> 
        |   <explicit table>' )]
    function SimpleTable(): Entry {
        return rule( fn() => oneOf(
            QuerySpecification(),
            TableValueConstructor(),
            ExplicitTable(),
        ) );
    }

    #[Grammar( '<query specification> ::= 
        SELECT 
        [ <set quantifier> ] 
        <select list> 
        <table expression>' )]
    function QuerySpecification(): Entry {
        return rule( fn() => sequence(
            word( 'SELECT' ),
            optional( SetQuantifier() ),
            SelectList(),
            TableExpression(),
        ) );
    }

    #[Grammar( '<select list> ::=
            <asterisk> 
        |   <select sublist> 
            [ {<comma> <select sublist> }... ]' )]
    function SelectList(): Entry {
        return rule( fn() => oneOf(
            Asterisk(),
            commaList( SelectSublist() ),
        ) );
    }

    #[Grammar( '<select sublist> ::=
            <derived column> 
        |   <qualifier> <period> <asterisk>' )]
    function SelectSublist(): Entry {
        return rule( fn() => oneOf(
            DerivedColumn(),
            sequence(
                Qualifier(),
                Period(),
                Asterisk(),
            )
        ) );
    }

    #[Grammar( '<derived column> ::=
        <value expression> 
        [ <as clause> ]' )]
    function DerivedColumn(): Entry {
        return rule( fn() => sequence(
            ValueExpression(),
            optional( AsClause() ),
        ) );
    }

    #[Grammar( '<as clause> ::= 
        [ AS ] 
        <column name>' )]
    function AsClause(): Entry {
        return rule( fn() => sequence(
            optionalWord( 'AS' ),
            ColumnName(),
        ) );
    }

    #[Grammar( '<table expression> ::= 
        <from clause> 
        [ <where clause> ] 
        [ <group by clause> ] 
        [ <having clause> ]' )]
    function TableExpression(): Entry {
        return rule( fn() => sequence(
            FromClause(),
            optional( WhereClause() ),
            optional( GroupByClause() ),
            optional( HavingClause() ),
        ) );
    }

    #[Grammar( '<from clause> ::= 
        FROM 
        <table reference> 
        [ {<comma> <table reference> }... ]' )]
    function FromClause(): Entry {
        return rule( fn() => sequence(
            word( 'FROM' ),
            commaList( TableReference() ),
        ) );
    }

    #[Grammar( '<table reference> ::=
            <table name> [ <correlation specification> ] 
        |   <derived table> <correlation specification> 
        |   <joined table>' )]
    function TableReference(): Entry {
        return rule( fn() => oneOf(
            sequence(
                TableName(),
                CorrelationSpecification()
            ),
            sequence(
                DerivedTable(),
                CorrelationSpecification()
            ),
            JoinedTable(),
        ) );
    }

    #[Grammar( '<correlation specification> ::= 
        [ AS ] 
        <correlation name> 
        [ <left paren> <derived column list> <right paren> ]' )]
    /**
     * @note <correlation specification> does not appear in the ISO/IEC grammar.
     *       The notation is written out longhand several times, instead.
     */
    function CorrelationSpecification(): Entry {
        return rule( fn() => sequence(
            optionalWord( 'AS' ),
            CorrelationName(),
            optional( parenEnclosed( DerivedColumnList() ) )
        ) );
    }

    #[Grammar( '<derived column list> ::=
        <column name list>' )]
    function DerivedColumnList(): Entry {
        return rule( fn() => ColumnNameList() );
    }

    #[Grammar( '<derived table> ::=
        <table subquery>' )]
    function DerivedTable(): Entry {
        return rule( fn() => TableSubquery() );
    }

    #[Grammar( '<table subquery> ::=
        <subquery>' )]
    function TableSubquery(): Entry {
        return rule( fn() => Subquery() );
    }

    #[Grammar( '<joined table> ::=
            <cross join> 
        |   <qualified join> 
        |   <left paren> <joined table> <right paren>' )]
    function JoinedTable(): Entry {
        return rule( fn() => oneOf(
            CrossJoin(),
            QualifiedJoin(),
            parenEnclosed( JoinedTable() ),
        ) );
    }

    #[Grammar( '<cross join> ::=
        <table reference> 
        CROSS JOIN 
        <table reference>' )]
    function CrossJoin(): Entry {
        return rule( fn() => sequence(
            TableReference(),
            wordSequence( 'CROSS JOIN' ),
            TableReference(),
        ) );
    }

    #[Grammar( '<qualified join> ::=
        <table reference> 
        [ NATURAL ] 
        [ <join type> ] 
        JOIN 
        <table reference> 
        [ <join specification> ]' )]
    function QualifiedJoin(): Entry {
        return rule( fn() => sequence(
            TableReference(),
            optional( word( 'NATURAL' ) ),
            optional( JoinType() ),
            word( 'JOIN' ),
            TableReference(),
            optional( JoinSpecification() ),
        ) );
    }

    #[Grammar( '<join type> ::= 
            INNER 
        |   <outer join type> [ OUTER ] 
        |   UNION' )]
    function JoinType(): Entry {
        return rule( fn() => oneOf(
            word( 'INNER' ),
            sequence(
                OuterJoinType(),
                optionalWord( 'OUTER' ),
            ),
            word( 'UNION' )
        ) );
    }
    const OUTER_JOIN_TYPE = 'LEFT | RIGHT | FULL';
    #[Grammar( '<outer join type> ::= ' .
               OUTER_JOIN_TYPE )]
    function OuterJoinType(): Entry {
        return rule( fn() => oneOfWords(
            ...splitWords( OUTER_JOIN_TYPE )
        ) );
    }

    #[Grammar( '<join specification> ::=
            <join condition> 
        |   <named columns join>' )]
    function JoinSpecification(): Entry {
        return rule( fn() => oneOf(
            JoinCondition(),
            NamedColumnsJoin()
        ) );
    }

    #[Grammar( '<join condition> ::= 
        ON 
        <search condition>' )]
    function JoinCondition(): Entry {
        return rule( fn() => sequence(
            word( 'ON' ),
            SearchCondition()
        ) );
    }

    #[Grammar( '<named columns join> ::= 
        USING 
        <left paren> <join column list> <right paren>' )]
    function NamedColumnsJoin(): Entry {
        return rule( fn() => sequence(
            word( 'USING' ),
            parenEnclosed( JoinColumnList() ),
        ) );
    }

    #[Grammar( '<join column list> ::= <column name list>' )]
    function JoinColumnList(): Entry {
        return rule( fn() => ColumnNameList() );
    }

    #[Grammar( '<where clause> ::= 
        WHERE 
        <search condition>' )]
    function WhereClause(): Entry {
        return rule( fn() => sequence(
            word( 'WHERE' ),
            SearchCondition(),
        ) );
    }

    #[Grammar( '<group by clause> ::= 
        GROUP BY 
        <grouping column reference list>' )]
    function GroupByClause(): Entry {
        return rule( fn() => sequence(
            wordSequence( 'GROUP BY' ),
            GroupingColumnReferenceList(),
        ) );
    }

    #[Grammar( '<grouping column reference list> ::= 
        <grouping column reference> 
        [ {<comma> <grouping column reference>} ... ]' )]
    function GroupingColumnReferenceList(): Entry {
        return rule( fn() => commaList( GroupingColumnReference() ) );
    }

    #[Grammar( '<grouping column reference> ::=
        <column reference> 
        [ <collate clause> ]' )]
    function GroupingColumnReference(): Entry {
        return rule( fn() => sequence(
            ColumnReference(),
            optional( CollateClause() ),
        ) );
    }

    #[Grammar( '<collate clause> ::= 
        COLLATE 
        <collation name>' )]
    function CollateClause(): Entry {
        return rule( fn() => sequence(
            word( 'COLLATE' ),
            CollationName(),
        ) );
    }

    #[Grammar( '<collation name> ::=
        <qualified name>' )]
    function CollationName(): Entry {
        return rule( fn() => QualifiedName() );
    }

    #[Grammar( '<having clause> ::= 
        HAVING 
        <search condition>' )]
    function HavingClause(): Entry {
        return rule( fn() => sequence(
            word( 'HAVING' ),
            SearchCondition(),
        ) );
    }

    #[Grammar( '<table value constructor> ::= 
        VALUES 
        <table value constructor list>' )]
    function TableValueConstructor(): Entry {
        return rule( fn() => sequence(
            word( 'VALUES' ),
            TableValueConstructorList()
        ) );
    }

    #[Grammar( '<table value constructor list> ::= 
        <row value constructor> 
        [ {<comma> <row value constructor> }... ]' )]
    function TableValueConstructorList(): Entry {
        return rule( fn() => commaList( RowValueConstructor() ) );
    }

    #[Grammar( '<explicit table> ::= 
        TABLE 
        <table name>' )]
    function ExplicitTable(): Entry {
        return rule( fn() => sequence(
            word( 'TABLE' ),
            TableName()
        ) );
    }

    #[Grammar( '<query term> ::=
            <non - join query term> 
        |   <joined table>' )]
    function QueryTerm(): Entry {
        return rule( fn() => oneOf(
            NonJoinQueryTerm(),
            JoinedTable(),
        ) );
    }

    #[Grammar( '<all> ::= ALL' )]
    function All(): Entry {
        return rule( fn() => word( 'ALL' ) );
    }

    #[Grammar( '<corresponding spec> ::= 
        CORRESPONDING 
        [   BY 
            <left paren> <corresponding column list> <right paren> ]' )]
    function CorrespondingSpec(): Entry {
        return rule( fn() => oneOf(
            word( 'CORRESPONDING' ),
            optionalSequence(
                word( 'BY' ),
                parenEnclosed( CorrespondingColumnList() ),
            ),
        ) );
    }

    #[Grammar( '<corresponding column list> ::=
            <column name list>' )]
    function CorrespondingColumnList(): Entry {
        return rule( fn() => ColumnNameList() );
    }

    #[Grammar( '<query primary> ::= 
            <non - join query primary> 
        |   <joined table>' )]
    function QueryPrimary(): Entry {
        return rule( fn() => oneOf(
            NonJoinQueryPrimary(),
            JoinedTable(),
        ) );
    }

    #[Grammar( '<case expression> ::=
            <case abbreviation> 
        |   <case specification>' )]
    function CaseExpression(): Entry {
        return rule( fn() => oneOf(
            CaseAbbreviation(),
            CaseSpecification(),
        ) );
    }

    #[Grammar( '<case abbreviation> ::= 
            NULLIF 
            <left paren> 
            <value expression> 
            <comma> 
            <value expression> 
            <right paren> 
        |   COALESCE 
            <left paren> <value expression> { <comma> <value expression> }... <right paren>' )]
    function CaseAbbreviation(): Entry {
        return rule( fn() => oneOf(
            sequence(
                word( 'NULLIF' ),
                LeftParen(),
                ValueExpression(),
                Comma(),
                ValueExpression(),
                RightParen()
            ),
            sequence(
                word( 'COALESCE' ),
                parenEnclosed( commaList( ValueExpression() ) ),
            ),
        ) );
    }

    #[Grammar( '<case specification> ::=
            <simple case> 
        |   <searched case>' )]
    function CaseSpecification(): Entry {
        return rule( fn() => oneOf(
            SimpleCase(),
            SearchedCase(),
        ) );
    }

    #[Grammar( '<simple case> ::= 
        CASE 
        <case operand> 
        <simple when clause>... 
        [ <else clause> ] 
        END' )]
    function SimpleCase(): Entry {
        return rule( fn() => sequence(
            word( 'CASE' ),
            CaseOperand(),
            SimpleWhenClause(),
            optional( ElseClause() ),
            word( 'END' ),
        ) );
    }

    #[Grammar( '<case operand> ::= <value expression>' )]
    function CaseOperand(): Entry {
        return rule( fn() => ValueExpression() );
    }

    #[Grammar( '<simple when clause> ::= 
        WHEN 
        <when operand> 
        THEN 
        <result>' )]
    function SimpleWhenClause(): Entry {
        return rule( fn() => sequence(
            word( 'WHEN' ),
            WhenOperand(),
            word( 'THEN' ),
            Result(),
        ) );
    }

    #[Grammar( '<when operand> ::= <value expression>' )]
    function WhenOperand(): Entry {
        return rule( fn() => ValueExpression() );
    }

    #[Grammar( '<result> ::=
            <result expression> 
        |   NULL' )]
    function Result(): Entry {
        return rule( fn() => oneOf(
            ResultExpression(),
            word( 'NULL' ),
        ) );
    }

    #[Grammar( '<result expression> ::= <value expression>' )]
    function ResultExpression(): Entry {
        return rule( fn() => ValueExpression() );
    }

    #[Grammar( '<else clause> ::= 
        ELSE 
        <result>' )]
    function ElseClause(): Entry {
        return rule( fn() => sequence(
            word( 'ELSE' ),
            Result(),
        ) );
    }

    #[Grammar( '<searched case> ::= 
        CASE 
        <searched when clause>... 
        [ <else clause> ] 
        END' )]
    function SearchedCase(): Entry {
        return rule( fn() => sequence(
            word( 'CASE' ),
            oneOrMore( SearchedWhenClause() ),
            zeroOrOne( ElseClause() ),
            word( 'END' ),
        ) );
    }

    #[Grammar( '<searched when clause> ::= 
        WHEN 
        <search condition> 
        THEN 
        <result>' )]
    function SearchedWhenClause(): Entry {
        return rule( fn() => sequence(
            word( 'WHEN' ),
            SearchCondition(),
            word( 'THEN' ),
            Result(),
        ) );
    }

    #[Grammar( '<cast specification> ::= 
        CAST 
        <left paren> 
        <cast operand> 
        AS 
        <cast target> 
        <right paren>' )]
    function CastSpecification(): Entry {
        return rule( fn() => sequence(
            word( 'CAST' ),
            LeftParen(),
            CastOperand(),
            word( 'AS' ),
            CastTarget(),
            RightParen(),
        ) );
    }

    #[Grammar( '<cast operand> ::=
            <value expression> 
        |   NULL' )]
    function CastOperand(): Entry {
        return rule( fn() => oneOf(
            ValueExpression(),
            word( 'NULL' ),
        ) );
    }

    #[Grammar( '<cast target> ::=
            <domain name> 
        |   <data type>' )]
    function CastTarget(): Entry {
        return rule( fn() => oneOf(
            DomainName(),
            DataType(),
        ) );
    }

    #[Grammar( '<domain name> ::= <qualified name>' )]
    function DomainName(): Entry {
        return rule( fn() => QualifiedName() );
    }

    #[Grammar( '<data type> ::=
            <character string type> 
            [ CHARACTER SET <character set specification> ] 
        |   <national character string type> 
        |   <bit string type> 
        |   <numeric type> 
        |   <datetime type> 
        |   <interval type>' )]
    function DataType(): Entry {
        return rule( fn() => oneOf(
            sequence(
                CharacterStringType(),
                optionalSequence(
                    word( 'CHARACTER SET' ),
                    CharacterSetSpecification(),
                )
            ),
            NationalCharacterStringType(),
            BitStringType(),
            NumericType(),
            DateTimeType(),
            IntervalType(),
        ) );
    }

    #[Grammar( '<character string type> ::= 
            CHARACTER 
            [ <paren enclosed length> ] 
        |   CHAR 
            [ <paren enclosed length> ] 
        |   CHARACTER VARYING 
            [ <paren enclosed length> ] 
        |   CHAR VARYING 
            [ <paren enclosed length> ] 
        |   VARCHAR 
            [ <paren enclosed length> ]' )]
    function CharacterStringType(): Entry {
        return rule( fn() => sequence(
            oneOf(
                word( 'VARCHAR' ),
                wordSequence( 'CHAR VARYING' ),
                word( 'CHAR' ),
                wordSequence( 'CHARACTER VARYING' ),
                word( 'CHARACTER' ),
            ),
            optional( ParenEnclosedLength() )
        ) );
    }

    #[Grammar( '<paren enclosed length> ::=
        <left paren> <length> <right paren>' )]
    function ParenEnclosedLength(): Entry {
        return rule( fn() => parenEnclosed( Length() ) );
    }

    #[Grammar( '<length> ::= <unsigned integer>' )]
    function Length(): Entry {
        return rule( fn() => UnsignedInteger() );
    }

    #[Grammar( '<national character string type> ::= 
            NATIONAL CHARACTER 
            [ <paren enclosed length>] 
        |   NATIONAL CHAR 
            [ <paren enclosed length>] 
        |   NCHAR 
            [ <paren enclosed length>] 
        |   NATIONAL CHARACTER VARYING 
            [ <paren enclosed length>] 
        |   NATIONAL CHAR VARYING 
            [ <paren enclosed length>] 
        |   NCHAR VARYING 
            [ <paren enclosed length>]' )]
    function NationalCharacterStringType(): Entry {
        return rule( fn() => sequence(
            oneOf(
                sequence(
                    word( 'NCHAR' ),
                    optionalWord( 'VARYING' ),
                ),
                sequence(
                    word( 'NATIONAL' ),
                    sequence(
                        oneOfWords( 'CHARACTER', 'CHAR' ),
                        optionalWord( 'VARYING' ),
                    ),
                ),
            ),
            optional( ParenEnclosedLength() ),
        ) );
    }

    #[Grammar( '<bit string type> ::= 
            BIT 
            [ <paren enclosed length>] 
        |   BIT VARYING 
            [ <paren enclosed length>]' )]
    function BitStringType(): Entry {
        return rule( fn() => sequence(
            oneOf(
                word( 'BIT' ),
                word( 'BIT VARYING' ),
            ),
            optional( ParenEnclosedLength() ),
        ) );
    }

    #[Grammar( '<numeric type> ::=
            <exact numeric type> 
        |   <approximate numeric type>' )]
    function NumericType(): Entry {
        return rule( fn() => oneOf(
            ExactNumericType(),
            ApproximateNumericType(),
        ) );
    }

    #[Grammar( '<exact numeric type> ::= 
            NUMERIC 
                [ <paren enclosed float> ] 
        |   DECIMAL 
                [ <paren enclosed float> ] 
        |   DEC 
                [ <paren enclosed float> ] 
        |   INTEGER 
        |   INT 
        |   SMALLINT' )]
    function ExactNumericType(): Entry {
        return rule( fn() => oneOf(
            word( 'INT' ),
            word( 'INTEGER' ),
            word( 'SMALLINT' ),
            sequence(
                oneOf(
                    word( 'NUMERIC' ),
                    word( 'DECIMAL' ),
                    word( 'DEC' ),
                ),
                optional( ParenEnclosedNumeric() ),
            ),
        ) );
    }

    #[Grammar( '<paren enclosed float> :=  
        <left paren> 
        <precision> 
        [ <comma> <scale> ] 
        <right paren>' )]
    function ParenEnclosedNumeric(): Entry {
        return rule( fn() => sequence(
            LeftParen(),
            Precision(),
            optionalSequence(
                Comma(),
                Scale()
            ),
            RightParen()
        ) );
    }

    #[Grammar( '<precision> ::=
            <unsigned integer>' )]
    function Precision(): Entry {
        return rule( fn() => UnsignedInteger() );
    }

    #[Grammar( '<scale> ::=
            <unsigned integer>' )]
    function Scale(): Entry {
        return rule( fn() => UnsignedInteger() );
    }

    #[Grammar( '<approximate numeric type> ::= 
        FLOAT [ <left paren> <precision> <right paren> ] 
        | REAL 
        | DOUBLE PRECISION' )]
    function ApproximateNumericType(): Entry {
        return rule( fn() => oneOf(
            sequence(
                word( 'FLOAT' ),
                optional( ParenEnclosedPrecision() )
            ),
            word( 'REAL' ),
            sequence(
                word( 'DOUBLE' ),
                word( 'PRECISION' ),
            ),
        ) );
    }

    #[Grammar( '<paren enclosed float> :=  
        <left paren> <precision> <right paren>' )]
    function ParenEnclosedPrecision(): Entry {
        return rule( fn() => parenEnclosed( Precision() ) );
    }

    #[Grammar( '<datetime type> ::= 
            DATE 
        |   TIME 
            [ <left paren> <time precision> <right paren> ] 
            [ <with time zone> ] 
        |   TIMESTAMP 
            [ <left paren> <timestamp precision> <right paren> ] 
            [ <with time zone> ]' )]
    function DateTimeType(): Entry {
        return rule( fn() => oneOf(
            word( 'DATE' ),
            sequence(
                word( 'TIME' ),
                optional( parenEnclosed( TimePrecision() ) ),
                optional( WithTimeZone() )
            ),
            sequence(
                word( 'TIMESTAMP' ),
                optional( parenEnclosed( TimestampPrecision() ) ),
                optional( WithTimeZone() )
            ),
        ) );
    }

    #[Grammar( '<time precision> ::= <time fractional seconds precision>' )]
    function TimePrecision(): Entry {
        return rule( fn() => TimeFractionalSecondsPrecision() );
    }

    #[Grammar( '<time fractional seconds precision> ::= <unsigned integer>' )]
    function TimeFractionalSecondsPrecision(): Entry {
        return rule( fn() => UnsignedInteger() );
    }

    #[Grammar( '<with time zone> ::=  WITH TIME ZONE' )]
    function WithTimeZone(): Entry {
        return rule( fn() => sequence(
            wordSequence( 'WITH TIME ZONE' ),
        ) );
    }

    #[Grammar( '<timestamp precision> ::= <time fractional seconds precision>' )]
    function TimestampPrecision(): Entry {
        return rule( fn() => TimeFractionalSecondsPrecision() );
    }

    #[Grammar( '<interval type> ::= 
        INTERVAL 
        <interval qualifier>' )]
    function IntervalType(): Entry {
        return rule( fn() => sequence(
            word( 'INTERVAL' ),
            IntervalQualifier(),
        ) );
    }

    #[Grammar( '<numeric value function> ::=
            <position expression> 
        |   <extract expression> 
        |    <length expression>' )]
    function NumericValueFunction(): Entry {
        return rule( fn() => oneOf(
            PositionExpression(),
            ExtractExpression(),
            LengthExpression(),
        ) );
    }

    #[Grammar( '<position expression> ::= 
        POSITION 
        <left paren> 
        <character value expression> 
        IN 
        <character value expression> 
        <right paren>' )]
    function PositionExpression(): Entry {
        return rule( fn() => sequence(
            word( 'POSITION' ),
            LeftParen(),
            CharacterValueExpression(),
            word( 'IN' ),
            CharacterValueExpression(),
            RightParen(),
        ) );
    }

    #[Grammar( '<character value expression> ::=
            <concatenation> 
        |   <character factor>' )]
    function CharacterValueExpression(): Entry {
        return rule( fn() => oneOf(
            Concatenation(),
            CharacterFactor(),
        ) );
    }

    #[Grammar( '<concatenation> ::=
            <character value expression> 
            <concatenation operator> 
            <character factor>' )]
    function Concatenation(): Entry {
        return rule( fn() => sequence(
            CharacterValueExpression(),
            ConcatenationOperator(),
            CharacterFactor(),
        ) );
    }

    #[Grammar( '<concatenation operator> ::= ||' )]
    function ConcatenationOperator(): Entry {
        return rule( fn() => Operator( '||' ) );
    }

    function Operator( string $sigil ): Entry {
        return rule( fn() => sigil( $sigil ) );
    }

    #[Grammar( '<character factor> ::=
            <character primary> 
            [ <collate clause> ]' )]
    function CharacterFactor(): Entry {
        return rule( fn() => sequence(
            CharacterPrimary(),
            optional( CollateClause() ),
        ) );
    }

    #[Grammar( '<character primary> ::=
            <value expression primary> 
        |   <string value function>' )]
    function CharacterPrimary(): Entry {
        return rule( fn() => oneOf(
            ValueExpressionPrimary(),
            StringValueFunction(),
        ) );
    }

    #[Grammar( '<string value function> ::=
            <character value function> 
        |   <bit value function>' )]
    function StringValueFunction(): Entry {
        return rule( fn() => oneOf(
            CharacterValueFunction(),
            BitValueFunction(),
        ) );
    }

    #[Grammar( '<character value function> ::=
            <character substring function> 
        |   <fold> 
        |   <form-of-use conversion> 
        |   <character translation> 
        |   <trim function>' )]
    function CharacterValueFunction(): Entry {
        return rule( fn() => oneOf(
            CharacterSubstringFunction(),
            Fold(),
            FormOfUseConversion(),
            CharacterTranslation(),
            TrimFunction(),
        ) );
    }

    #[Grammar( '<character substring function> ::= 
        SUBSTRING 
        <left paren> 
        <character value expression> 
        FROM 
        <start position> 
        [ 
            FOR 
            <string length> 
        ] 
        <right paren>' )]
    function CharacterSubstringFunction(): Entry {
        return rule( fn() => sequence(

            word( 'SUBSTRING' ),
            LeftParen(),
            CharacterValueExpression(),
            word( 'FROM' ),
            StartPosition(),
            optionalSequence(
                word( 'FOR' ),
                StringLength(),
            ),
            RightParen(),
        ) );
    }

    #[Grammar( '<start position> ::= <numeric value expression>' )]
    function StartPosition(): Entry {
        return rule( fn() => NumericValueExpression() );
    }

    #[Grammar( '<string length> ::= <numeric value expression>' )]
    function StringLength(): Entry {
        return rule( fn() => NumericValueExpression() );
    }

    #[Grammar( '<fold> ::= 
        { UPPER | LOWER } 
        <left paren> <character value expression> <right paren>' )]
    function Fold(): Entry {
        return rule( fn() => sequence(
            oneOfWords( 'UPPER', 'LOWER' ),
            parenEnclosed( CharacterValueExpression() ),
        ) );
    }

    #[Grammar( '<form-of-use conversion> ::= 
        CONVERT 
        <left paren> 
        <character value expression> 
        USING 
        <form-of-use conversion name> 
        <right paren>' )]
    function FormOfUseConversion(): Entry {
        return rule( fn() => sequence(
            word( 'CONVERT' ),
            LeftParen(),
            CharacterValueExpression(),
            word( 'USING' ),
            FormOfUseConversionName(),
            RightParen(),
        ) );
    }

    #[Grammar( '<form-of-use conversion name> ::= <qualified name>' )]
    function FormOfUseConversionName(): Entry {
        return rule( fn() => QualifiedName() );
    }

    #[Grammar( '<character translation> ::= 
        TRANSLATE 
        <left paren> 
        <character value expression> 
        USING 
        <translation name> 
        <right paren>' )]
    function CharacterTranslation(): Entry {
        return rule( fn() => sequence(

            word( 'TRANSLATE' ),
            LeftParen(),
            CharacterValueExpression(),
            word( 'USING' ),
            TranslationName(),
            RightParen(),
        ) );
    }

    #[Grammar( '<translation name> ::= <qualified name>' )]
    function TranslationName(): Entry {
        return rule( fn() => QualifiedName() );
    }

    #[Grammar( '<trim function> ::= 
        TRIM 
        <left paren> <trim operands> <right paren>' )]
    function TrimFunction(): Entry {
        return rule( fn() => sequence(
            word( 'TRIM' ),
            parenEnclosed( TrimOperands() ),
        ) );
    }

    #[Grammar( '<trim operands> ::= 
        [ 
            [ <trim specification> ] 
            [ <trim character> ] 
            FROM 
        ] 
        <trim source>' )]
    function TrimOperands(): Entry {
        return rule( fn() => sequence(
            optionalSequence(
                optional( TrimSpecification() ),
                optional( TrimCharacter() ),
                word( 'FROM' ),
            ),
            TrimSource(),
        ) );
    }

    #[Grammar( '<trim specification> ::= 
            LEADING 
        |   TRAILING 
        |   BOTH' )]
    function TrimSpecification(): Entry {
        return rule( fn() => oneOfWords(
            'LEADING',
            'TRAILING',
            'BOTH' ) );
    }

    #[Grammar( '<trim character> ::= <character value expression>' )]
    function TrimCharacter(): Entry {
        return rule( fn() => CharacterValueExpression() );
    }

    #[Grammar( '<trim source> ::= <character value expression>' )]
    function TrimSource(): Entry {
        return rule( fn() => CharacterValueExpression() );
    }

    #[Grammar( '<bit value function> ::= <bit substring function>' )]
    function BitValueFunction(): Entry {
        return rule( fn() => BitSubstringFunction() );
    }

    #[Grammar( '<bit substring function> ::= 
        SUBSTRING 
        <left paren> 
        <bit value expression> 
        FROM 
        <start position> 
        [ 
            FOR 
            <string length> 
        ] 
        <right paren>' )]
    function BitSubstringFunction(): Entry {
        return rule( fn() => sequence(

            word( 'SUBSTRING' ),
            LeftParen(),
            BitValueExpression(),
            word( 'FROM' ),
            StartPosition(),
            optionalSequence(
                word( 'FOR' ),
                StringLength(),
            ),
            RightParen(),
        ) );
    }

    #[Grammar( '<bit value expression> ::=
            <bit concatenation> 
        |   <bit factor>' )]
    function BitValueExpression(): Entry {
        return rule( fn() => oneOf(
            BitConcatenation(),
            BitFactor(),
        ) );
    }

    #[Grammar( '<bit concatenation> ::= 
        <bit value expression> 
        <concatenation operator> 
        <bit factor>' )]
    function BitConcatenation(): Entry {
        return rule( fn() => sequence(

            BitValueExpression(),
            ConcatenationOperator(),
            BitFactor(),
        ) );
    }

    #[Grammar( '<bit factor> ::= <bit primary>' )]
    function BitFactor(): Entry {
        return rule( fn() => BitPrimary() );
    }

    #[Grammar( '<bit primary> ::=
            <value expression primary> 
        |   <string value function>' )]
    function BitPrimary(): Entry {
        return rule( fn() => oneOf(
            ValueExpressionPrimary(),
            StringValueFunction(),
        ) );
    }

    #[Grammar( '<extract expression> ::= 
        EXTRACT 
        <left paren> 
        <extract field> 
        FROM 
        <extract source> 
        <right paren>' )]
    function ExtractExpression(): Entry {
        return rule( fn() => sequence(

            word( 'EXTRACT' ),
            LeftParen(),
            ExtractField(),
            word( 'FROM' ),
            ExtractSource(),
            RightParen(),
        ) );
    }

    #[Grammar( '<extract field> ::=
            <datetime field> 
        |   <time zone field>' )]
    function ExtractField(): Entry {
        return rule( fn() => oneOf(

            DateTimeField(),
            TimeZoneField(),
        ) );
    }

    #[Grammar( '<datetime field> ::=
            <non-second datetime field> 
        |   SECOND' )]
    function DateTimeField(): Entry {
        return rule( fn() => oneOf(

            NonSecondDateTimeField(),
            word( 'SECOND' ),
        ) );
    }

    #[Grammar( '<time zone field> ::= TIMEZONE_HOUR | TIMEZONE_MINUTE' )]
    function TimeZoneField(): Entry {
        return rule( fn() => oneOf(

            word( 'TIMEZONE_HOUR' ),
            word( 'TIMEZONE_MINUTE' ),
        ) );
    }

    #[Grammar( '<extract source> ::=
            <datetime value expression> 
        |   <interval value expression>' )]
    function ExtractSource(): Entry {
        return rule( fn() => oneOf(

            DateTimeValueExpression(),
            IntervalValueExpression(),
        ) );
    }

    #[Grammar( '<datetime value expression> ::=
            <datetime term> 
        |   <interval value expression> 
            <plus sign> 
            <datetime term> 
        |   <datetime value expression> 
            <plus sign> 
            <interval term> 
        |   <datetime value expression> 
            <minus sign> 
            <interval term>' )]
    function DateTimeValueExpression(): Entry {
        return rule( fn() => oneOf(
            DateTimeTerm(),
            sequence(
                IntervalValueExpression(),
                PlusSign(),
                DateTimeTerm(),
            ),
            sequence(
                DateTimeValueExpression(),
                PlusSign(),
                IntervalTerm(),
            ),
            sequence(
                DateTimeValueExpression(),
                MinusSign(),
                IntervalTerm(),
            ),
        ) );
    }

    #[Grammar( '<datetime term> ::= <datetime factor>' )]
    function DateTimeTerm(): Entry {
        return rule( fn() => DateTimeFactor() );
    }

    #[Grammar( '<datetime factor> ::=
        <datetime primary> 
        [ <time zone> ]' )]
    function DateTimeFactor(): Entry {
        return rule( fn() => sequence(
            DateTimePrimary(),
            optional( TimeZone() ),
        ) );
    }

    #[Grammar( '<datetime primary> ::=
            <value expression primary> 
        |   <datetime value function>' )]
    function DateTimePrimary(): Entry {
        return rule( fn() => oneOf(
            ValueExpressionPrimary(),
            DateTimeValueFunction(),
        ) );
    }

    #[Grammar( '<datetime value function> ::=
            <current date value function> 
        |   <current time value function> 
        |   <current timestamp value function>' )]
    function DateTimeValueFunction(): Entry {
        return rule( fn() => oneOf(
            CurrentDateValueFunction(),
            CurrentTimeValueFunction(),
            CurrentTimestampValueFunction(),
        ) );
    }

    #[Grammar( '<current date value function> ::= CURRENT_DATE' )]
    function CurrentDateValueFunction(): Entry {
        return rule( fn() => word( 'CURRENT_DATE' ) );
    }

    #[Grammar( '<current time value function> ::= 
            CURRENT_TIME 
            [ <left paren> <time precision> <right paren> ]' )]
    function CurrentTimeValueFunction(): Entry {
        return rule( fn() => sequence(
            word( 'CURRENT_TIME' ),
            optional( parenEnclosed( TimePrecision() ) ),
        ) );
    }

    #[Grammar( '<current timestamp value function> ::= 
        CURRENT_TIMESTAMP 
        [ <left paren> <timestamp precision> <right paren> ]' )]
    function CurrentTimestampValueFunction(): Entry {
        return rule( fn() => sequence(
            word( 'CURRENT_TIMESTAMP' ),
            optional( parenEnclosed( TimestampPrecision() ) ),
        ) );
    }

    #[Grammar( '<time zone> ::= 
        AT 
        <time zone specifier>' )]
    function TimeZone(): Entry {
        return rule( fn() => sequence(
            word( 'AT' ),
            TimeZoneSpecifier(),
        ) );
    }

    #[Grammar( '<time zone specifier> ::= 
            LOCAL 
        |   TIME ZONE 
            <interval value expression>' )]
    function TimeZoneSpecifier(): Entry {
        return rule( fn() => oneOf(
            word( 'LOCAL' ),
            sequence(
                wordSequence( 'TIME ZONE' ),
                IntervalValueExpression(),
            ),
        ) );
    }

    #[Grammar( '<interval value expression> ::=
            <interval term> 
        |   <interval value expression 1> 
            <plus sign> 
            <interval term 1> 
        |   <interval value expression 1> 
            <minus sign> 
            <interval term 1> 
        |   <left paren> 
            <datetime value expression> 
            <minus sign> 
            <datetime term> 
            <right paren> 
            <interval qualifier>' )]
    function IntervalValueExpression(): Entry {
        return rule( fn() => oneOf(
            IntervalTerm(),
            sequence(
                IntervalValueExpression1(),
                PlusSign(),
                IntervalTerm1(),
            ),
            sequence(
                IntervalValueExpression1(),
                MinusSign(),
                IntervalTerm1(),
            ),
            sequence(
                LeftParen(),
                DateTimeValueExpression(),
                MinusSign(),
                DateTimeTerm(),
                RightParen(),
                IntervalQualifier(),
            ),
        ) );
    }

    #[Grammar( '<interval term> ::=
            <interval factor> 
        |   <interval term 2> 
            <asterisk> 
            <factor> 
        |   <interval term 2> 
            <solidus> 
            <factor> 
        |   <term> 
            <asterisk> 
            <interval factor>' )]
    function IntervalTerm(): Entry {
        return rule( fn() => oneOf(
            IntervalFactor(),
            sequence(
                IntervalTerm2(),
                Asterisk(),
                Factor(),
            ),
            sequence(
                IntervalTerm2(),
                Solidus(),
                Factor(),
            ),
            sequence(
                Term(),
                Asterisk(),
                IntervalFactor(),
            ),
        ) );
    }

    #[Grammar( '<interval factor> ::= 
        [ <sign> ] 
        <interval primary>' )]
    function IntervalFactor(): Entry {
        return rule( fn() => sequence(
            optional( Sign() ),
            IntervalPrimary(),
        ) );
    }

    #[Grammar( '<interval primary> ::=
            <value expression primary> 
            [ <interval qualifier> ]' )]
    function IntervalPrimary(): Entry {
        return rule( fn() => oneOf(
            ValueExpressionPrimary(),
            optional( IntervalQualifier() ),
        ) );
    }

    #[Grammar( '<interval term 2> ::= <interval term>' )]
    function IntervalTerm2(): Entry {
        return rule( fn() => IntervalTerm() );
    }

    #[Grammar( '<solidus> ::= /' )]
    function Solidus(): Entry {
        return rule( fn() => char( '/' ) );
    }

    #[Grammar( '<interval value expression 1> ::= <interval value expression>' )]
    function IntervalValueExpression1(): Entry {
        return rule( fn() => IntervalValueExpression() );
    }

    #[Grammar( '<interval term 1> ::= <interval term>' )]
    function IntervalTerm1(): Entry {
        return rule( fn() => IntervalTerm() );
    }

    #[Grammar( '<length expression> ::=
            <char length expression> 
        |   <octet length expression> 
        |   <bit length expression>' )]
    function LengthExpression(): Entry {
        return rule( fn() => oneOf(
            CharLengthExpression(),
            OctalLengthExpression(),
            BitLengthExpression(),
        ) );
    }

    #[Grammar( '<char length expression> ::= 
        { CHAR_LENGTH | CHARACTER_LENGTH } 
        <left paren>  <string value expression> <right paren>' )]
    function CharLengthExpression(): Entry {
        return rule( fn() => sequence(
            oneOfWords( 'CHAR_LENGTH', 'CHARACTER_LENGTH' ),
            parenEnclosed( StringValueExpression() ),
        ) );
    }

    #[Grammar( '<string value expression> ::=
            <character value expression> 
        |   <bit value expression>' )]
    function StringValueExpression(): Entry {
        return rule( fn() => oneOf(
            CharacterValueExpression(),
            BitValueExpression(),
        ) );
    }

    #[Grammar( '<octet length expression> ::= 
        OCTET_LENGTH 
        <left paren> <string value expression> <right paren>' )]
    function OctalLengthExpression(): Entry {
        return rule( fn() => sequence(
            word( 'OCTET_LENGTH' ),
            parenEnclosed( StringValueExpression() ),
        ) );
    }

    #[Grammar( '<bit length expression> ::= 
        BIT_LENGTH 
        <left paren> <string value expression> <right paren>' )]
    function BitLengthExpression(): Entry {
        return rule( fn() => sequence(
            word( 'BIT_LENGTH' ),
            parenEnclosed( StringValueExpression() ),
        ) );
    }

    #[Grammar( '<null specification> ::= NULL' )]
    function NullSpecification(): Entry {
        return rule( fn() => word( 'NULL' ) );
    }

    #[Grammar( '<default specification> ::= DEFAULT' )]
    function DefaultSpecification(): Entry {
        return rule( fn() => word( 'DEFAULT' ) );
    }

    #[Grammar( '<row value constructor list> ::=
        <row value constructor element> 
        [ { <comma> <row value constructor element> } ... ]' )]
    function RowValueConstructorList(): Entry {
        return rule( fn() => commaList( RowValueConstructorElement() ) );
    }

    #[Grammar( '<row subquery> ::= <subquery>' )]
    function RowSubquery(): Entry {
        return rule( fn() => Subquery() );
    }

    #[Grammar( '<comp op> ::= 
            <equals operator> 
        |   <not equals operator> 
        |   <less than operator> 
        |   <greater than operator> 
        |   <less than or equals operator> 
        |   <greater than or equals operator>' )]
    function CompOp(): Entry {
        return rule( fn() => oneOf(
            EqualsOperator(),
            NotEqualsOperator(),
            LessThanOperator(),
            GreaterThanOperator(),
            LessThanOrEqualsOperator(),
            GreaterThanOrEqualsOperator(),
        ) );
    }

    #[Grammar( '<equals operator> ::= =' )]
    function EqualsOperator(): Entry {
        return rule( fn() => Operator( '=' ) );
    }

    #[Grammar( '<not equals operator> ::= <>' )]
    function NotEqualsOperator(): Entry {
        return rule( fn() => Operator( '<>' ) );
    }

    #[Grammar( '<less than operator> ::= <' )]
    function LessThanOperator(): Entry {
        return rule( fn() => Operator( '<' ) );
    }

    #[Grammar( '<greater than operator> ::= >' )]
    function GreaterThanOperator(): Entry {
        return rule( fn() => Operator( '>' ) );
    }

    #[Grammar( '<less than or equals operator> ::= <=' )]
    function LessThanOrEqualsOperator(): Entry {
        return rule( fn() => Operator( '<=' ) );
    }

    #[Grammar( '<greater than or equals operator> ::= >=' )]
    function GreaterThanOrEqualsOperator(): Entry {
        return rule( fn() => Operator( '>=' ) );
    }

    #[Grammar( '<between predicate> ::=
        <row value constructor> 
        [ NOT ] 
        BETWEEN 
        <row value constructor> 
        AND 
        <row value constructor>' )]
    function BetweenPredicate(): Entry {
        return rule( fn() => sequence(
            RowValueConstructor(),
            optionalWord( 'NOT' ),
            word( 'BETWEEN' ),
            RowValueConstructor(),
            word( 'AND' ),
            RowValueConstructor(),
        ) );
    }

    #[Grammar( '<in predicate> ::=
            <row value constructor> 
            [ NOT ] 
            IN 
            <in predicate value>' )]
    function InPredicate(): Entry {
        return rule( fn() => sequence(
            RowValueConstructor(),
            optionalWord( 'NOT' ),
            word( 'IN' ),
            InPredicateValue(),
        ) );
    }

    #[Grammar( '<in predicate value> ::=
                <table subquery> 
            |   <left paren> <in value list> <right paren>' )]
    function InPredicateValue(): Entry {
        return rule( fn() => oneOf(
            optional( parenEnclosed( InValueList() ) ),
            TableSubquery(),
        ) );
    }

    #[Grammar( '<in value list> ::=
        <value expression> { <comma> <value expression> } ...' )]
    function InValueList(): Entry {
        return rule( fn() => commaList( ValueExpression() ) );
    }

    #[Grammar( '<like predicate> ::=
            <match value> 
            [ NOT ] 
            LIKE 
            <pattern> 
            [   ESCAPE 
                <escape character> ]' )]
    function LikePredicate(): Entry {
        return rule( fn() => sequence(
            MatchValue(),
            optionalWord( 'NOT' ),
            word( 'LIKE' ),
            Pattern(),
            optionalSequence(
                word( 'ESCAPE' ),
                EscapeCharacter(),
            ),
        ) );
    }

    #[Grammar( '<match value> ::= <character value expression>' )]
    function MatchValue(): Entry {
        return rule( fn() => CharacterValueExpression() );
    }

    #[Grammar( '<pattern> ::= <character value expression>' )]
    function Pattern(): Entry {
        return rule( fn() => CharacterValueExpression() );
    }

    #[Grammar( '<escape character> ::= <character value expression>' )]
    function EscapeCharacter(): Entry {
        return rule( fn() => CharacterValueExpression() );
    }

    #[Grammar( '<null predicate> ::= 
        <row value constructor> 
        IS 
        [ NOT ] 
        NULL' )]
    function NullPredicate(): Entry {
        return rule( fn() => sequence(
            RowValueConstructor(),
            word( 'IS' ),
            optionalWord( 'NOT' ),
            word( 'NULL' ),
        ) );
    }

    #[Grammar( '<quantified comparison predicate> ::= 
            <row value constructor> 
            <comp op> 
            <quantifier> 
            <table subquery>' )]
    function QuantifiedComparisonPredicate(): Entry {
        return rule( fn() => sequence(
            RowValueConstructor(),
            CompOp(),
            Quantifier(),
            TableSubquery(),
        ) );
    }

    #[Grammar( '<quantifier> ::=
            <all> 
        |   <some>' )]
    function Quantifier(): Entry {
        return rule( fn() => oneOf(
            All(),
            Some(),
        ) );
    }

    #[Grammar( '<some> ::= SOME | ANY' )]
    function Some(): Entry {
        return rule( fn() => oneOfWords( 'SOME', 'ANY' ) );
    }

    #[Grammar( '<exists predicate> ::= 
        EXISTS 
        <table subquery>' )]
    function ExistsPredicate(): Entry {
        return rule( fn() => sequence(
            word( 'EXISTS' ),
            TableSubquery(),
        ) );
    }

    #[Grammar( '<match predicate> ::= 
        <row value constructor> 
        MATCH 
        [ UNIQUE ] 
        [ PARTIAL | FULL ] 
        <table subquery>' )]
    function MatchPredicate(): Entry {
        return rule( fn() => sequence(
            RowValueConstructor(),
            word( 'MATCH' ),
            optionalWord( 'UNIQUE' ),
            optional( oneOfWords( 'PARTIAL', 'FULL' ) ),
            TableSubquery(),
        ) );
    }

    #[Grammar( '<overlaps predicate> ::= 
        <row value constructor 1> 
        OVERLAPS 
        <row value constructor 2>' )]
    function OverlapsPredicate(): Entry {
        return rule( fn() => sequence(
            RowValueConstructor1(),
            word( 'OVERLAPS' ),
            RowValueConstructor2(),
        ) );
    }

    #[Grammar( '<row value constructor 1> ::= <row value constructor>' )]
    function RowValueConstructor1(): Entry {
        return rule( fn() => RowValueConstructor() );
    }

    #[Grammar( '<row value constructor 2> ::= <row value constructor>' )]
    function RowValueConstructor2(): Entry {
        return rule( fn() => RowValueConstructor() );
    }

    #[Grammar( '<truth value> ::= TRUE | FALSE | UNKNOWN' )]
    function TruthValue(): Entry {
        return rule( fn() => oneOfWords( 'TRUE', 'FALSE', 'UNKNOWN' ) );
    }

    #[Grammar( '<unique predicate> ::= 
        UNIQUE 
        <table subquery>' )]
    function UniquePredicate(): Entry {
        return rule( fn() => sequence(
            word( 'UNIQUE' ),
            TableSubquery(),
        ) );
    }

    #[Grammar( '<time zone interval> ::=
            <sign> 
            <hours value> 
            <colon> 
            <minutes value>' )]
    function TimeZoneValue(): Entry {
        return rule( fn() => sequence(
            Sign(),
            HoursValue(),
            Colon(),
            MinutesValue(),
        ) );
    }

    #[Grammar( '<SQL terminal character> ::=
            <SQL language character> 
        |   <SQL embedded language character>' )]
    function SQLTerminalCharacter(): Entry {
        return rule( fn() => oneOf( oneOf(
            SQLLanguageCharacter(),
            SQLEmbeddedLanguageCharacter(),
            digit(),
        ) ) );
    }

    #[Grammar( '<SQL language character> ::=
            <simple Latin letter> 
        |   <digit> 
        |   <SQL special character>' )]
    function SQLLanguageCharacter(): Entry {
        return rule( fn() => oneOf(
            SimpleLatinLetter(),
            digit(),
            SqlSpecialCharacter(),
        ) );
    }

    #[Grammar( '<SQL special character> ::= 
            <space> 
        |   <double quote> 
        |   <percent> 
        |   <ampersand> 
        |   <quote> 
        |   <left paren> 
        |   <right paren> 
        |   <asterisk> 
        |   <plus sign> 
        |   <comma> 
        |   <minus sign> 
        |   <period> 
        |   <solidus> 
        |   <colon> 
        |   <semicolon> 
        |   <less than operator> 
        |   <greater than operator> 
        |   <equals operator> 
        |   <question mark> 
        |   <underscore> 
        |   <vertical bar>' )]
    function SqlSpecialCharacter(): Entry {
        return rule( fn() => oneOf(
            Space(),
            DoubleQuote(),
            Percent(),
            Ampersand(),
            Quote(),
            LeftParen(),
            RightParen(),
            Asterisk(),
            PlusSign(),
            Comma(),
            MinusSign(),
            Period(),
            Solidus(),
            Colon(),
            SemiColon(),
            LessThanOperator(),
            GreaterThanOperator(),
            EqualsOperator(),
            QuestionMark(),
            Underscore(),
            VerticalBar(),
        ) );
    }

    #[Grammar( '<percent> ::= %' )]
    function Percent(): Entry {
        return rule( fn() => char( '%' ) );
    }

    #[Grammar( '<ampersand> ::= &' )]
    function Ampersand(): Entry {
        return rule( fn() => char( '&' ) );
    }

    #[Grammar( '<semicolon> ::= ;' )]
    function SemiColon(): Entry {
        return rule( fn() => char( ';' ) );
    }

    #[Grammar( '<vertical bar> ::= |' )]
    function VerticalBar(): Entry {
        return rule( fn() => char( '|' ) );
    }

    #[Grammar( '<SQL embedded language character> ::=
            <left bracket> 
        |   <right bracket>' )]
    function SQLEmbeddedLanguageCharacter(): Entry {
        return rule( fn() => oneOf(
            LeftBracket(),
            RightBracket(),
        ) );
    }

    #[Grammar( '<left bracket> ::= [' )]
    function LeftBracket(): Entry {
        return rule( fn() => char( '[' ) );
    }

    #[Grammar( '<right bracket> ::= ]' )]
    function RightBracket(): Entry {
        return rule( fn() => char( ']' ) );
    }

    #[Grammar( '<token> ::=
            <nondelimiter token> 
        |   <delimiter token>' )]
    function Token(): Entry {
        return rule( fn() => oneOf(
            NonDelimiterToken(),
            DelimiterToken(),
        ) );
    }

    #[Grammar( '<nondelimiter token> ::= 
            <regular identifier> 
        |   <key word> 
        |   <unsigned numeric literal> 
        |   <national character string literal> 
        |   <bit string literal> 
        |   <hex string literal>' )]
    function NonDelimiterToken(): Entry {
        return rule( fn() => oneOf(
            RegularIdentifier(),
            KeyWord(),
            UnsignedNumericLiteral(),
            NationalCharacterStringLiteral(),
            BitStringLiteral(),
            HexStringLiteral(),
        ) );
    }

    #[Grammar( '<key word> ::=
            <reserved word> 
        |   <non-reserved word>' )]
    function KeyWord(): Entry {
        return rule( fn() => oneOf(
            ReservedWord(),
            NonReservedWord(),
        ) );
    }

    const RESERVED_WORDS = <<<WORDS
         ABSOLUTE|ACTION|ADD|ALL|ALLOCATE|ALTER|AND|ANY|ARE
        |AS|ASC|ASSERTION|AT|AUTHORIZATION|AVG
        |BEGIN|BETWEEN|BIT|BIT_LENGTH|BOTH|BY
        |CASCADE|CASCADED|CASE|CAST|CATALOG|CHAR|CHARACTER|CHARACTER_LENGTH
        |CHAR_LENGTH|CHECK|CLOSE|COALESCE|COLLATE|COLLATION|COLUMN|COMMIT
        |CONNECT|CONNECTION|CONSTRAINT|CONSTRAINTS|CONTINUE|CONVERT|CORRESPONDING
        |CREATE|CROSS|CURRENT|CURRENT_DATE|CURRENT_TIME|CURRENT_TIMESTAMP|CURRENT_USER|CURSOR
        |DATE|DAY|DEALLOCATE|DEC|DECIMAL|DECLARE|DEFAULT
        |DEFERRABLE|DEFERRED|DELETE|DESC|DESCRIBE|DESCRIPTOR|DIAGNOSTICS
        |DISCONNECT|DISTINCT|DOMAIN|DOUBLE|DROP
        |ELSE|END|END-EXEC|ESCAPE|EXCEPT|EXCEPTION|EXEC|EXECUTE|EXISTS|EXTERNAL|EXTRACT
        |FALSE|FETCH|FIRST|FLOAT|FOR|FOREIGN|FOUND|FROM|FULL
        |GET|GLOBAL|GO|GOTO|GRANT|GROUP
        |HAVING|HOUR
        |IDENTITY|IMMEDIATE|IN|INDICATOR|INITIALLY|INNER|INPUT|INSENSITIVE
        |INSERT|INT|INTEGER|INTERSECT|INTERVAL|INTO|IS|ISOLATION
        |JOIN
        |KEY
        |LANGUAGE|LAST|LEADING|LEFT|LEVEL|LIKE|LOCAL|LOWER
        |MATCH|MAX|MIN|MINUTE|MODULE|MONTH
        |NAMES|NATIONAL|NATURAL|NCHAR|NEXT|NO|NOT|NULL|NULLIF|NUMERIC
        |OCTET_LENGTH|OF|ON|ONLY|OPEN|OPTION|OR|ORDER|OUTER|OUTPUT|OVERLAPS
        |PAD|PARTIAL|POSITION|PRECISION|PREPARE|PRESERVE|PRIMARY|PRIOR|PRIVILEGES|PROCEDURE|PUBLIC
        |READ|REAL|REFERENCES|RELATIVE|RESTRICT|REVOKE|RIGHT|ROLLBACK|ROWS
        |SCHEMA|SCROLL|SECOND|SECTION|SELECT|SESSION|SESSION_USER|SET
        |SIZE|SMALLINT|SOME|SPACE|SQL|SQLCODE|SQLERROR|SQLSTATE|SUBSTRING|SUM|SYSTEM_USER
        |TABLE|TEMPORARY|THEN|TIME|TIMESTAMP|TIMEZONE_HOUR|TIMEZONE_MINUTE
        |TO|TRAILING|TRANSACTION|TRANSLATE|TRANSLATION|TRIM|TRUE
        |UNION|UNIQUE|UNKNOWN|UPDATE|UPPER|USAGE|USER|USING
        |VALUE|VALUES|VARCHAR|VARYING|VIEW
        |WHEN|WHENEVER|WHERE|WITH|WORK|WRITE
        |YEAR
        |ZONE
    WORDS;
    #[Grammar( '<reserved word> ::= ' . RESERVED_WORDS )]
    function ReservedWord(): Entry {
        return rule( fn() => oneOfWords( ...splitWords( RESERVED_WORDS ) ) );
    }

    const NON_RESERVED_WORDS = <<<WORDS
         ADA
        |C|CATALOG_NAME|CHARACTER_SET_CATALOG|CHARACTER_SET_NAME|CHARACTER_SET_SCHEMA
        |CLASS_ORIGIN|COBOL|COLLATION_CATALOG|COLLATION_NAME|COLLATION_SCHEMA
        |COLUMN_NAME|COMMAND_FUNCTION|COMMITTED|CONDITION_NUMBER|CONNECTION_NAME
        |CONSTRAINT_CATALOG|CONSTRAINT_NAME|CONSTRAINT_SCHEMA|CURSOR_NAME
        |DATA|DATETIME_INTERVAL_CODE|DATETIME_INTERVAL_PRECISION|DYNAMIC_FUNCTION
        |FORTRAN
        |LENGTH
        |MESSAGE_LENGTH|MESSAGE_OCTET_LENGTH|MESSAGE_TEXT|MORE|MUMPS
        |NAME|NULLABLE|NUMBER
        |PASCAL|PLI
        |REPEATABLE|RETURNED_LENGTH|RETURNED_OCTET_LENGTH|RETURNED_SQLSTATE|ROW_COUNT
        |SCALE|SCHEMA_NAME|SERIALIZABLE|SERVER_NAME|SUBCLASS_ORIGIN
        |TABLE_NAME|TYPE
        |UNCOMMITTED|UNNAMED
    WORDS;
    #[Grammar( '<non-reserved word> ::= ' . NON_RESERVED_WORDS )]
    function NonReservedWord(): Entry {
        return rule( fn() => oneOfWords( ...splitWords( NON_RESERVED_WORDS ) ) );
    }

    #[Grammar( '<delimiter token> ::= 
            <character string literal> 
        |   <date string> 
        |   <time string> 
        |   <timestamp string> 
        |   <delimited identifier> 
        |   <SQL special character> 
        |   <not equals operator> 
        |   <greater than or equals operator> 
        |   <less than or equals operator> 
        |   <concatenation operator> 
        |   <double period> 
        |   <left bracket> 
        |   <right bracket>' )]
    function DelimiterToken(): Entry {
        return rule( fn() => oneOf(
            CharacterStringLiteral(),
            DateString(),
            TimeString(),
            TimestampString(),
            DelimitedIdentifier(),
            SqlSpecialCharacter(),
            NotEqualsOperator(),
            GreaterThanOrEqualsOperator(),
            LessThanOrEqualsOperator(),
            ConcatenationOperator(),
            DoublePeriod(),
            LeftBracket(),
            RightBracket(),
        ) );
    }

    #[Grammar( '<double period> ::= ..' )]
    function DoublePeriod(): Entry {
        return rule( fn() => Operator( '..' ) );
    }

    #[Grammar( '<module> ::=
        <module name clause> 
        <language clause> 
        <module authorization clause>
        [ <temporary table declaration>... ]
        <module contents>...' )]
    function Module(): Entry {
        return rule( fn() => sequence(
            ModuleNameClause(),
            LanguageClause(),
            ModuleAuthorizationClause(),
            zeroOrMore( TemporaryTableDeclaration() ),
            oneOrMore( ModuleContents() ),
        ) );
    }

    #[Grammar( '<module name clause> ::=
          MODULE 
          [ <module name> ] 
          [ <module character set specification> ]' )]
    function ModuleNameClause(): Entry {
        return rule( fn() => sequence(
            wordSequence( 'MODULE' ),
            optional( ModuleName() ),
            optional( ModuleCharacterSetSpecification() ),
        ) );
    }

    #[Grammar( '<module name> ::= <identifier>' )]
    function ModuleName(): Entry {
        return rule( fn() => Identifier() );
    }

    #[Grammar( '<module character set specification> ::= 
        NAMES ARE 
        <character set specification>' )]
    function ModuleCharacterSetSpecification(): Entry {
        return rule( fn() => sequence(
            wordSequence( 'NAMES ARE' ),
            CharacterSetSpecification()
        ) );
    }

    #[Grammar( '<language clause> ::= 
        LANGUAGE 
        <language name>' )]
    function LanguageClause(): Entry {
        return rule( fn() => sequence(
            word( 'LANGUAGE' ),
            LanguageName(),
        ) );
    }

    #[Grammar( '<language name> ::= ADA | C | COBOL | FORTRAN | MUMPS | PASCAL | PLI' )]
    function LanguageName(): Entry {
        return rule( fn() => oneOfWords(
            'ADA',
            'C',
            'COBOL',
            'FORTRAN',
            'MUMPS',
            'PASCAL',
            'PLI' ) );
    }

    #[Grammar( '<module authorization clause> ::=
            SCHEMA 
            <schema name>
        |   AUTHORIZATION 
            <module authorization identifier>
        |   SCHEMA 
            <schema name> 
            AUTHORIZATION 
            <module authorization identifier>' )]
    function ModuleAuthorizationClause(): Entry {
        return rule( fn() => oneOf(
            sequence(
                word( 'SCHEMA' ),
                SchemaName(),
                optionalSequence(
                    word( 'AUTHORIZATION' ),
                    ModuleAuthorizationIdentifier(),
                ),
            ),
            sequence(
                word( 'AUTHORIZATION' ),
                ModuleAuthorizationIdentifier(),
            ),
        ) );
    }

    #[Grammar( '<module authorization identifier> ::= <authorization identifier>' )]
    function ModuleAuthorizationIdentifier(): Entry {
        return rule( fn() => AuthorizationIdentifier() );
    }

    #[Grammar( '<authorization identifier> ::= <identifier>' )]
    function AuthorizationIdentifier(): Entry {
        return rule( fn() => Identifier() );
    }

    #[Grammar( '<temporary table declaration> ::=
        DECLARE LOCAL TEMPORARY TABLE 
        <qualified local table name> 
        <table element list> 
        [   ON COMMIT 
            { PRESERVE | DELETE } 
            ROWS ]' )]
    function TemporaryTableDeclaration(): Entry {
        return rule( fn() => sequence(
            wordSequence( 'DECLARE LOCAL TEMPORARY TABLE' ),
            QualifiedLocalTableName(),
            TableElementList(),
            wordSequence( 'ON COMMIT' ),
            oneOfWords( 'PRESERVE', 'DELETE' ),
            word( 'ROWS' ),
        ) );
    }

    #[Grammar( '<table element list> ::= 
        <left paren> <table element> [ { <comma> <table element> }... ] <right paren>' )]
    function TableElementList(): Entry {
        return rule( fn() => parenEnclosed( commaList( TableElement() ) ) );
    }

    #[Grammar( '<table element> ::=
            <column definition> 
        |   <table constraint definition>' )]
    function TableElement(): Entry {
        return rule( fn() => oneOf(
            ColumnDefinition(),
            TableConstraintDefinition(),
        ) );
    }

    #[Grammar( '<column definition> ::=
        <column name> 
        { 
                <data type> 
            |   <domain name> 
        } 
        [ <default clause> ] 
        [ <column constraint definition>... ] 
        [ <collate clause> ]' )]
    function ColumnDefinition(): Entry {
        return rule( fn() => sequence(
            ColumnName(),
            oneOf(
                DataType(),
                DomainName(),
            ),
            zeroOrOne( DefaultClause() ),
            zeroOrMore( ColumnConstraintDefinition() ),
            zeroOrOne( CollateClause() ),
        ) );
    }

    #[Grammar( '<default clause> ::= 
        DEFAULT 
        <default option>' )]
    function DefaultClause(): Entry {
        return rule( fn() => sequence(
            word( 'DEFAULT' ),
            DefaultOption(),
        ) );
    }

    #[Grammar( '<default option> ::= 
            <literal> 
        |   <datetime value function> 
        |   USER 
        |   CURRENT_USER 
        |   SESSION_USER 
        |   SYSTEM_USER 
        |   NULL' )]
    function DefaultOption(): Entry {
        return rule( fn() => oneOf(
            Literal(),
            DateTimeValueFunction(),
            oneOfWords(
                'USER',
                'CURRENT_USER',
                'SESSION_USER',
                'SYSTEM_USER',
                'NULL' ),
        ) );
    }

    #[Grammar( '<literal> ::=
            <signed numeric literal> 
        |   <general literal>' )]
    function Literal(): Entry {
        return rule( fn() => oneOf(
            SignedNumericLiteral(),
            GeneralLiteral(),
        ) );
    }

    #[Grammar( '<signed numeric literal> ::= 
        [ <sign> ] 
        <unsigned numeric literal>' )]
    function SignedNumericLiteral(): Entry {
        return rule( fn() => oneOf(
            optional( Sign() ),
            UnsignedNumericLiteral(),
        ) );
    }

    #[Grammar( '<column constraint definition> ::= 
        [ <constraint name definition> ] 
        <column constraint> 
        [ <constraint attributes> ]' )]
    function ColumnConstraintDefinition(): Entry {
        return rule( fn() => sequence(
            optional( ConstraintNameDefinition() ),
            ColumnConstraint(),
            optional( ConstraintAttributes() ),
        ) );
    }

    #[Grammar( '<constraint name definition> ::= 
        CONSTRAINT 
        <constraint name>' )]
    function ConstraintNameDefinition(): Entry {
        return rule( fn() => sequence(
            word( 'CONSTRAINT' ),
            ConstraintName(),
        ) );
    }

    #[Grammar( '<constraint name> ::= <qualified name>' )]
    function ConstraintName(): Entry {
        return rule( fn() => QualifiedName() );
    }

    #[Grammar( '<table constraint definition> ::= 
        [ <constraint name definition> ] 
        <table constraint> 
        [ <constraint check time> ]' )]
    function TableConstraintDefinition(): Entry {
        return rule( fn() => sequence(
            optional( ConstraintNameDefinition() ),
            TableConstraint(),
            optional( ConstraintCheckTime() ),
        ) );
    }

    #[Grammar( '<table constraint> ::=
            <unique constraint definition>
        |   <referential constraint definition>
        |   <check constraint definition>' )]
    function TableConstraint(): Entry {
        return rule( fn() => oneOf(
            UniqueConstraintDefinition(),
            ReferentialConstraintDefinition(),
            CheckConstraintDefinition(),
        ) );
    }

    #[Grammar( '<unique constraint definition> ::= 
            <unique specification> 
            <left paren> <unique column list> <right paren>' )]
    function UniqueConstraintDefinition(): Entry {
        return rule( fn() => sequence(
            UniqueSpecification(),
            parenEnclosed( UniqueColumnList() ),
        ) );
    }

    #[Grammar( '<unique column list> ::= <column name list>' )]
    function UniqueColumnList(): Entry {
        return rule( fn() => ColumnNameList() );
    }

    #[Grammar( '<referential constraint definition> ::=
              FOREIGN KEY 
              <left paren> <referencing columns> <right paren> 
              <references specification>' )]
    function ReferentialConstraintDefinition(): Entry {
        return rule( fn() => sequence(
            wordSequence( 'FOREIGN KEY' ),
            parenEnclosed( ReferencingColumns() ),
            ReferencesSpecification(),
        ) );
    }

    #[Grammar( '<referencing columns> ::= <reference column list>' )]
    function ReferencingColumns(): Entry {
        return rule( fn() => ReferenceColumnList() );
    }

    #[Grammar( '<constraint check time> ::= INITIALLY DEFERRED | INITIALLY IMMEDIATE' )]
    function ConstraintCheckTime(): Entry {
        return rule( fn() => oneOf(
            wordSequence( 'INITIALLY DEFERRED' ),
            wordSequence( 'INITIALLY IMMEDIATE' ),
        ) );
    }

    #[Grammar( '<module contents> ::=
            <declare cursor>
        |   <dynamic declare cursor>
        |   <procedure>' )]
    function ModuleContents(): Entry {
        return rule( fn() => oneOf(
            DeclareCursor(),
            DynamicDeclareCursor(),
            Procedure(),
        ) );
    }

    #[Grammar( '<declare cursor> ::=
        DECLARE 
        <cursor name> 
        [ INSENSITIVE ] 
        [ SCROLL ] 
        CURSOR FOR 
        <cursor specification>' )]
    function DeclareCursor(): Entry {
        return rule( fn() => sequence(
            word( 'DECLARE' ),
            CursorName(),
            optionalWord( 'INSENSITIVE' ),
            optionalWord( 'SCROLL' ),
            wordSequence( 'CURSOR FOR' ),
            CursorSpecification(),
        ) );
    }

    #[Grammar( '<cursor name> ::= <identifier>' )]
    function CursorName(): Entry {
        return rule( fn() => Identifier() );
    }

    #[Grammar( '<cursor specification> ::= 
        <query expression> 
        [ <order by clause> ] 
        [ <updatability clause> ]' )]
    function CursorSpecification(): Entry {
        return rule( fn() => sequence(
            QueryExpression(),
            optional( OrderByClause() ),
            optional( UpdatabilityClause() ),
        ) );
    }

    #[Grammar( '<order by clause> ::= 
        ORDER BY 
        <sort specification list>' )]
    function OrderByClause(): Entry {
        return rule( fn() => sequence(
            wordSequence( 'ORDER BY' ),
            SortSpecificationList(),
        ) );
    }

    #[Grammar( '<sort specification list> ::=
            <sort specification> 
            [ { <comma> <sort specification> }... ]' )]
    function SortSpecificationList(): Entry {
        return rule( fn() => commaList( SortSpecification() ) );
    }

    #[Grammar( '<sort specification> ::=
            <sort key> 
            [ <collate clause> ] 
            [ <ordering specification> ]' )]
    function SortSpecification(): Entry {
        return rule( fn() => sequence(
            SortKey(),
            optional( CollateClause() ),
            optional( OrderingSpecification() ),
        ) );
    }

    #[Grammar( '<sort key> ::=
            <column name> 
        |   <unsigned integer>' )]
    function SortKey(): Entry {
        return rule( fn() => oneOf(
            ColumnName(),
            UnsignedInteger(),
        ) );
    }

    #[Grammar( '<ordering specification> ::= ASC | DESC' )]
    function OrderingSpecification(): Entry {
        return rule( fn() => oneOfWords( 'ASC', 'DESC' ) );
    }

    #[Grammar( '<updatability clause> ::= 
        FOR 
        {       READ ONLY 
            |   UPDATE 
                [ OF <column name list> ] 
        }' )]
    function UpdatabilityClause(): Entry {
        return rule( fn() => sequence(
            word( 'FOR' ),
            oneOf(
                wordSequence( 'READ ONLY' ),
                sequence(
                    word( 'UPDATE' ),
                    optionalSequence(
                        word( 'OF' ),
                        ColumnNameList(),
                    )
                )
            )
        ) );
    }

    #[Grammar( '<dynamic declare cursor> ::=
        DECLARE 
        <cursor name> 
        [ INSENSITIVE ] 
        [ SCROLL ] 
        CURSOR FOR 
        <statement name>' )]
    function DynamicDeclareCursor(): Entry {
        return rule( fn() => sequence(
            word( 'DECLARE' ),
            CursorName(),
            optionalWord( 'INSENSITIVE' ),
            optionalWord( 'SCROLL' ),
            wordSequence( 'CURSOR FOR' ),
            StatementName(),
        ) );
    }

    #[Grammar( '<statement name> ::= <identifier>' )]
    function StatementName(): Entry {
        return rule( fn() => Identifier() );
    }

    #[Grammar( '<procedure> ::=
        PROCEDURE 
        <procedure name> 
        <parameter declaration list> 
        <semicolon> 
        <SQL procedure statement> 
        <semicolon>' )]
    function Procedure(): Entry {
        return rule( fn() => sequence(
            word( 'PROCEDURE' ),
            ProcedureName(),
            ParameterDeclarationList(),
            SemiColon(),
            SqlProcedureStatement(),
            SemiColon(),
        ) );
    }

    #[Grammar( '<procedure name> ::= <identifier>' )]
    function ProcedureName(): Entry {
        return rule( fn() => Identifier() );
    }

    #[Grammar( '<parameter declaration list> ::=
        <left paren> <parameter declaration> [ { <comma> <parameter declaration> }... ] <right paren>' )]
    function ParameterDeclarationList(): Entry {
        return rule( fn() => parenEnclosed( commaList( ParameterDeclaration() ) ) );
    }

    #[Grammar( '<parameter declaration> ::=
            <parameter name> 
            <data type> 
        |   <status parameter>' )]
    function ParameterDeclaration(): Entry {
        return rule( fn() => oneOf(
            sequence(
                ParameterName(),
                DataType(),
            ),
            StatusParameter(),
        ) );
    }

    #[Grammar( '<status parameter> ::= SQLCODE | SQLSTATE' )]
    function StatusParameter(): Entry {
        return rule( fn() => oneOfWords( 'SQLCODE', 'SQLSTATE' ) );
    }

    #[Grammar( '<SQL procedure statement> ::=
            <SQL schema statement>
        |   <SQL data statement>
        |   <SQL transaction statement>
        |   <SQL connection statement>
        |   <SQL session statement>
        |   <SQL dynamic statement>
        |   <SQL diagnostics statement>' )]
    function SqlProcedureStatement(): Entry {
        return rule( fn() => oneOf(
            SqlSchemaStatement(),
            SqlDataStatement(),
            SqlTransactionStatement(),
            SqlConnectionStatement(),
            SqlSessionStatement(),
            SqlDynamicStatement(),
            SqlDiagnosticsStatement(),
        ) );
    }

    #[Grammar( '<SQL schema statement> ::=
            <SQL schema definition statement>
        |   <SQL schema manipulation statement>' )]
    function SqlSchemaStatement(): Entry {
        return rule( fn() => oneOf(
            SqlSchemaDefinitionStatement(),
            SqlSchemaManipulationStatement(),
        ) );
    }

    #[Grammar( '<SQL schema definition statement> ::=
            <schema definition>
        |   <table definition>
        |   <view definition>
        |   <grant statement>
        |   <domain definition>
        |   <character set definition>
        |   <collation definition>
        |   <translation definition>
        |   <assertion definition>' )]
    function SqlSchemaDefinitionStatement(): Entry {
        return rule( fn() => oneOf(
            SchemaDefinition(),
            TableDefinition(),
            ViewDefinition(),
            GrantStatement(),
            DomainDefinition(),
            CharacterSetDefinition(),
            CollationDefinition(),
            TranslationDefinition(),
            AssertionDefinition(),
        ) );
    }

    #[Grammar( '<schema definition> ::=
        CREATE SCHEMA 
        <schema name clause>
        [ <schema character set specification> ]
        [ <schema element>... ]' )]
    function SchemaDefinition(): Entry {
        return rule( fn() => sequence(
            wordSequence( 'CREATE SCHEMA' ),
            SchemaNameClause(),
            zeroOrOne( SchemaCharacterSetSpecification() ),
            zeroOrMore( SchemaElement() ),
        ) );
    }

    #[Grammar( '<schema name clause> ::=
            <schema name>
        |   AUTHORIZATION 
            <schema authorization identifier>
        |   <schema name> 
            AUTHORIZATION 
            <schema authorization identifier>' )]
    function SchemaNameClause(): Entry {
        return rule( fn() => oneOf(
            sequence(
                word( 'AUTHORIZATION' ),
                SchemaAuthorizationIdentifier(),
            ),
            sequence(
                SchemaName(),
                optionalSequence(
                    word( 'AUTHORIZATION' ),
                    SchemaAuthorizationIdentifier(),
                ),
            ),
        ) );
    }

    #[Grammar( '<schema authorization identifier> ::= <authorization identifier>' )]
    function SchemaAuthorizationIdentifier(): Entry {
        return rule( fn() => AuthorizationIdentifier() );
    }

    #[Grammar( '<schema character set specification> ::= 
        DEFAULT CHARACTER SET 
        <character set specification>' )]
    function SchemaCharacterSetSpecification(): Entry {
        return rule( fn() => sequence(
            wordSequence( 'DEFAULT CHARACTER SET' ),
            CharacterSetSpecification(),
        ) );
    }

    #[Grammar( '<schema element> ::=
            <domain definition>
        |   <table definition>
        |   <view definition>
        |   <grant statement>
        |   <assertion definition>
        |   <character set definition>
        |   <collation definition>
        |   <translation definition>' )]
    function SchemaElement(): Entry {
        return rule( fn() => sequence(
            DomainDefinition(),
            TableDefinition(),
            ViewDefinition(),
            GrantStatement(),
            AssertionDefinition(),
            CharacterSetDefinition(),
            CollationDefinition(),
            TranslationDefinition(),
        ) );
    }

    #[Grammar( '<domain definition> ::=
        CREATE DOMAIN 
        <domain name> 
        [ AS ] 
        <data type>
        [ <default clause> ] 
        [ <domain constraint> ] 
        [ <collate clause> ]' )]
    function DomainDefinition(): Entry {
        return rule( fn() => sequence(
            wordSequence( 'CREATE DOMAIN' ),
            DomainName(),
            optionalWord( 'AS' ),
            DataType(),
            optional( DefaultClause() ),
            optional( DomainConstraint() ),
            optional( CollateClause() ),
        ) );
    }

    #[Grammar( '<domain constraint> ::=
        [ <constraint name definition> ] 
        <check constraint definition> 
        [ <constraint attributes> ]' )]
    function DomainConstraint(): Entry {
        return rule( fn() => sequence(
            optional( ConstraintNameDefinition() ),
            CheckConstraintDefinition(),
            optional( ConstraintAttributes() ),
        ) );
    }

    #[Grammar( '<constraint attributes> ::=
        <constraint check time> 
        [ 
            [ NOT ] 
            DEFERRABLE ]
        | 
            [ NOT ] 
            DEFERRABLE 
                [ <constraint check time> ]' )]
    function ConstraintAttributes(): Entry {
        return rule( fn() => oneOf(
            ConstraintCheckTime(),
            sequence(
                optionalWord( 'NOT' ),
                word( 'DEFERRABLE' ),
                optional( ConstraintCheckTime() ),
            ),
        ) );
    }

    #[Grammar( '<table definition> ::=
        CREATE 
        [   { GLOBAL | LOCAL } 
            TEMPORARY 
        ] 
        TABLE 
        <table name> 
        <table element list> 
        [   ON COMMIT 
            { DELETE | PRESERVE } 
            ROWS 
        ]' )]
    function TableDefinition(): Entry {
        return rule( fn() => sequence(
            word( 'CREATE' ),
            optionalSequence(
                oneOfWords( 'GLOBAL', 'LOCAL' ),
                word( 'TEMPORARY' ),
            ),
            word( 'TABLE' ),
            TableName(),
            TableElementList(),
            optionalSequence(
                wordSequence( 'ON COMMIT' ),
                oneOfWords( 'DELETE', 'PRESERVE' ),
                word( 'ROWS' ),
            ),
        ) );
    }

    #[Grammar( '<view definition> ::=
        CREATE VIEW 
        <table name> 
        [ <left paren> <view column list> <right paren> ]
        AS 
        <query expression> 
        [   WITH 
            [ <levels clause> ] 
            CHECK OPTION 
        ]' )]
    function ViewDefinition(): Entry {
        return rule( fn() => sequence(
            wordSequence( 'CREATE VIEW' ),
            TableName(),
            optional( parenEnclosed( ViewColumnList() ) ),
            word( 'AS' ),
            QueryExpression(),
            optionalSequence(
                word( 'WITH' ),
                optional( LevelsClause() ),
                wordSequence( 'CHECK OPTION' ),
            ),
        ) );
    }

    #[Grammar( '<view column list> ::= <column name list>' )]
    function ViewColumnList(): Entry {
        return rule( fn() => ColumnNameList() );
    }

    #[Grammar( '<levels clause> ::= CASCADED | LOCAL' )]
    function LevelsClause(): Entry {
        return rule( fn() => oneOfWords( 'CASCADED', 'LOCAL' ) );
    }

    #[Grammar( '<grant statement> ::=
        GRANT 
        <privileges> 
        ON 
        <object name> 
        TO 
        <grantee> [ { <comma> <grantee> }... ] 
        [ WITH GRANT OPTION ]' )]
    function GrantStatement(): Entry {
        return rule( fn() => sequence(
            word( 'GRANT' ),
            Privileges(),
            word( 'ON' ),
            ObjectName(),
            word( 'TO' ),
            commaList( Grantee() ),
            optional( wordSequence( 'WITH GRANT OPTION' ) ),
        ) );
    }

    #[Grammar( '<privileges> ::= 
            ALL PRIVILEGES 
        |   <action list>' )]
    function Privileges(): Entry {
        return rule( fn() => oneOf(
            wordSequence( 'ALL PRIVILEGES' ),
            ActionList(),
        ) );
    }

    #[Grammar( '<action list> ::=
            <action> 
            [ { <comma> <action> }... ]' )]
    function ActionList(): Entry {
        return rule( fn() => commaList( Action() ) );
    }

    #[Grammar( '<action> ::=
            SELECT
        |   DELETE
        |   INSERT 
            [ <left paren> <privilege column list> <right paren> ]
        |   UPDATE 
            [ <left paren> <privilege column list> <right paren> ]
        |   REFERENCES 
            [ <left paren> <privilege column list> <right paren> ]
        |   USAGE' )]
    function Action(): Entry {
        return rule( fn() => oneOf(
            oneOfWords( 'SELECT', 'DELETE', 'USAGE' ),
            sequence(
                oneOfWords( 'INSERT', 'UPDATE', 'REFERENCES' ),
                optional(
                    parenEnclosed( PrivilegeColumnList() ),
                ),
            ),
        ) );
    }

    #[Grammar( '<privilege column list> ::= <column name list>' )]
    function PrivilegeColumnList(): Entry {
        return rule( fn() => ColumnNameList() );
    }

    #[Grammar( '<object name> ::=
            [ TABLE ] 
            <table name>
        |   DOMAIN 
            <domain name>
        |   COLLATION 
            <collation name>
        |   CHARACTER SET 
            <character set name>
        |   TRANSLATION 
            <translation name>' )]
    function ObjectName(): Entry {
        return rule( fn() => oneOf(
            sequence(
                word( 'DOMAIN' ),
                DomainName(),
            ),
            sequence(
                word( 'COLLATION' ),
                CollationName(),
            ),
            sequence(
                word( 'TRANSLATION' ),
                TranslationName(),
            ),
            sequence(
                wordSequence( 'CHARACTER SET' ),
                CharacterSetName(),
            ),
            sequence(
                optionalWord( 'TABLE' ),
                TableName(),
            ),
        ) );
    }

    #[Grammar( '<grantee> ::= 
            PUBLIC 
        |   <authorization identifier>' )]
    function Grantee(): Entry {
        return rule( fn() => oneOf(
            word( 'PUBLIC' ),
            AuthorizationIdentifier(),
        ) );
    }

    #[Grammar( '<assertion definition> ::=
        CREATE ASSERTION 
        <constraint name> 
        <assertion check> 
        [ <constraint attributes> ]' )]
    function AssertionDefinition(): Entry {
        return rule( fn() => sequence(
            wordSequence( 'CREATE ASSERTION' ),
            ConstraintName(),
            AssertionCheck(),
            optional( ConstraintAttributes() ),
        ) );
    }

    #[Grammar( '<assertion check> ::= 
        CHECK 
        <left paren> <search condition> <right paren>' )]
    function AssertionCheck(): Entry {
        return rule( fn() => sequence(
            word( 'CHECK' ),
            parenEnclosed( SearchCondition() ),
        ) );
    }

    #[Grammar( '<character set definition> ::=
            CREATE CHARACTER SET 
            <character set name> 
            [ AS ] 
            <character set source>
            [       <collate clause> 
                |   <limited collation definition> ]' )]
    function CharacterSetDefinition(): Entry {
        return rule( fn() => sequence(
            wordSequence( 'CREATE CHARACTER SET' ),
            CharacterSetName(),
            optionalWord( 'AS' ),
            CharacterSetSource(),
            oneOf(
                CollateClause(),
                LimitedCollationDefinition(),
            ),
        ) );
    }

    #[Grammar( '<character set source> ::= 
        GET 
        <existing character set name>' )]
    function CharacterSetSource(): Entry {
        return rule( fn() => sequence(
            word( 'GET' ),
            ExistingCharacterSetName(),
        ) );
    }

    #[Grammar( '<existing character set name> ::=
            <standard character repertoire name>
        |   <implementation-defined character repertoire name>
        |   <schema character set name>' )]
    function ExistingCharacterSetName(): Entry {
        return rule( fn() => oneOf(
            StandardCharacterRepertoireName(),
            ImplementationDefinedCharacterRepertoireName(),
            SchemaCharacterSetName(),

        ) );
    }

    #[Grammar( '<schema character set name> ::= <character set name>' )]
    function SchemaCharacterSetName(): Entry {
        return rule( fn() => CharacterSetName() );
    }

    #[Grammar( '<limited collation definition> ::=
        COLLATION FROM 
        <collation source>' )]
    function LimitedCollationDefinition(): Entry {
        return rule( fn() => sequence(
            wordSequence( 'COLLATION FROM' ),
            CollationSource(),
        ) );
    }

    #[Grammar( '<collation source> ::= 
            <collating sequence definition> 
        |   <translation collation>' )]
    function CollationSource(): Entry {
        return rule( fn() => oneOf(
            CollatingSequenceDefinition(),
            TranslationCollation(),
        ) );
    }

    #[Grammar( '<collating sequence definition> ::=
            <external collation>
        |   <schema collation name>
        |   DESC 
            <left paren> <collation name> <right paren>
        |   DEFAULT' )]
    function CollatingSequenceDefinition(): Entry {
        return rule( fn() => oneOf(
            word( 'DEFAULT' ),
            sequence(
                word( 'DESC' ),
                parenEnclosed( CollationName() ),
            ),
            ExternalCollation(),
            SchemaCollationName(),
        ) );
    }

    #[Grammar( '<external collation> ::=
        EXTERNAL 
        <left paren> <quote> <external collation name> <quote> <right paren>' )]
    function ExternalCollation(): Entry {
        return rule( fn() => sequence(
            word( 'EXTERNAL' ),
            parenEnclosed( quoted( ExternalCollationName() ) ),
        ) );
    }

    #[Grammar( '<external collation name> ::= 
            <standard collation name> 
        |   <implementation-defined collation name>' )]
    function ExternalCollationName(): Entry {
        return rule( fn() => oneOf(
            StandardCollationName(),
            ImplementationDefinedCollationName(),
        ) );
    }

    #[Grammar( '<standard collation name> ::= <collation name>' )]
    function StandardCollationName(): Entry {
        return rule( fn() => CollationName() );
    }

    #[Grammar( '<implementation-defined collation name> ::= <collation name>' )]
    function ImplementationDefinedCollationName(): Entry {
        return rule( fn() => CollationName() );
    }

    #[Grammar( '<schema collation name> ::= <collation name>' )]
    function SchemaCollationName(): Entry {
        return rule( fn() => CollationName() );
    }

    #[Grammar( '<translation collation> ::= 
        TRANSLATION 
        <translation name> 
        [   THEN COLLATION 
            <collation name> ]' )]
    function TranslationCollation(): Entry {
        return rule( fn() => sequence(
            word( 'TRANSLATION' ),
            TranslationName(),
            optionalSequence(
                wordSequence( 'THEN COLLATION' ),
                CollationName(),
            ),
        ) );
    }

    #[Grammar( '<collation definition> ::=
        CREATE COLLATION 
        <collation name> 
        FOR 
        <character set specification>
        FROM 
        <collation source> 
        [ <pad attribute> ]' )]
    function CollationDefinition(): Entry {
        return rule( fn() => sequence(
            wordSequence( 'CREATE COLLATION' ),
            CollationName(),
            word( 'FOR' ),
            CharacterSetSpecification(),
            word( 'FROM' ),
            CollationSource(),
            optional( PadAttribute() ),
        ) );
    }

    #[Grammar( '<pad attribute> ::= NO PAD | PAD SPACE' )]
    function PadAttribute(): Entry {
        return rule( fn() => oneOf(
            wordSequence( 'NO PAD' ),
            wordSequence( 'PAD SPACE' ),
        ) );
    }

    #[Grammar( '<translation definition> ::=
        CREATE TRANSLATION 
        <translation name>
        FOR 
        <source character set specification>
        TO 
        <target character set specification>
        FROM 
        <translation source>' )]
    function TranslationDefinition(): Entry {
        return rule( fn() => sequence(
            wordSequence( 'CREATE TRANSLATION' ),
            TranslationName(),
            word( 'FOR' ),
            SourceCharacterSetSpecification(),
            word( 'TO' ),
            TargetCharacterSetSpecification(),
            word( 'FROM' ),
            TranslationSource(),
        ) );
    }

    #[Grammar( '<source character set specification> ::= <character set specification>' )]
    function SourceCharacterSetSpecification(): Entry {
        return rule( fn() => CharacterSetSpecification() );
    }

    #[Grammar( '<target character set specification> ::= <character set specification>' )]
    function TargetCharacterSetSpecification(): Entry {
        return rule( fn() => CharacterSetSpecification() );
    }

    #[Grammar( '<translation source> ::= <translation specification>' )]
    function TranslationSource(): Entry {
        return rule( fn() => TranslationSpecification() );
    }

    #[Grammar( '<translation specification> ::=
            <external translation>
        |   IDENTITY
        |   <schema translation name>' )]
    function TranslationSpecification(): Entry {
        return rule( fn() => oneOf(
            word( 'IDENTITY' ),
            ExternalTranslation(),
            SchemaTranslationName(),
        ) );
    }

    #[Grammar( '<external translation> ::=
        EXTERNAL 
        <left paren> <quote> <external translation name> <quote> <right paren>' )]
    function ExternalTranslation(): Entry {
        return rule( fn() => sequence(
            word( 'EXTERNAL' ),
            parenEnclosed(
                quoted( ExternalTranslationName() ),
            ),
        ) );

    }

    #[Grammar( '<external translation name> ::=
            <standard translation name>
        |   <implementation-defined translation name>' )]
    function ExternalTranslationName(): Entry {
        return rule( fn() => oneOf(
            StandardTranslationName(),
            ImplementationDefinedTranslationName(),
        ) );
    }

    #[Grammar( '<standard translation name> ::= <translation name>' )]
    function StandardTranslationName(): Entry {
        return rule( fn() => TranslationName() );
    }

    #[Grammar( '<implementation-defined translation name> ::= <translation name>' )]
    function ImplementationDefinedTranslationName(): Entry {
        return rule( fn() => TranslationName() );
    }

    #[Grammar( '<schema translation name> ::= <translation name>' )]
    function SchemaTranslationName(): Entry {
        return rule( fn() => TranslationName() );
    }

    #[Grammar( '<SQL schema manipulation statement> ::=
            <drop schema statement>
        |   <alter table statement>
        |   <drop table statement>
        |   <drop view statement>
        |   <revoke statement>
        |   <alter domain statement>
        |   <drop domain statement>
        |   <drop character set statement>
        |   <drop collation statement>
        |   <drop translation statement>
        |   <drop assertion statement>' )]
    function SqlSchemaManipulationStatement(): Entry {
        return rule( fn() => oneOf(
            DropSchemaStatement(),
            AlterTableStatement(),
            DropTableStatement(),
            DropViewStatement(),
            RevokeStatement(),
            AlterDomainStatement(),
            DropDomainStatement(),
            DropCharacterSetStatement(),
            DropCollationStatement(),
            DropTranslationStatement(),
            DropAssertionStatement(),
        ) );
    }

    #[Grammar( '<drop schema statement> ::= 
        DROP SCHEMA <schema name> <drop behaviour>' )]
    function DropSchemaStatement(): Entry {
        return rule( fn() => sequence(
            wordSequence( 'DROP SCHEMA' ),
            SchemaName(),
            DropBehaviour(),
        ) );
    }

    #[Grammar( '<drop behaviour> ::= CASCADE | RESTRICT' )]
    function DropBehaviour(): Entry {
        return rule( fn() => oneOfWords( 'CASCADE', 'RESTRICT' ) );
    }

    #[Grammar( '<alter table statement> ::= 
        ALTER TABLE <table name> <alter table action>' )]
    function AlterTableStatement(): Entry {
        return rule( fn() => sequence(
            wordSequence( 'ALTER TABLE' ),
            TableName(),
            AlterTableAction(),
        ) );
    }

    #[Grammar( '<alter table action> ::=
              <add column definition>
        |   <alter column definition>
        |   <drop column definition>
        |   <add table constraint definition>
        |   <drop table constraint definition>' )]
    function AlterTableAction(): Entry {
        return rule( fn() => sequence(
            AddColumnDefinition(),
            AlterColumnDefinition(),
            DropColumnDefinition(),
            AddTableConstraintDefinition(),
            DropTableConstraintDefinition(),
        ) );
    }

    #[Grammar( '<add column definition> ::= 
        ADD 
        [ COLUMN ] 
        <column definition>' )]
    function AddColumnDefinition(): Entry {
        return rule( fn() => sequence(
            word( 'ADD' ),
            optionalWord( 'COLUMN' ),
            ColumnDefinition(),
        ) );
    }

    #[Grammar( '<alter column definition> ::= 
        ALTER 
        [ COLUMN ] 
        <column name> 
        <alter column action>' )]
    function AlterColumnDefinition(): Entry {
        return rule( fn() => sequence(
            word( 'ALTER' ),
            optionalWord( 'COLUMN' ),
            ColumnName(),
            AfterColumnAction(),
        ) );
    }

    #[Grammar( '<alter column action> ::= 
            <set column default clause> 
        |   <drop column default clause>' )]
    function AfterColumnAction(): Entry {
        return rule( fn() => oneOf(
            SetColumnDefaultClause(),
            DropColumnDefaultClause(),
        ) );
    }

    #[Grammar( '<set column default clause> ::= 
        SET 
        <default clause>' )]
    function SetColumnDefaultClause(): Entry {
        return rule( fn() => sequence(
            word( 'SET' ),
            DefaultClause(),
        ) );
    }

    #[Grammar( '<drop column default clause> ::= DROP DEFAULT' )]
    function DropColumnDefaultClause(): Entry {
        return rule( fn() => wordSequence( 'DROP DEFAULT' ) );
    }

    #[Grammar( '<drop column definition> ::= 
        DROP 
        [ COLUMN ] 
        <column name> 
        <drop behaviour>' )]
    function DropColumnDefinition(): Entry {
        return rule( fn() => sequence(
            word( 'DROP' ),
            optionalWord( 'COLUMN' ),
            ColumnName(),
            DropBehaviour(),
        ) );
    }

    #[Grammar( '<add table constraint definition> ::= 
        ADD 
        <table constraint definition>' )]
    function AddTableConstraintDefinition(): Entry {
        return rule( fn() => sequence(
            word( 'ADD' ),
            TableConstraintDefinition(),
        ) );
    }

    #[Grammar( '<drop table constraint definition> ::= 
        DROP CONSTRAINT 
        <constraint name> 
        <drop behaviour>' )]
    function DropTableConstraintDefinition(): Entry {
        return rule( fn() => sequence(
            wordSequence( 'DROP CONSTRAINT' ),
            ConstraintName(),
            DropBehaviour(),
        ) );
    }

    #[Grammar( '<drop table statement> ::= 
       DROP TABLE 
       <table name> 
       <drop behaviour>' )]
    function DropTableStatement(): Entry {
        return rule( fn() => sequence(
            wordSequence( 'DROP TABLE' ),
            TableName(),
            DropBehaviour(),
        ) );
    }

    #[Grammar( '<drop view statement> ::= 
        DROP VIEW 
        <table name> 
        <drop behaviour>' )]
    function DropViewStatement(): Entry {
        return rule( fn() => sequence(
            wordSequence( 'DROP VIEW' ),
            TableName(),
            DropBehaviour(),
        ) );
    }

    #[Grammar( '<revoke statement> ::=
        REVOKE 
        [ GRANT OPTION FOR ] 
        <privileges> 
        ON 
        <object name>
        FROM 
        <grantee> [ { <comma> <grantee> }... ] 
        <drop behaviour>' )]
    function RevokeStatement(): Entry {
        return rule( fn() => sequence(
            word( 'REVOKE' ),
            optional( wordSequence( 'GRANT OPTION FOR' ) ),
            Privileges(),
            word( 'ON' ),
            ObjectName(),
            word( 'FROM' ),
            commaList( Grantee() ),
            DropBehaviour(),
        ) );
    }

    #[Grammar( '<alter domain statement> ::= 
        ALTER DOMAIN 
        <domain name> 
        <alter domain action>' )]
    function AlterDomainStatement(): Entry {
        return rule( fn() => sequence(
            wordSequence( 'ALTER DOMAIN' ),
            DomainName(),
            AlterDomainAction(),
        ) );
    }

    #[Grammar( '<alter domain action> ::=
            <set domain default clause>
        |   <drop domain default clause>
        |   <add domain constraint definition>
        |   <drop domain constraint definition>
        ' )]
    function AlterDomainAction(): Entry {
        return rule( fn() => oneOf(
            SetDomainDefaultClause(),
            DropDomainDefaultClause(),
            AddDomainConstraintDefinition(),
            DropDomainConstraintDefinition(),
        ) );
    }

    #[Grammar( '<set domain default clause> ::= 
        SET 
        <default clause>' )]
    function SetDomainDefaultClause(): Entry {
        return rule( fn() => sequence(
            word( 'SET' ),
            DefaultClause(),
        ) );
    }

    #[Grammar( '<drop domain default clause> ::= DROP DEFAULT' )]
    function DropDomainDefaultClause(): Entry {
        return rule( fn() => wordSequence( 'DROP DEFAULT' ) );
    }

    #[Grammar( '<add domain constraint definition> ::= 
        ADD 
        <domain constraint>' )]
    function AddDomainConstraintDefinition(): Entry {
        return rule( fn() => sequence(
            word( 'ADD' ),
            DomainConstraint(),
        ) );
    }

    #[Grammar( '<drop domain constraint definition> ::= 
        DROP CONSTRAINT 
        <constraint name>' )]
    function DropDomainConstraintDefinition(): Entry {
        return rule( fn() => sequence(
            wordSequence( 'DROP CONSTRAINT' ),
            ConstraintName(),
        ) );
    }

    #[Grammar( '<drop domain statement> ::= 
        DROP DOMAIN 
        <domain name> 
        <drop behaviour>' )]
    function DropDomainStatement(): Entry {
        return rule( fn() => sequence(
            wordSequence( 'DROP DOMAIN' ),
            DomainName(),
            DropBehaviour(),
        ) );
    }

    #[Grammar( '<drop character set statement> ::= 
        DROP CHARACTER SET 
        <character set name>' )]
    function DropCharacterSetStatement(): Entry {
        return rule( fn() => sequence(
            wordSequence( 'DROP CHARACTER SET' ),
            CharacterSetName(),
        ) );
    }

    #[Grammar( '<drop collation statement> ::= 
        DROP COLLATION 
        <collation name>' )]
    function DropCollationStatement(): Entry {
        return rule( fn() => sequence(
            wordSequence( 'DROP COLLATION' ),
            CollationName(),
        ) );
    }

    #[Grammar( '<drop translation statement> ::= 
        DROP TRANSLATION 
        <translation name>' )]
    function DropTranslationStatement(): Entry {
        return rule( fn() => sequence(
            wordSequence( 'DROP TRANSLATION' ),
            TranslationName(),
        ) );
    }

    #[Grammar( '<drop assertion statement> ::= 
        DROP ASSERTION 
        <constraint name>' )]
    function DropAssertionStatement(): Entry {
        return rule( fn() => sequence(
            wordSequence( 'DROP ASSERTION' ),
            ConstraintName(),
        ) );
    }

    #[Grammar( '<SQL data statement> ::=
            <open statement>
        |   <fetch statement>
        |   <close statement>
        |   <select statement: single row>
        |   <SQL data change statement>' )]
    function SqlDataStatement(): Entry {
        return rule( fn() => oneOf(
            OpenStatement(),
            FetchStatement(),
            CloseStatement(),
            SelectStatementSingleRow(),
            SqlDataChangeStatement(),
        ) );
    }

    #[Grammar( '<open statement> ::= 
        OPEN 
        <cursor name>' )]
    function OpenStatement(): Entry {
        return rule( fn() => sequence(
            word( 'OPEN' ),
            CursorName(),
        ) );
    }

    #[Grammar( '<fetch statement> ::=
        FETCH 
        [ 
            [ <fetch orientation> ] 
            FROM ] 
        <cursor name> 
        INTO 
        <fetch target list>' )]
    function FetchStatement(): Entry {
        return rule( fn() => sequence(
            word( 'FETCH' ),
            optionalSequence(
                optional( FetchOrientation() ),
                word( 'FROM' ),
            ),
            CursorName(),
            word( 'INTO' ),
            FetchTargetList(),
        ) );
    }

    #[Grammar( '<fetch orientation> ::=
            NEXT 
        |   PRIOR 
        |   FIRST 
        |   LAST
        |   { ABSOLUTE | RELATIVE } 
            <simple value specification>' )]
    function FetchOrientation(): Entry {
        return rule( fn() => oneOf(
            oneOfWords( 'NEXT', 'PRIOR', 'FIRST', 'LAST' ),
            sequence(
                oneOfWords( 'ABSOLUTE', 'RELATIVE' ),
                SimpleValueSpecification(),
            ),
        ) );
    }

    #[Grammar( '<simple value specification> ::= 
            <parameter name> 
        |   <embedded variable name> 
        |   <literal>' )]
    function SimpleValueSpecification(): Entry {
        return rule( fn() => oneOf(
            ParameterName(),
            EmbeddedVariableName(),
            Literal(),
        ) );
    }

    #[Grammar( '<fetch target list> ::= 
            <target specification> [ { <comma> <target specification> }... ]' )]
    function FetchTargetList(): Entry {
        return rule( fn() => commaList( TargetSpecification() ) );
    }

    #[Grammar( '<target specification> ::=
            <parameter specification>
        |   <variable specification>' )]
    function TargetSpecification(): Entry {
        return rule( fn() => oneOf(
            ParameterSpecification(),
            VariableSpecification(),
        ) );
    }

    #[Grammar( '<close statement> ::= 
        CLOSE 
        <cursor name>' )]
    function CloseStatement(): Entry {
        return rule( fn() => sequence(
            word( 'CLOSE' ),
            CursorName(),
        ) );
    }

    #[Grammar( '<select statement: single row> ::=
        SELECT 
        [ <set quantifier> ] 
        <select list> 
        INTO 
        <select target list> 
        <table expression>' )]
    function SelectStatementSingleRow(): Entry {
        return rule( fn() => sequence(
            word( 'SELECT' ),
            optional( SetQuantifier() ),
            SelectList(),
            word( 'INTO' ),
            SelectTargetList(),
            TableExpression(),
        ) );
    }

    #[Grammar( '<select target list> ::= 
            <target specification> [ { <comma> <target specification> }... ]' )]
    function SelectTargetList(): Entry {
        return rule( fn() => commaList( TargetSpecification() ) );
    }

    #[Grammar( '<SQL data change statement> ::=
            <delete statement: positioned>
        |   <delete statement: searched>
        |   <insert statement>
        |   <update statement: positioned>
        |   <update statement: searched>' )]
    function SqlDataChangeStatement(): Entry {
        return rule( fn() => oneOf(
            DeleteStatementPositioned(),
            DeleteStatementSearched(),
            InsertStatement(),
            UpdateStatementPositioned(),
            UpdateStatementSearched(),
        ) );
    }

    #[Grammar( '<delete statement: positioned> ::= 
        DELETE FROM 
        <table name> 
        WHERE CURRENT OF 
        <cursor name>' )]
    function DeleteStatementPositioned(): Entry {
        return rule( fn() => sequence(
            wordSequence( 'DELETE FROM' ),
            TableName(),
            wordSequence( 'WHERE CURRENT OF' ),
            CursorName(),
        ) );
    }

    #[Grammar( '<delete statement: searched> ::= 
        DELETE FROM 
        <table name> 
        [   WHERE 
            <search condition> ]' )]
    function DeleteStatementSearched(): Entry {
        return rule( fn() => sequence(
            wordSequence( 'DELETE FROM' ),
            TableName(),
            optionalSequence(
                word( 'WHERE' ),
                SearchCondition(),
            ),
        ) );
    }

    #[Grammar( '<insert statement> ::= 
        INSERT INTO 
        <table name> 
        <insert columns and source>' )]
    function InsertStatement(): Entry {
        return rule( fn() => sequence(
            wordSequence( 'INSERT INTO' ),
            TableName(),
            InsertColumnsAndSource(),
        ) );
    }

    #[Grammar( '<insert columns and source> ::=
            [ <left paren> <insert column list> <right paren> ] 
            <query expression>
        |
            DEFAULT VALUES' )]
    function InsertColumnsAndSource(): Entry {
        return rule( fn() => oneOf(
            wordSequence( 'DEFAULT VALUES' ),
            sequence(
                optional( parenEnclosed( InsertColumnList() ) ),
                QueryExpression(),
            ),
        ) );
    }

    #[Grammar( '<insert column list> ::= <column name list>' )]
    function InsertColumnList(): Entry {
        return rule( fn() => ColumnNameList() );
    }

    #[Grammar( '<update statement: positioned> ::=
        UPDATE 
        <table name> 
        SET 
        <set clause list> 
        WHERE CURRENT OF 
        <cursor name>' )]
    function UpdateStatementPositioned(): Entry {
        return rule( fn() => sequence(
            word( 'UPDATE' ),
            TableName(),
            word( 'SET' ),
            SetClauseList(),
            wordSequence( 'WHERE CURRENT OF' ),
            CursorName(),
        ) );
    }

    #[Grammar( '<set clause list> ::= 
        <set clause> [ { <comma> <set clause> } ... ]' )]
    function SetClauseList(): Entry {
        return rule( fn() => commaList( SetClause() ) );
    }

    #[Grammar( '<set clause> ::= 
        <object column> 
        <equals operator> 
        <update source>' )]
    function SetClause(): Entry {
        return rule( fn() => sequence(
            ObjectColumn(),
            EqualsOperator(),
            UpdateSource(),
        ) );
    }

    #[Grammar( '<object column> ::= <column name>' )]
    function ObjectColumn(): Entry {
        return rule( fn() => ColumnName() );
    }

    #[Grammar( '<update source> ::= 
            <value expression> 
        |   <null specification> 
        |   DEFAULT' )]
    function UpdateSource(): Entry {
        return rule( fn() => oneOf(
            word( 'DEFAULT' ),
            NullSpecification(),
            ValueExpression(),
        ) );
    }

    #[Grammar( '<update statement: searched> ::=
        UPDATE 
        <table name> 
        SET 
        <set clause list> 
        [   WHERE 
            <search condition> ]' )]
    function UpdateStatementSearched(): Entry {
        return rule( fn() => sequence(
            word( 'UPDATE' ),
            TableName(),
            word( 'SET' ),
            SetClauseList(),
            optionalSequence(
                word( 'WHERE ' ),
                SearchCondition(),
            ),
        ) );

    }

    #[Grammar( '<SQL transaction statement> ::=
            <set transaction statement>
        |   <set constraints mode statement>
        |   <commit statement>
        |   <rollback statement>' )]
    function SqlTransactionStatement(): Entry {
        return rule( fn() => oneOf(
            SetTransactionStatement(),
            SetConstraintsModeStatement(),
            CommitStatement(),
            RollbackStatement(),
        ) );
    }

    #[Grammar( '<set transaction statement> ::=
            SET TRANSACTION 
            <transaction mode> [ { <comma> <transaction mode> }... ]' )]
    function SetTransactionStatement(): Entry {
        return rule( fn() => sequence(
            wordSequence( 'SET TRANSACTION' ),
            commaList( TransactionMode() ),
        ) );
    }

    #[Grammar( '<transaction mode> ::=
            <isolation level>
        |   <transaction access mode>
        |   <diagnostics size>' )]
    function TransactionMode(): Entry {
        return rule( fn() => oneOf(
            IsolationLevel(),
            TransactionAccessMode(),
            DiagnosticsSize(),
        ) );
    }

    #[Grammar( '<isolation level> ::= 
        ISOLATION LEVEL 
        <level of isolation>' )]
    function IsolationLevel(): Entry {
        return rule( fn() => sequence(
            wordSequence( 'ISOLATION LEVEL' ),
            LevelOfIsolation(),
        ) );
    }

    #[Grammar( '<level of isolation> ::=
            READ UNCOMMITTED
        |   READ COMMITTED
        |   REPEATABLE READ
        |   SERIALIZABLE' )]
    function LevelOfIsolation(): Entry {
        return rule( fn() => oneOf(
            wordSequence( 'READ UNCOMMITTED' ),
            wordSequence( 'READ COMMITTED' ),
            wordSequence( 'REPEATABLE READ' ),
            wordSequence( 'SERIALIZABLE' ),
        ) );
    }

    #[Grammar( '<transaction access mode> ::= 
            READ ONLY 
        |   READ WRITE' )]
    function TransactionAccessMode(): Entry {
        return rule( fn() => oneOf(
            wordSequence( 'READ ONLY' ),
            wordSequence( 'READ WRITE' ),
        ) );
    }

    #[Grammar( '<diagnostics size> ::= 
        DIAGNOSTICS SIZE 
        <number of conditions>' )]
    function DiagnosticsSize(): Entry {
        return rule( fn() => sequence(
            wordSequence( 'DIAGNOSTICS SIZE' ),
            NumberOfConditions(),
        ) );
    }

    #[Grammar( '<number of conditions> ::= <simple value specification>' )]
    function NumberOfConditions(): Entry {
        return rule( fn() => SimpleValueSpecification() );
    }

    #[Grammar( '<set constraints mode statement> ::=
            SET CONSTRAINTS 
            <constraint name list> 
            { DEFERRED | IMMEDIATE }' )]
    function SetConstraintsModeStatement(): Entry {
        return rule( fn() => sequence(
            wordSequence( 'SET CONSTRAINTS' ),
            ConstraintNameList(),
            oneOfWords( 'DEFERRED', 'IMMEDIATE' ),
        ) );
    }

    #[Grammar( '<constraint name list> ::= 
            ALL 
        |   <constraint name> [ { <comma> <constraint name> }... ]' )]
    function ConstraintNameList(): Entry {
        return rule( fn() => oneOf(
            word( 'ALL' ),
            commaList( ConstraintName() ),
        ) );
    }

    #[Grammar( '<commit statement> ::= COMMIT [ WORK ]' )]
    function CommitStatement(): Entry {
        return rule( fn() => oneOfWords( 'COMMIT', 'WORK' ) );
    }

    #[Grammar( '<rollback statement> ::= ROLLBACK [ WORK ]' )]
    function RollbackStatement(): Entry {
        return rule( fn() => sequence(
            word( 'ROLLBACK' ),
            optionalWord( 'WORK' ),
        ) );
    }

    #[Grammar( '<SQL connection statement> ::=
            <connect statement>
        |   <set connection statement>
        |   <disconnect statement>' )]
    function SqlConnectionStatement(): Entry {
        return rule( fn() => oneOf(
            ConnectStatement(),
            SetConnectionStatement(),
            DisconnectStatement(),
        ) );
    }

    #[Grammar( '<connect statement> ::= CONNECT TO <connection target>' )]
    function ConnectStatement(): Entry {
        return rule( fn() => sequence(
            wordSequence( 'CONNECT TO' ),
            ConnectionTarget(),
        ) );
    }

    #[Grammar( '<connection target> ::=
            <SQL-server name> 
            [   AS 
                <connection name> ] 
            [   USER 
                <user name> ]
        | DEFAULT' )]
    function ConnectionTarget(): Entry {
        return rule( fn() => oneOf(
            word( 'DEFAULT' ),
            sequence(
                SqlServerName(),
                optionalSequence(
                    word( 'AS' ),
                    ConnectionName(),
                ),
                optionalSequence(
                    word( 'USER' ),
                    UserName(),
                ),
            ),
        ) );
    }

    #[Grammar( '<SQL-server name> ::= <simple value specification>' )]
    function SqlServerName(): Entry {
        return rule( fn() => SimpleValueSpecification() );
    }

    #[Grammar( '<connection name> ::= <simple value specification>' )]
    function ConnectionName(): Entry {
        return rule( fn() => SimpleValueSpecification() );
    }

    #[Grammar( '<user name> ::= <simple value specification>' )]
    function UserName(): Entry {
        return rule( fn() => SimpleValueSpecification() );
    }

    #[Grammar( '<set connection statement> ::= 
        SET CONNECTION 
        <connection object>' )]
    function SetConnectionStatement(): Entry {
        return rule( fn() => sequence(
            wordSequence( 'SET CONNECTION' ),
            ConnectionObject(),
        ) );
    }

    #[Grammar( '<connection object> ::= 
            DEFAULT 
        |   <connection name>' )]
    function ConnectionObject(): Entry {
        return rule( fn() => oneOf(
            word( 'DEFAULT' ),
            ConnectionName(),
        ) );
    }

    #[Grammar( '<disconnect statement> ::= 
        DISCONNECT 
        <disconnect object>' )]
    function DisconnectStatement(): Entry {
        return rule( fn() => sequence(
            word( 'DISCONNECT' ),
            DisconnectObject(),
        ) );
    }

    #[Grammar( '<disconnect object> ::=
            <connection object> | ALL | CURRENT' )]
    function DisconnectObject(): Entry {
        return rule( fn() => oneOf(
            word( 'ALL' ),
            word( 'CURRENT' ),
            ConnectionObject(),
        ) );
    }

    #[Grammar( '<SQL session statement> ::=
            <set catalog statement>
        |   <set schema statement>
        |   <set names statement>
        |   <set session authorization identifier statement>
        |   <set local time zone statement>' )]
    function SqlSessionStatement(): Entry {
        return rule( fn() => oneOf(
            SetCatalogStatement(),
            ValueSpecification(),
            SetSchemaStatement(),
            SetNamesStatement(),
            SetSessionAuthorizationIdentifierStatement(),
            SetLocalTimeZoneStatement(),
            SetTimeZoneValue(),
        ) );
    }

    #[Grammar( '<set catalog statement> ::= 
        SET CATALOG 
        <value specification>' )]
    function SetCatalogStatement(): Entry {
        return rule( fn() => sequence(
            wordSequence( 'SET CATALOG' ),
            ValueSpecification(),
        ) );
    }

    #[Grammar( '<value specification> ::=
            <literal> 
        |   <general value specification>' )]
    function ValueSpecification(): Entry {
        return rule( fn() => oneOf(
            Literal(),
            GeneralValueSpecification(),

        ) );
    }

    #[Grammar( '<set schema statement> ::= 
        SET SCHEMA 
        <value specification>' )]
    function SetSchemaStatement(): Entry {
        return rule( fn() => sequence(
            wordSequence( 'SET SCHEMA' ),
            ValueSpecification(),
        ) );

    }

    #[Grammar( '<set names statement> ::= 
        SET NAMES 
        <value specification>' )]
    function SetNamesStatement(): Entry {
        return rule( fn() => sequence(
            wordSequence( 'SET NAMES' ),
            ValueSpecification(),
        ) );

    }

    #[Grammar( '<set session authorization identifier statement> ::= 
        SET SESSION AUTHORIZATION 
        <value specification>' )]
    function SetSessionAuthorizationIdentifierStatement(): Entry {
        return rule( fn() => sequence(
            wordSequence( 'SET SESSION AUTHORIZATION' ),
            ValueSpecification(),
        ) );

    }

    #[Grammar( '<set local time zone statement> ::= 
        SET TIME ZONE 
        <set time zone value>' )]
    function SetLocalTimeZoneStatement(): Entry {
        return rule( fn() => sequence(
            wordSequence( 'SET TIME ZONE' ),
            SetTimeZoneValue(),
        ) );
    }

    #[Grammar( '<set time zone value> ::=
            <interval value expression> 
        |   LOCAL' )]
    function SetTimeZoneValue(): Entry {
        return rule( fn() => oneOf(
            word( 'LOCAL' ),
            IntervalValueExpression(),
        ) );
    }

    #[Grammar( '<SQL dynamic statement> ::=
            <system descriptor statement>
        |   <prepare statement>
        |   <deallocate prepared statement>
        |   <describe statement>
        |   <execute statement>
        |   <execute immediate statement>
        |   <SQL dynamic data statement>' )]
    function SqlDynamicStatement(): Entry {
        return rule( fn() => oneOf(
            SystemDescriptorStatement(),
            PrepareStatement(),
            DeallocatePreparedStatement(),
            DescribeStatement(),
            ExecuteStatement(),
            ExecuteImmediateStatement(),
            SqlDynamicDataStatement(),
        ) );
    }

    #[Grammar( '<system descriptor statement> ::=
            <allocate descriptor statement>
        |   <deallocate descriptor statement>
        |   <get descriptor statement>
        |   <set descriptor statement>
    ' )]
    function SystemDescriptorStatement(): Entry {
        return rule( fn() => oneOf(
            AllocateDescriptorStatement(),
            DeallocateDescriptorStatement(),
            GetDescriptorStatement(),
            SetDescriptorStatement(),
        ) );
    }

    #[Grammar( '<allocate descriptor statement> ::= 
        ALLOCATE DESCRIPTOR 
        <descriptor name> 
        [   WITH MAX 
            <occurrences> ]' )]
    function AllocateDescriptorStatement(): Entry {
        return rule( fn() => sequence(
            wordSequence( 'ALLOCATE DESCRIPTOR' ),
            DescriptorName(),
            optionalSequence(
                wordSequence( 'WITH MAX' ),
                Occurrences(),
            ),
        ) );
    }

    #[Grammar( '<descriptor name> ::= 
        [ <scope option> ] 
        <simple value specification>' )]
    function DescriptorName(): Entry {
        return rule( fn() => sequence(
            optional( ScopeOption() ),
            SimpleValueSpecification(),
        ) );
    }

    #[Grammar( '<scope option> ::= GLOBAL | LOCAL' )]
    function ScopeOption(): Entry {
        return rule( oneOfWords( 'GLOBAL', 'LOCAL' ) );
    }

    #[Grammar( '<occurrences> ::= <simple value specification>' )]
    function Occurrences(): Entry {
        return rule( fn() => SimpleValueSpecification() );
    }

    #[Grammar( '<deallocate descriptor statement> ::= 
        DEALLOCATE DESCRIPTOR 
        <descriptor name>' )]
    function DeallocateDescriptorStatement(): Entry {
        return rule( fn() => sequence(
            wordSequence( 'DEALLOCATE DESCRIPTOR' ),
            DescriptorName(),
        ) );
    }

    #[Grammar( '<get descriptor statement> ::= 
        GET DESCRIPTOR 
        <descriptor name> 
        <get descriptor information>' )]
    function GetDescriptorStatement(): Entry {
        return rule( fn() => sequence(
            wordSequence( 'GET DESCRIPTOR' ),
            DescriptorName(),
            GetDescriptorInformation(),
        ) );
    }

    #[Grammar( '<get descriptor information> ::=
            <get count>
        |   VALUE 
            <item number> 
            <get item information> [ { <comma> <get item information> }... ]' )]
    function GetDescriptorInformation(): Entry {
        return rule( fn() => oneOf(
            sequence(
                word( 'VALUE' ),
                ItemNumber(),
                commaList( GetItemInformation() ),
            ),
            GetCount(),
        ) );
    }

    #[Grammar( '<get count> ::=
            <simple target specification 1> 
            <equals operator> 
            COUNT' )]
    function GetCount(): Entry {
        return rule( fn() => sequence(
            SimpleTargetSpecification1(),
            EqualsOperator(),
            word( 'COUNT' ),
        ) );
    }

    #[Grammar( '<simple target specification 1> ::= <simple target specification>' )]
    function SimpleTargetSpecification1(): Entry {
        return rule( fn() => SimpleTargetSpecification() );
    }

    #[Grammar( '<simple target specification> ::= 
            <parameter name> 
        |   <embedded variable name>' )]
    function SimpleTargetSpecification(): Entry {
        return rule( fn() => oneOf(
            ParameterName(),
            EmbeddedVariableName(),
        ) );
    }

    #[Grammar( '<item number> ::= <simple value specification>' )]
    function ItemNumber(): Entry {
        return rule( fn() => SimpleValueSpecification() );
    }

    #[Grammar( '<get item information> ::= 
        <simple target specification 2> 
        <equals operator> 
        <descriptor item name>' )]
    function GetItemInformation(): Entry {
        return rule( fn() => sequence(
            SimpleTargetSpecification2(),
            EqualsOperator(),
            DescriptorItemName(),
        ) );
    }

    #[Grammar( '<simple target specification 2> ::= <simple target specification>' )]
    function SimpleTargetSpecification2(): Entry {
        return rule( fn() => SimpleTargetSpecification() );
    }

    const DESCRIPTOR_ITEM_NAME = <<<WORDS
        TYPE
        | LENGTH
        | OCTET_LENGTH
        | RETURNED_LENGTH
        | RETURNED_OCTET_LENGTH
        | PRECISION
        | SCALE
        | DATETIME_INTERVAL_CODE
        | DATETIME_INTERVAL_PRECISION
        | NULLABLE
        | INDICATOR
        | DATA
        | NAME
        | UNNAMED
        | COLLATION_CATALOG
        | COLLATION_SCHEMA
        | COLLATION_NAME
        | CHARACTER_SET_CATALOG
        | CHARACTER_SET_SCHEMA
        | CHARACTER_SET_NAME
    WORDS;
    #[Grammar( '<descriptor item name> ::=' .
               DESCRIPTOR_ITEM_NAME )]
    function DescriptorItemName(): Entry {
        return rule( fn() => oneOfWords( ...splitWords( DESCRIPTOR_ITEM_NAME ) ) );
    }

    #[Grammar( '<set descriptor statement> ::= 
        SET DESCRIPTOR 
        <descriptor name> 
        <set descriptor information>' )]
    function SetDescriptorStatement(): Entry {
        return rule( fn() => sequence(
            wordSequence( 'SET DESCRIPTOR' ),
            DescriptorName(),
            SetDescriptorInformation(),
        ) );
    }

    #[Grammar( '<set descriptor information> ::=
            <set count>
        |   VALUE 
            <item number> 
            <set item information> [ { <comma> <set item information> }... ]' )]
    function SetDescriptorInformation(): Entry {
        return rule( fn() => oneOf(
            sequence(
                word( 'VALUE' ),
                ItemNumber(),
                commaList( SetItemInformation() ),
            ),
            SetCount(),
        ) );
    }

    #[Grammar( '<set count> ::= 
        COUNT 
        <equals operator> 
        <simple value specification 1>' )]
    function SetCount(): Entry {
        return rule( fn() => sequence(
            word( 'COUNT' ),
            EqualsOperator(),
            SimpleValueSpecification1(),
        ) );
    }

    #[Grammar( '<simple value specification 1> ::= <simple value specification>' )]
    function SimpleValueSpecification1(): Entry {
        return rule( fn() => SimpleValueSpecification() );
    }

    #[Grammar( '<set item information> ::= 
        <descriptor item name> 
        <equals operator> 
        <simple value specification 2>' )]
    function SetItemInformation(): Entry {
        return rule( fn() => sequence(
            DescriptorItemName(),
            EqualsOperator(),
            SimpleValueSpecification2(),
        ) );
    }

    #[Grammar( '<simple value specification 2> ::= <simple value specification>' )]
    function SimpleValueSpecification2(): Entry {
        return rule( fn() => SimpleValueSpecification() );
    }

    #[Grammar( '<prepare statement> ::= 
        PREPARE 
        <SQL statement name> 
        FROM 
        <SQL statement variable>' )]
    function PrepareStatement(): Entry {
        return rule( fn() => sequence(
            word( 'PREPARE' ),
            SqlStatementName(),
            word( 'FROM' ),
            SqlStatementVariable(),
        ) );
    }

    #[Grammar( '<SQL statement name> ::=
            <statement name> 
        |   <extended statement name>' )]
    function SqlStatementName(): Entry {
        return rule( fn() => oneOf(
            SqlStatementName(),
            ExtendedSqlStatementName(),
        ) );
    }

    #[Grammar( '<extended statement name> ::= 
        [ <scope option> ] 
        <simple value specification>' )]
    function ExtendedSqlStatementName(): Entry {
        return rule( fn() => sequence(
            optional( ScopeOption() ),
            SimpleValueSpecification(),
        ) );
    }

    #[Grammar( '<SQL statement variable> ::= <simple value specification>' )]
    function SqlStatementVariable(): Entry {
        return rule( fn() => SimpleValueSpecification() );
    }


    #[Grammar( '<deallocate prepared statement> ::= 
        DEALLOCATE PREPARE 
        <SQL statement name>' )]
    function DeallocatePreparedStatement(): Entry {
        return rule( fn() => sequence(
            wordSequence( 'DEALLOCATE PREPARE' ),
            SqlStatementName(),
        ) );
    }

    #[Grammar( '<describe statement> ::= 
            <describe input statement> 
        |   <describe output statement>' )]
    function DescribeStatement(): Entry {
        return rule( fn() => oneOf(
            DescribeInputStatement(),
            DescribeOutputStatement(),
        ) );
    }

    #[Grammar( '<describe input statement> ::= 
        DESCRIBE INPUT 
        <SQL statement name> 
        <using descriptor>' )]
    function DescribeInputStatement(): Entry {
        return rule( fn() => sequence(
            wordSequence( 'DESCRIBE INPUT' ),
            SqlStatementName(),
            UsingDescriptor(),
        ) );
    }

    #[Grammar( '<using descriptor> ::= 
        { USING | INTO } 
        SQL DESCRIPTOR 
        <descriptor name>' )]
    function UsingDescriptor(): Entry {
        return rule( fn() => sequence(
            oneOfWords( 'USING', 'INTO' ),
            wordSequence( 'SQL DESCRIPTOR' ),
            DescriptorName(),
        ) );
    }

    #[Grammar( '<describe output statement> ::= 
        DESCRIBE 
        [ OUTPUT ] 
        <SQL statement name> 
        <using descriptor>' )]
    function DescribeOutputStatement(): Entry {
        return rule( fn() => sequence(
            word( 'DESCRIBE' ),
            optionalWord( 'OUTPUT' ),
            SqlStatementName(),
            UsingDescriptor(),
        ) );
    }

    #[Grammar( '<execute statement> ::= 
        EXECUTE 
        <SQL statement name> 
        [ <result using clause> ] 
        [ <parameter using clause>]' )]
    function ExecuteStatement(): Entry {
        return rule( fn() => sequence(
            word( 'EXECUTE' ),
            SqlStatementName(),
            optional( ResultUsingClause() ),
            optional( ParameterUsingClause() ),

        ) );
    }

    #[Grammar( '<result using clause> ::= <using clause>' )]
    function ResultUsingClause(): Entry {
        return rule( fn() => UsingClause() );
    }

    #[Grammar( '<parameter using clause> ::= <using clause>' )]
    function ParameterUsingClause(): Entry {
        return rule( fn() => UsingClause() );
    }

    #[Grammar( '<using clause> ::=
            <using arguments> 
        |   <using descriptor>' )]
    function UsingClause(): Entry {
        return rule( fn() => oneOf(
            UsingArguments(),
            UsingDescriptor(),
        ) );
    }

    #[Grammar( '<using arguments> ::= 
        { USING | INTO } 
        <argument>  [ { <comma> <argument> }... ]' )]
    function UsingArguments(): Entry {
        return rule( fn() => sequence(
            oneOfWords( 'USING', 'INTO' ),
            commaList( Argument() ),
        ) );
    }

    #[Grammar( '<execute immediate statement> ::= 
        EXECUTE IMMEDIATE 
        <SQL statement variable>' )]
    function ExecuteImmediateStatement(): Entry {
        return rule( fn() => sequence(
            wordSequence( 'EXECUTE IMMEDIATE' ),
            SqlStatementVariable(),
        ) );
    }

    #[Grammar( '<argument> ::= <target specification>' )]
    function Argument(): Entry {
        return rule( fn() => TargetSpecification() );
    }

    #[Grammar( '<SQL dynamic data statement> ::=
            <allocate cursor statement>
        |   <dynamic open statement>
        |   <dynamic close statement>
        |   <dynamic fetch statement>
        |   <dynamic delete statement: positioned>
        |   <dynamic update statement: positioned>
    ' )]
    function SqlDynamicDataStatement(): Entry {
        return rule( fn() => oneOf(
            AllocateCursorStatement(),
            DynamicOpenStatement(),
            DynamicCloseStatement(),
            DynamicFetchStatement(),
            DynamicDeleteStatementPositioned(),
            DynamicUpdateStatementPositioned(),
        ) );
    }

    #[Grammar( '<allocate cursor statement> ::= 
        ALLOCATE 
        <extended cursor name> 
        [ INSENSITIVE ] 
        [ SCROLL ] 
        CURSOR FOR 
        <extended statement name>' )]
    function AllocateCursorStatement(): Entry {
        return rule( fn() => sequence(
            word( 'ALLOCATE' ),
            ExtendedCursorName(),
            optionalWord( 'INSENSITIVE' ),
            optionalWord( 'SCROLL' ),
            wordSequence( 'CURSOR FOR' ),
            ExtendedStatementName(),
        ) );
    }

    #[Grammar( '<extended cursor name> ::= 
        [ <scope option> ] 
        <simple value specification>' )]
    function ExtendedCursorName(): Entry {
        return rule( fn() => sequence(
            optional( ScopeOption() ),
            SimpleValueSpecification()
        ) );
    }

    #[Grammar( '<extended statement name> ::= 
        [ <scope option> ]
        <simple value specification>' )]
    function ExtendedStatementName(): Entry {
        return rule( fn() => sequence(
            optional( ScopeOption() ),
            SimpleValueSpecification()
        ) );
    }

    #[Grammar( '<dynamic open statement> ::= 
        OPEN 
        <dynamic cursor name> 
        [ <using clause> ]' )]
    function DynamicOpenStatement(): Entry {
        return rule( fn() => sequence(
            word( 'OPEN' ),
            DynamicCursorName(),
            optional( UsingClause() ),
        ) );
    }

    #[Grammar( '<dynamic cursor name> ::=
            <cursor name> 
        |   <extended cursor name>' )]
    function DynamicCursorName(): Entry {
        return rule( fn() => oneOf(
            CursorName(),
            ExtendedCursorName(),
        ) );
    }

    #[Grammar( '<dynamic close statement> ::= 
        CLOSE 
        <dynamic cursor name>' )]
    function DynamicCloseStatement(): Entry {
        return rule( fn() => sequence(
            word( 'CLOSE' ),
            DynamicCursorName(),
        ) );
    }

    #[Grammar( '<dynamic fetch statement> ::= 
        FETCH 
        [ 
            [ <fetch orientation> ] 
            FROM 
        ] 
        <dynamic cursor name>' )]
    function DynamicFetchStatement(): Entry {
        return rule( fn() => sequence(
            word( 'FETCH' ),
            optionalSequence(
                optional( FetchOrientation() ),
                word( 'FROM' ),
            ),
            DynamicCursorName(),
        ) );
    }

    #[Grammar( '<dynamic delete statement: positioned> ::=
        DELETE FROM 
        <table name> 
        WHERE CURRENT OF
        <dynamic cursor name>' )]
    function DynamicDeleteStatementPositioned(): Entry {
        return rule( fn() => sequence(
            wordSequence( 'DELETE FROM' ),
            TableName(),
            wordSequence( 'WHERE CURRENT OF' ),
            DynamicCursorName(),
        ) );
    }

    #[Grammar( '<dynamic update statement: positioned> ::= 
        UPDATE 
        <table name> 
        SET 
        <set clause> [ { <comma> <set clause> }... ] 
        WHERE CURRENT OF 
        <dynamic cursor name>' )]
    function DynamicUpdateStatementPositioned(): Entry {
        return rule( fn() => sequence(
            word( 'UPDATE' ),
            TableName(),
            word( 'SET' ),
            commaList( SetClause() ),
            wordSequence( 'WHERE CURRENT OF' ),
            DynamicCursorName(),
        ) );
    }

    #[Grammar( '<SQL diagnostics statement> ::= <get diagnostics statement>' )]
    function SqlDiagnosticsStatement(): Entry {
        return rule( fn() => GetDiagnosticsStatement() );
    }

    #[Grammar( '<get diagnostics statement> ::= 
        GET DIAGNOSTICS 
        <sql diagnostics information>' )]
    function GetDiagnosticsStatement(): Entry {
        return rule( fn() => sequence(
            wordSequence( 'GET DIAGNOSTICS' ),
            SqlDiagnosticsInformation(),
        ) );
    }

    #[Grammar( '<sql diagnostics information> ::= 
            <statement information> 
        |   <condition information>' )]
    function SqlDiagnosticsInformation(): Entry {
        return rule( fn() => oneOf(
            StatementInformation(),
            ConditionInformation(),
        ) );
    }

    #[Grammar( '<statement information> ::= 
        <statement information item> [ { <comma> <statement information item> }... ]' )]
    function StatementInformation(): Entry {
        return rule( fn() => commaList( StatementInformationItem() ) );
    }

    #[Grammar( '<statement information item> ::= 
        <simple target specification> 
        <equals operator> 
        <statement information item name>' )]
    function StatementInformationItem(): Entry {
        return rule( fn() => sequence(
            SimpleTargetSpecification(),
            EqualsOperator(),
            StatementInformationItemName(),
        ) );
    }

    const STATEMENT_INFORMATION_ITEM_NAME = <<<WORDS
        NUMBER 
        | MORE 
        | COMMAND_FUNCTION 
        | DYNAMIC_FUNCTION 
        | ROW_COUNT
    WORDS;
    #[Grammar( '<statement information item name> ::= ' .
               STATEMENT_INFORMATION_ITEM_NAME )]
    function StatementInformationItemName(): Entry {
        return rule( fn() => oneOfWords(
            ...splitWords( STATEMENT_INFORMATION_ITEM_NAME )
        ) );
    }

    const CONDITION_INFORMATION_ITEM_NAME = <<<WORDS
        CONDITION_NUMBER
        | RETURNED_SQLSTATE
        | CLASS_ORIGIN
        | SUBCLASS_ORIGIN
        | SERVER_NAME
        | CONNECTION_NAME
        | CONSTRAINT_CATALOG
        | CONSTRAINT_SCHEMA
        | CONSTRAINT_NAME
        | CATALOG_NAME
        | SCHEMA_NAME
        | TABLE_NAME
        | COLUMN_NAME
        | CURSOR_NAME
        | MESSAGE_TEXT
        | MESSAGE_LENGTH
        | MESSAGE_OCTET_LENGTH
    WORDS;
    #[Grammar( '<condition information item name> ::= ' .
               CONDITION_INFORMATION_ITEM_NAME )]
    function ConditionInformationItemName(): Entry {
        return rule( fn() => oneOfWords(
            ...splitWords( CONDITION_INFORMATION_ITEM_NAME )
        ) );
    }

    #[Grammar( '<condition information> ::= 
        EXCEPTION 
        <condition number> 
        <condition information item> [ { <comma> <condition information item> }...]' )]
    function ConditionInformation(): Entry {
        return rule( fn() => sequence(
            word( 'EXCEPTION' ),
            ConditionNumber(),
            commaList( ConditionInformationItem() ),
        ) );
    }

    #[Grammar( '<condition number> ::= <simple value specification>' )]
    function ConditionNumber(): Entry {
        return rule( fn() => SimpleValueSpecification() );
    }

    #[Grammar( '<condition information item> ::= 
        <simple target specification> 
        <equals operator> 
        <condition information item name>' )]
    function ConditionInformationItem(): Entry {
        return rule( fn() => sequence(
            SimpleTargetSpecification(),
            EqualsOperator(),
            ConditionInformationItemName(),
        ) );
    }

    #[Grammar( '<embedded SQL host program> ::=
            <embedded SQL Ada program>
        |   <embedded SQL C program>
        |   <embedded SQL Cobol program>
        |   <embedded SQL Fortran program>
        |   <embedded SQL MUMPS program>
        |   <embedded SQL Pascal program>
        |   <embedded SQL PL/I program>
        ' )]
    function EmbeddedSqlHostProgram(): Entry {
        return rule( fn() => oneOf(
            EmbeddedSqlAdaProgram(),
            EmbeddedSqlCProgram(),
            EmbeddedSqlCobolProgram(),
            EmbeddedSqlFortranProgram(),
            EmbeddedSqlMumpsProgram(),
            EmbeddedSqlPascalProgram(),
            EmbeddedSqlPLIProgram(),
        ) );
    }

    #[Grammar( '<embedded SQL Ada program> ::= !! See the syntax rules' )]
    function EmbeddedSqlAdaProgram(): Entry {
        return rule( fn() => customParser() );
    }

    #[Grammar( '<embedded SQL C program> ::= !! See the syntax rules' )]
    function EmbeddedSqlCProgram(): Entry {
        return rule( fn() => customParser() );
    }

    #[Grammar( '<embedded SQL Cobol program> ::= !! See the syntax rules' )]
    function EmbeddedSqlCobolProgram(): Entry {
        return rule( fn() => customParser() );
    }

    #[Grammar( '<embedded SQL Fortran program> ::= !! See the syntax rules' )]
    function EmbeddedSqlFortranProgram(): Entry {
        return rule( fn() => customParser() );
    }

    #[Grammar( '<embedded SQL MUMPS program> ::= !! See the syntax rules' )]
    function EmbeddedSqlMumpsProgram(): Entry {
        return rule( fn() => customParser() );
    }

    #[Grammar( '<embedded SQL Pascal program> ::= !! See the syntax rules' )]
    function EmbeddedSqlPascalProgram(): Entry {
        return rule( fn() => customParser() );
    }

    #[Grammar( '<embedded SQL PL/I program> ::= !! See the syntax rules' )]
    function EmbeddedSqlPLIProgram(): Entry {
        return rule( fn() => customParser() );
    }

    #[Grammar( '<embedded SQL declare section> ::=
            <embedded SQL begin declare>
            [ <embedded character set declaration> ]
            [ <host variable definition> ... ]
            <embedded SQL end declare>
        |   <embedded SQL MUMPS declare>' )]
    function EmbeddedSqlDeclareSection(): Entry {
        return rule( fn() => oneOf(
            sequence(
                EmbeddedSqlBeginDeclare(),
                zeroOrOne( EmbeddedCharacterSetDeclaration() ),
                zeroOrMore( HostVariableDefinition() ),
                EmbeddedSqlEndDeclare(),
            ),
            EmbeddedSqlMumpsDeclare(),
        ) );
    }

    #[Grammar( '<embedded SQL begin declare> ::= 
        <SQL prefix> 
        BEGIN DECLARE SECTION 
        [ <SQL terminator> ]' )]
    function EmbeddedSqlBeginDeclare(): Entry {
        return rule( fn() => sequence(
            SqlPrefix(),
            optional( wordSequence( 'BEGIN DECLARE SECTION' ) ),
            SqlTerminator(),
        ) );
    }

    #[Grammar( '<embedded character set declaration> ::= 
        SQL NAMES ARE 
        <character set specification>' )]
    function EmbeddedCharacterSetDeclaration(): Entry {
        return rule( fn() => sequence(
            wordSequence( 'SQL NAMES ARE' ),
            CharacterSetSpecification(),
        ) );
    }

    #[Grammar( '<SQL prefix> ::= 
            EXEC SQL 
        |   <ampersand>SQL<left paren>' )]
    function SqlPrefix(): Entry {
        return rule( fn() => oneOf(
            wordSequence( 'EXEC SQL' ),
            sequence(
                Ampersand(),
                word( 'SQL' ),
                LeftParen(),
            ),
        ) );
    }

    #[Grammar( '<SQL terminator> ::= 
            END-EXEC 
        |   <semicolon> 
        |   <right paren>' )]
    function SqlTerminator(): Entry {
        return rule( fn() => oneOf(
            word( 'END-EXEC' ),
            SemiColon(),
            RightParen(),
        ) );
    }

    #[Grammar( '<embedded SQL end declare> ::= 
        <SQL prefix> 
        END DECLARE SECTION 
        [ <SQL terminator> ]' )]
    function EmbeddedSqlEndDeclare(): Entry {
        return rule( fn() => sequence(
            SqlPrefix(),
            wordSequence( 'END DECLARE SECTION' ),
            optional( SqlTerminator() ),
        ) );
    }

    #[Grammar( '<embedded SQL MUMPS declare> ::=
        <SQL prefix>
        BEGIN DECLARE SECTION
        [ <embedded character set declaration> ]
        [ <host variable definition>... ]
        END DECLARE SECTION
        <SQL terminator>' )]
    function EmbeddedSqlMumpsDeclare(): Entry {
        return rule( fn() => sequence(
            SqlPrefix(),
            wordSequence( 'BEGIN DECLARE SECTION' ),
            zeroOrOne( EmbeddedCharacterSetDeclaration() ),
            zeroOrMore( HostVariableDefinition() ),
            wordSequence( 'END DECLARE SECTION' ),
            SqlTerminator(),
        ) );
    }

    #[Grammar( '<embedded SQL statement> ::= 
        <SQL prefix> 
        <statement or declaration> 
        [ <SQL terminator> ]' )]
    function EmbeddedSqlStatement(): Entry {
        return rule( fn() => sequence(
            SqlPrefix(),
            StatementOrDeclaration(),
            SqlTerminator(),
        ) );
    }

    #[Grammar( '<statement or declaration> ::=
            <declare cursor>
        |   <dynamic declare cursor>
        |   <temporary table declaration>
        |   <embedded exception declaration>
        |   <SQL procedure statement>' )]
    function StatementOrDeclaration(): Entry {
        return rule( fn() => oneOf(
            DeclareCursor(),
            DynamicDeclareCursor(),
            TemporaryTableDeclaration(),
            EmbeddedExceptionDeclaration(),
            SqlProcedureStatement(),
        ) );
    }

    #[Grammar( '<embedded exception declaration> ::= 
        WHENEVER 
        <condition> 
        <condition action>' )]
    function EmbeddedExceptionDeclaration(): Entry {
        return rule( fn() => sequence(
            word( 'WHENEVER' ),
            Condition(),
            ConditionAction(),
        ) );
    }

    #[Grammar( '<condition> ::= 
            SQLERROR 
        |   NOT FOUND' )]
    function Condition(): Entry {
        return rule( fn() => oneOf(
            word( 'SQLERROR' ),
            wordSequence( 'NOT FOUND' ),
        ) );
    }

    #[Grammar( '<condition action> ::= 
            CONTINUE 
        |   <go to>' )]
    function ConditionAction(): Entry {
        return rule( fn() => oneOf(
            word( 'CONTINUE' ),
            _GoTo(),
        ) );
    }

    #[Grammar( '<go to> ::= 
        { GOTO | GO TO } 
        <goto target>' )]
    function _GoTo(): Entry {
        return rule( fn() => sequence(
            oneOf(
                word( 'GOTO' ),
                wordSequence( 'GO TO' ),
            ),
            GotoTarget(),
        ) );
    }

    #[Grammar( '<goto target> ::=
            <host label identifier>
        |   <unsigned integer>
        |   <host PL/I label variable>' )]
    function GotoTarget(): Entry {
        return rule( fn() => oneOf(
            HostLabelIdentifier(),
            UnsignedInteger(),
            HostPLILabelVariable()
        ) );
    }

    #[Grammar( '<host label identifier> ::= !! See the syntax rules' )]
    function HostLabelIdentifier(): Entry {
        return rule( fn() => customParser() );
    }

    #[Grammar( '<host PL/I label variable> ::= !! See the syntax rules' )]
    function HostPLILabelVariable(): Entry {
        return rule( fn() => customParser() );
    }

    #[Grammar( '<preparable statement> ::=
            <preparable SQL data statement>
        |   <preparable SQL schema statement>
        |   <preparable SQL transaction statement>
        |   <preparable SQL session statement>
        |   <preparable SQL implementation-defined statement>' )]
    function PreparableStatement(): Entry {
        return rule( fn() => oneOf(
            PreparableSqlDataStatement(),
            PreparableSqlSchemaStatement(),
            PreparableSqlTransactionStatement(),
            PreparableSqlSessionStatement(),
            PreparableSqlImplementationDefinedStatement(),
        ) );
    }

    #[Grammar( '<preparable SQL data statement> ::=
            <delete statement: searched>
        |   <dynamic single row select statement>
        |   <insert statement>
        |   <dynamic select statement>
        |   <update statement: searched>
        |   <preparable dynamic delete statement: positioned>
        |   <preparable dynamic update statement: positioned>' )]
    function PreparableSqlDataStatement(): Entry {
        return rule( fn() => oneOf(
            DeleteStatementSearched(),
            DynamicSingleRowSelectStatement(),
            InsertStatement(),
            DynamicSelectStatement(),
            UpdateStatementSearched(),
            PreparableDynamicDeleteStatementPositioned(),
            PreparableDynamicUpdateStatementPositioned(),
        ) );
    }

    #[Grammar( '<dynamic single row select statement> ::= <query specification>' )]
    function DynamicSingleRowSelectStatement(): Entry {
        return rule( fn() => QuerySpecification() );
    }

    #[Grammar( '<dynamic select statement> ::= <cursor specification>' )]
    function DynamicSelectStatement(): Entry {
        return rule( fn() => CursorSpecification() );
    }

    #[Grammar( '<preparable dynamic delete statement: positioned> ::=
        DELETE 
        [   FROM 
            <table name> ] 
        WHERE CURRENT OF 
        <cursor name>' )]
    function PreparableDynamicDeleteStatementPositioned(): Entry {
        return rule( fn() => sequence(
            word( 'DELETE' ),
            optionalSequence(
                word( 'FROM' ),
                TableName(),
            ),
            wordSequence( 'WHERE CURRENT OF' ),
            CursorName(),
        ) );
    }

    #[Grammar( '<preparable dynamic update statement: positioned> ::=
        UPDATE 
        [ <table name> ] 
        SET 
        <set clause> 
        WHERE CURRENT OF 
        <cursor name>' )]
    function PreparableDynamicUpdateStatementPositioned(): Entry {
        return rule( fn() => sequence(
            word( 'UPDATE' ),
            optional( TableName() ),
            word( 'SET' ),
            SetClause(),
            wordSequence( 'WHERE CURRENT OF' ),
            CursorName(),
        ) );
    }

    #[Grammar( '<preparable SQL schema statement> ::= <SQL schema statement>' )]
    function PreparableSqlSchemaStatement(): Entry {
        return rule( fn() => SqlSchemaStatement() );
    }

    #[Grammar( '<preparable SQL transaction statement> ::= <SQL transaction statement>' )]
    function PreparableSqlTransactionStatement(): Entry {
        return rule( fn() => SqlTransactionStatement() );
    }

    #[Grammar( '<preparable SQL session statement> ::= <SQL session statement>' )]
    function PreparableSqlSessionStatement(): Entry {
        return rule( fn() => SqlSessionStatement() );
    }

    #[Grammar( '<preparable SQL implementation-defined statement> ::= !! See the syntax rules' )]
    function PreparableSqlImplementationDefinedStatement(): Entry {
        return rule( fn() => customParser() );
    }

    #[Grammar( '<direct SQL statement> ::=
            <direct SQL data statement>
        |   <SQL schema statement>
        |   <SQL transaction statement>
        |   <SQL connection statement>
        |   <SQL session statement>
        |   <direct implementation-defined statement>' )]
    function DirectSqlStatement(): Entry {
        return rule( fn() => oneOf(
            DirectSqlDataStatement(),
            SqlSchemaStatement(),
            SqlTransactionStatement(),
            SqlConnectionStatement(),
            SqlSessionStatement(),
            DirectImplementationDefinedStatement(),
        ) );
    }

    #[Grammar( '<direct SQL data statement> ::=
            <delete statement: searched>
        |   <direct select statement: multiple rows>
        |   <insert statement>
        |   <update statement: searched>
        |   <temporary table declaration>' )]
    function DirectSqlDataStatement(): Entry {
        return rule( fn() => oneOf(
            DeleteStatementSearched(),
            DirectSelectStatementMultipleRows(),
            InsertStatement(),
            UpdateStatementSearched(),
            TemporaryTableDeclaration(),
        ) );
    }

    #[Grammar( '<direct select statement: multiple rows> ::= 
        <query expression> 
        [ <order by clause> ]' )]
    function DirectSelectStatementMultipleRows(): Entry {
        return rule( fn() => sequence(
            QueryExpression(),
            optional( OrderByClause() ),
        ) );
    }

    #[Grammar( '<direct implementation-defined statement> ::=  !! See the syntax rules' )]
    function DirectImplementationDefinedStatement(): Entry {
        return rule( fn() => customParser() );
    }


    #[Grammar( '<host variable definition> ::=
            <Ada variable definition>
        |   <C variable definition>
        |   <Cobol variable definition>
        |   <Fortran variable definition>
        |   <MUMPS variable definition>
        |   <Pascal variable definition>
        |   <PL/I variable definition>' )]
    function HostVariableDefinition(): Entry {
        return rule( fn() => oneOf(
            AdaVariableDefinition(),
            CVariableDefinition(),
            CobolVariableDefinition(),
            FortranVariableDefinition(),
            MumpsVariableDefinition(),
            PascalVariableDefinition(),
            PLIVariableDefinition(),
        ) );
    }

    #[Grammar( '<Ada variable definition> ::=
        <Ada host identifier> [ { <comma> <Ada host identifier> }... ] 
        <colon>
        <Ada type specification> 
        [ <Ada initial value> ]' )]
    function AdaVariableDefinition(): Entry {
        return rule( fn() => sequence(
            commaList( AdaHostIdentifier() ),
            Colon(),
            AdaTypeSpecification(),
            optional( AdaInitialValue() ),
        ) );
    }

    #[Grammar( '<Ada type specification> ::= 
            <Ada qualified type specification> 
        |   <Ada unqualified type specification>' )]
    function AdaTypeSpecification(): Entry {
        return rule( fn() => oneOf(
            AdaQualifiedTypeSpecification(),
            AdaUnqualifiedTypeSpecification(),
        ) );
    }

    #[Grammar( '<Ada initial value> ::= 
        <Ada assignment operator> 
        <character representation>' )]
    function AdaInitialValue(): Entry {
        return rule( fn() => sequence(
            AdaAssignmentOperator(),
            CharacterRepresentation(),
        ) );
    }

    #[Grammar( '<Ada assignment operator> ::= 
        <colon>
        <equals operator>' )]
    function AdaAssignmentOperator(): Entry {
        return rule( fn() => sequence(
            Colon(),
            EqualsOperator(),
        ) );
    }

    const ADA_QUALIFIED_TYPE_SPECIFICATION_INDEPENDENT_WORDS = <<<WORDS
          SQL_STANDARD.SMALLINT
        | SQL_STANDARD.INT
        | SQL_STANDARD.REAL
        | SQL_STANDARD.DOUBLE_PRECISION
        | SQL_STANDARD.SQLCODE_TYPE
        | SQL_STANDARD.SQLSTATE_TYPE
        | SQL_STANDARD.INDICATOR_TYPE
    WORDS;
    #[Grammar( '<Ada qualified type specification> ::= ' .
               ADA_QUALIFIED_TYPE_SPECIFICATION_INDEPENDENT_WORDS . '
        |   SQL_STANDARD.CHAR [ CHARACTER SET [ IS ] <character set specification> ] 
            <left paren> 
            1 
            <double period> 
            <length> 
            <right paren>
        |   SQL_STANDARD.BIT 
            <left paren> 
            1 
            <double period> 
            <length> 
            <right paren>' )]
    function AdaQualifiedTypeSpecification(): Entry {
        $getParams = function (): Entry {
            return rule( fn() => sequence(
                LeftParen(),
                char( '1' ),
                DoublePeriod(),
                Length(),
                RightParen(),
            ) );
        };
        return rule( fn() => oneOf(
            oneOfWords( ...splitWords(
                    ADA_QUALIFIED_TYPE_SPECIFICATION_INDEPENDENT_WORDS )
            ),
            oneOf(
                sequence(
                    word( 'SQL_STANDARD.BIT' ),
                    $getParams(),
                ),
                sequence(
                    word( 'SQL_STANDARD.CHAR' ),
                    optionalSequence(
                        wordSequence( 'CHARACTER SET' ),
                        optionalWord( 'IS' ),
                        CharacterSetSpecification(),
                    ),
                    $getParams(),
                ),
            ),
        ) );
    }
    const ADA_UNQUALIFIED_TYPE_SPECIFICATION_INDEPENDENT_WORDS = <<<WORDS
          SMALLINT
        | INT
        | REAL
        | DOUBLE_PRECISION
        | SQLCODE_TYPE
        | SQLSTATE_TYPE
        | INDICATOR_TYPE
    WORDS;
    #[Grammar( '<Ada unqualified type specification> ::=
            CHAR 
            <left paren> 
            1 
            <double period> 
            <length> 
            <right paren>
        | 
            BIT 
            <left paren> 
            1 
            <double period> 
            <length> 
            <right paren>' .
               ADA_UNQUALIFIED_TYPE_SPECIFICATION_INDEPENDENT_WORDS )]
    function AdaUnqualifiedTypeSpecification(): Entry {
        return rule( fn() => oneOf(
            oneOfWords( ...splitWords(
                    ADA_UNQUALIFIED_TYPE_SPECIFICATION_INDEPENDENT_WORDS )
            ),
            sequence(
                oneOfWords( 'CHAR', 'BIT' ),
                LeftParen(),
                char( '1' ),
                DoublePeriod(),
                Length(),
                RightParen(),
            ),
        ) );
    }

    #[Grammar( '<C variable definition> ::= 
        [ <C storage class> ] 
        [ <C class modifier> ] 
        <C variable specification> 
        <semicolon>' )]
    function CVariableDefinition(): Entry {
        return rule( fn() => sequence(
            optional( CStorageClass() ),
            optional( CClassModifier() ),
            CVariableSpecification(),
            SemiColon(),
        ) );
    }

    #[Grammar( '<C storage class> ::= auto | extern | static' )]
    function CStorageClass(): Entry {
        return rule( fn() => caseSensitive( oneOfWords( 'auto', 'extern', 'static' ) ) );
    }

    #[Grammar( '<C class modifier> ::= const | volatile' )]
    function CClassModifier(): Entry {
        return rule( fn() => caseSensitive( oneOfWords( 'const', 'volatile' ) ) );
    }

    #[Grammar( '<C variable specification> ::= 
            <C numeric variable>  
        |   <C character variable>  
        |   <C derived variable>' )]
    function CVariableSpecification(): Entry {
        return rule( fn() => sequence(
            CNumericVariable(),
            CCharacterVariable(),
            CDerivedVariable(),
        ) );
    }

    #[Grammar( '<C numeric variable> ::=   
        { long | short | float | double } 
        <C host identifier> 
        [ <C initial value> ]    
            [ { <comma> <C host identifier> [ <C initial value> ] }... ]' )]
    function CNumericVariable(): Entry {
        return rule( fn() => sequence(
            caseSensitive( oneOfWords( 'long', 'short', 'float', 'double' ) ),
            commaList( sequence(
                CHostIdentifier(),
                optional( CInitialValue() ),
            ) ),
        ) );
    }

    #[Grammar( '<C initial value> ::= 
        <equals operator> 
        <character representation>' )]
    function CInitialValue(): Entry {
        return rule( fn() => sequence(
            EqualsOperator(),
            CharacterRepresentation(),
        ) );
    }

    #[Grammar( '<C character variable> ::= 
        char 
        [   CHARACTER SET 
            [ IS ] 
            <character set specification> ] 
        <C host identifier list>' )]
    function CCharacterVariable(): Entry {
        return rule( fn() => sequence(
            caseSensitive( word( 'char' ) ),
            optionalSequence(
                wordSequence( 'CHARACTER SET' ),
                optionalWord( 'IS' ),
                CharacterSetSpecification(),
            ),
            commaList( sequence(
                CHostIdentifier(),
                CArraySpecification(),
                optional( CInitialValue() ),
            ) ),
        ) );
    }

    #[Grammar( '<C array specification> ::= 
        <left bracket> 
        <length> 
        <right bracket>' )]
    function CArraySpecification(): Entry {
        return rule( fn() => sequence(
            LeftBracket(),
            Length(),
            RightBracket(),
        ) );
    }

    #[Grammar( '<C derived variable> ::=  
            <C VARCHAR variable> 
        |   <C bit variable>' )]
    function CDerivedVariable(): Entry {
        return rule( fn() => oneOf(
            CVarCharVariable(),
            CBitVariable(),
        ) );
    }

    #[Grammar( '<C VARCHAR variable> ::=   
        VARCHAR 
        [   CHARACTER SET 
            [ IS ] 
            <character set specification> ]
        <C host identifier> 
        <C array specification> 
        [ <C initial value> ]
            [ { <comma> <C host identifier> <C array specification> [ <C initial value> ] }... ]' )]
    function CVarcharVariable(): Entry {
        return rule( fn() => sequence(
            word( 'VARCHAR' ),
            optionalSequence(
                wordSequence( 'CHARACTER SET' ),
                optionalWord( 'IS' ),
                CharacterSetSpecification(),
            ),
            commaList( sequence(
                CHostIdentifier(),
                CArraySpecification(),
                optional( CInitialValue() ),
            ) ),
        ) );
    }

    #[Grammar( '<C bit variable> ::= 
        BIT 
        <C host identifier> 
        <C array specification> 
        [ <C initial value> ] 
            [ { <comma> <C host identifier> <C array specification> [ <C initial value> ] }... ]' )]
    function CBitVariable(): Entry {
        return rule( fn() => sequence(
            word( 'BIT' ),
            commaList( sequence(
                CHostIdentifier(),
                CArraySpecification(),
                optional( CInitialValue() ),
            ) ),
        ) );
    }

    #[Grammar( '<Cobol variable definition> ::= ...omitted...' )]
    function CobolVariableDefinition(): Entry {
        return rule( fn() => unsupported() );
    }

    #[Grammar( '<Fortran variable definition> ::= ...omitted...' )]
    function FortranVariableDefinition(): Entry {
        return rule( fn() => unsupported() );
    }

    #[Grammar( '<MUMPS variable definition> ::= ...omitted...' )]
    function MumpsVariableDefinition(): Entry {
        return rule( fn() => unsupported() );
    }

    #[Grammar( '<Pascal variable definition> ::= ...omitted...' )]
    function PascalVariableDefinition(): Entry {
        return rule( fn() => unsupported() );
    }

    #[Grammar( '<PL/I variable definition> ::= ...omitted...' )]
    function PLIVariableDefinition(): Entry {
        return rule( fn() => unsupported() );
    }


    #[Grammar( '<1987> ::= 
            0 
        |   edition1987 
            <left paren> 
            0 
            <right paren>' )]
    function _1987(): Entry {
        return rule( fn() => oneOf(
            char( '0' ),
            sequence(
                word( 'edition1987' ),
                parenEnclosed( char( '0' ) ),
            ),
        ) );
    }

    #[Grammar( '<1989 base> ::= 
            1 
        |   edition1989 
            <left paren> 
            1
            <right paren>' )]
    function _1989Base(): Entry {
        return rule( fn() => oneOf(
            char( '1' ),
            sequence(
                word( 'edition1989' ),
                parenEnclosed( char( '1' ) ),
            ),
        ) );
    }

    #[Grammar( '<1989 package> ::= 
            <integrity no> 
        |   <integrity yes>' )]
    function _1989Package(): Entry {
        return rule( fn() => oneOf(
            IntegrityNo(),
            IntegrityYes(),
        ) );
    }

    #[Grammar( '<1989> ::= 
        <1989 base> 
        <1989 package>' )]
    function _1989(): Entry {
        return rule( fn() => sequence(
            _1989Base(),
            _1989Package(),
        ) );
    }

    #[Grammar( '<1992> ::= 
            2 
        |   edition1992 
            <left paren> 
            2
            <right paren>' )]
    function _1992(): Entry {
        return rule( fn() => oneOf(
            char( '2' ),
            sequence(
                word( 'edition1992' ),
                parenEnclosed( char( '2' ) ),
            ),
        ) );
    }

    #[Grammar( '<arc1> ::= 
            iso 
        |   1 
        |   iso 
            <left paren> 
            1
            <right paren>' )]
    function Arc1(): Entry {
        return rule( fn() => oneOf(
            word( 'iso' ),
            char( '1' ),
            sequence(
                word( 'iso' ),
                parenEnclosed( char( '1' ) ),
            ),
        ) );
    }

    #[Grammar( '<arc2> ::= 
            standard 
        |   0 
        |   standard 
            <left paren> 
            0 
            <right paren>' )]
    function Arc2(): Entry {
        return rule( fn() => oneOf(
            word( 'standard' ),
            char( '0' ),
            sequence(
                word( 'standard' ),
                parenEnclosed( char( '0' ) ),
            ),
        ) );
    }

    #[Grammar( '<arc3> ::= 9075' )]
    function Arc3(): Entry {
        return rule( fn() => word( '9075' ) );
    }

    #[Grammar( '<high> ::= 
            2 
        |   High <left paren> 2 <right paren>' )]
    function High(): Entry {
        return rule( fn() => oneOf(
            char( '2' ),
            sequence(
                word( 'High' ),
                parenEnclosed( char( '2' ) ),
            ),
        ) );
    }

    #[Grammar( '<integrity no> ::= 
            0 
        |   IntegrityNo <left paren> 0 <right paren>' )]
    function IntegrityNo(): Entry {
        return rule( fn() => oneOf(
            char( '0' ),
            sequence(
                word( 'IntegrityNo' ),
                parenEnclosed( char( '0' ) ),
            ),
        ) );
    }

    #[Grammar( '<integrity yes> ::= 
            1 
        |   IntegrityYes <left paren> 1 <right paren>' )]
    function IntegrityYes(): Entry {
        return rule( fn() => oneOf(
            char( '1' ),
            sequence(
                word( 'IntegrityYes' ),
                parenEnclosed( char( '1' ) ),
            ),
        ) );
    }

    #[Grammar( '<intermediate> ::= 
            1 
        |   Intermediate <left paren> 1 <right paren>' )]
    function Intermediate(): Entry {
        return rule( fn() => oneOf(
            char( '1' ),
            sequence(
                word( 'Intermediate' ),
                parenEnclosed( char( '1' ) ),
            ),
        ) );
    }

    #[Grammar( '<low> ::= 
            0 
        |   Low <left paren> 0 <right paren>' )]
    function Low(): Entry {
        return rule( fn() => oneOf(
            char( '0' ),
            sequence(
                word( 'Low' ),
                parenEnclosed( char( '0' ) ),
            ),
        ) );
    }

    #[Grammar( '<SQL conformance> ::= 
            <low> 
        |   <intermediate> 
        |   <high>' )]
    function SqlConformance(): Entry {
        return rule( fn() => oneOf(
            Low(),
            Intermediate(),
            High(),
        ) );
    }

    #[Grammar( '<SQL edition> ::= 
            <1987> 
        |   <1989> 
        |   <1992>' )]
    function SqlEdition(): Entry {
        return rule( fn() => oneOf(
            _1987(),
            _1989(),
            _1992(),
        ) );
    }

    #[Grammar( '<SQL object identifier> ::= 
        <SQL provenance> 
        <SQL variant>' )]
    function SqlObjectIdentifier(): Entry {
        return rule( fn() => sequence(
            SqlProvenance(),
            SqlVariant(),
        ) );
    }

    #[Grammar( '<SQL provenance> ::= 
        <arc1> 
        <arc2> 
        <arc3>' )]
    function SqlProvenance(): Entry {
        return rule( fn() => sequence(
            Arc1(),
            Arc2(),
            Arc3(),
        ) );
    }

    #[Grammar( '<SQL variant> ::= 
        <SQL edition> 
        <SQL conformance>' )]
    function SqlVariant(): Entry {
        return rule( fn() => sequence(
            SqlEdition(),
            SqlConformance(),
        ) );
    }

}