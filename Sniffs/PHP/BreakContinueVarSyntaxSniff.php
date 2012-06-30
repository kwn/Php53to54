<?php

/**
 * Continue/Break syntax without variable
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Marcel Eichner // foobugs <marcel.eichner@foobugs.com>
 * @copyright 2012 foobugs oelke & eichner GbR
 * @license   BSD http://www.opensource.org/licenses/bsd-license.php
 * @link      https://github.com/foobugs/PHP53to54
 * @since     1.0-beta
 */

/**
 * Continue/Break syntax without variable
 *
 * Searches for break or continue statements that use parameters which is not
 * allowed anymore in PHP 5.4.
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Marcel Eichner // foobugs <marcel.eichner@foobugs.com>
 * @copyright 2012 foobugs oelke & eichner GbR
 * @license   BSD http://www.opensource.org/licenses/bsd-license.php
 * @link      https://github.com/foobugs/PHP53to54
 * @since     1.0-beta
 */
class PHP53to54_Sniffs_PHP_BreakContinueVarSyntaxSniff
implements PHP_CodeSniffer_Sniff
{
    /**
     * A list of tokenizers this sniff supports.
     *
     * @var array
     */
    public $supportedTokenizers = array(
        'PHP',
    );

    /**
     * Returns the token types that this sniff is interested in.
     *
     * @return array(int)
     * @see PHP_CodeSniffer_Sniff::register()
     */
    public function register()
    {
        return array( T_BREAK, T_CONTINUE, );
    }

    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
     * @param int                  $stackPtr  The position of the current token
     *                                         in the stack passed in $tokens.
     *
     * @return void
     * @see PHP_CodeSniffer_Sniff::process()
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        // iterate over next tokens and search for hints to variable usage
        // or method/function calls
        $nextSemicolonToken = $phpcsFile->findNext(
            T_SEMICOLON, ($stackPtr), null, false
        );
        if (!$nextSemicolonToken) {
            return false;
        }
        $curToken = $stackPtr;
        while (++$curToken < $nextSemicolonToken) {
            $token = $tokens[$curToken];
            $nextNotEmptyToken = $phpcsFile->findNext(
                PHP_CodeSniffer_Tokens::$emptyTokens, $curToken + 1,
                null, true
            );
            $nextToken = $tokens[$nextNotEmptyToken];

            $staticObjectMethodCall = $token['code'] == T_STRING
                && $nextToken['code'] == T_DOUBLE_COLON;
            $objectMethodCall = $token['code'] == T_STRING
                && $nextToken['code'] == T_OBJECT_OPERATOR;
            $functionCall = $token['code'] == T_STRING
                && $nextToken['code'] == T_OPEN_PARENTHESIS;
            $isVariable = $token['code'] == T_VARIABLE;

            if ($staticObjectMethodCall || $objectMethodCall || $functionCall) {
                $phpcsFile->addError(
                    'function calls in break/continue statements not supported',
                    $stackPtr
                );
                break;
            }
            if ($isVariable) {
                $phpcsFile->addError(
                    'break/continue with variable is not supported',
                    $stackPtr
                );
                break;
            }
        }
        return true;
    }
}