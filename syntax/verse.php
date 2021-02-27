<?php
/**
 * Plugin Style/Verse: More styles for dokuwiki
 * Format: see README
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Ivan A-R <ivan@iar.spb.ru>
 * @page       http://iar.spb.ru/en/projects/doku/styler
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
class syntax_plugin_styler_verse extends DokuWiki_Syntax_Plugin
{
    public function getType()
    {
        return 'protected';
    }

    public function getPType()
    {
        return 'block';
    }

    public function getSort()
    {
        return 205;
    }

    public function connectTo($mode)
    {
        $this->Lexer->addEntryPattern('<verse.*?>(?=.*?\x3C/verse\x3E)', $mode, 'plugin_styler_verse');
    }

    public function postConnect()
    {
        $this->Lexer->addExitPattern('</verse>', 'plugin_styler_verse');
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
                    $renderer->doc .= '<div class="verse' . $class . '">' . "\n" . '<pre>';
                    break;
                case DOKU_LEXER_UNMATCHED:
                    $result = preg_replace("/\b([A-H][#]?[m]?[75]?)\b/m", "<span>\\1</span>", $data[0]);
                    $renderer->doc .= htmlspecialchars($result);
                    break;
                case DOKU_LEXER_EXIT:
                    $renderer->doc .= "</pre>\n</div>";// "</p>" and "\n</p>" is hack
                    break;
            }
            return true;
        }
        return false;
    }
}
