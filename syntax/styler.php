<?php
/**
 * Plugin Style: More styles for dokuwiki
 * Format: see README
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Ivan A-R <ivan@iar.spb.ru>
 * @page       http://iar.spb.ru/projects/doku/styler
 * @version    0.2
 */

if (!defined('DOKU_INC')) {
    define('DOKU_INC', realpath(dirname(__FILE__) . '/../../') . '/');
}
if (!defined('DOKU_PLUGIN')) {
    define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
}
require_once(DOKU_PLUGIN . 'syntax.php');

/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 */
class syntax_plugin_styler_styler extends DokuWiki_Syntax_Plugin
{
    public function getType()
    {
        return 'container';
    }

    public function getAllowedTypes()
    {
        return array('container', 'substition', 'protected', 'disabled', 'formatting', 'paragraphs');
    }

    public function getPType()
    {
        return 'stack';
    }

    public function getSort()
    {
        return 205;
    }

    public function connectTo($mode)
    {
        $this->Lexer->addEntryPattern('<style.*?>(?=.*?\x3C/style\x3E)', $mode, 'plugin_styler_styler');
        $this->Lexer->addEntryPattern('<quote.*?>(?=.*?\x3C/quote\x3E)', $mode, 'plugin_styler_styler');
        $this->Lexer->addEntryPattern('<epigraph.*?>(?=.*?\x3C/epigraph\x3E)', $mode, 'plugin_styler_styler');
    }

    public function postConnect()
    {
        $this->Lexer->addExitPattern('</style>', 'plugin_styler_styler');
        $this->Lexer->addExitPattern('</quote>', 'plugin_styler_styler');
        $this->Lexer->addExitPattern('</epigraph>', 'plugin_styler_styler');
    }


    /**
     * Handle the match
     */
    public function handle($match, $state, $pos, &$handler)
    {
        global $conf;
        switch ($state) {
            case DOKU_LEXER_ENTER:
                $match = str_replace(array('<', '>'), array('', ''), $match);
                $attrib = preg_split('/\s+/', strtolower($match));
                if ($attrib[0]) {
                    return array(array_shift($attrib), $state, $attrib);
                } else {
                    return array($match, $state, array());
                }
            case DOKU_LEXER_UNMATCHED:
                return array($match, $state, array());
            case DOKU_LEXER_EXIT:
                return array('', $state, array());
        }
        return array();
    }

    /**
     * Create output
     */
    public function render($mode, &$renderer, $data)
    {
        global $st;
        global $et;
        global $conf;
        global $prt;
        if ($mode == 'xhtml') {
            switch ($data[1]) {
                case DOKU_LEXER_ENTER:
                    $class = '';
                    foreach (
                        array(
                            'left',
                            'right',
                            'center',
                            'justify',
                            'box',
                            'float-left',
                            'float-right',
                            'background'
                        ) as $v
                    ) {
                        if (in_array($v, $data[2])) {
                            $class .= ' styler-' . $v;
                        }
                    }
                    $renderer->doc .= "</p>\n"; // It is hack
                    if ($data[0] == 'quote') {
                        $renderer->doc .= '<div class="styler-quote' . $class . '">';
                    } elseif ($data[0] == 'epigraph') {
                        $renderer->doc .= '<div class="epigraph' . $class . '">';
                    } else {
                        $renderer->doc .= '<div class="styler' . $class . '">';
                    }
                    break;
                case DOKU_LEXER_UNMATCHED:
                    $renderer->doc .= htmlspecialchars($data[0]);
                    break;
                case DOKU_LEXER_EXIT:
                    $renderer->doc .= "</div>\n<p>"; // "</p>" and "\n</p>" is hack
                    break;
            }
            return true;
        }
        return false;
    }
}
