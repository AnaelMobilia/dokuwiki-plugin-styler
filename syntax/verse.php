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

/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 */
class syntax_plugin_styler_verse extends DokuWiki_Syntax_Plugin
{
    /**
     * Get the type of syntax this plugin defines.
     * @return string
     */
    public function getType()
    {
        return 'protected';
    }

    /**
     * Define how this plugin is handled regarding paragraphs.
     * @return string
     */
    public function getPType()
    {
        return 'block';
    }

    /**
     * Where to sort in?
     * @return int
     */
    public function getSort()
    {
        return 205;
    }

    /**
     * Connect lookup pattern to lexer.
     * @param $mode String The desired rendermode.
     */
    public function connectTo($mode)
    {
        $this->Lexer->addEntryPattern('<verse.*?>(?=.*?\x3C/verse\x3E)', $mode, 'plugin_styler_verse');
    }

    /**
     * Second pattern to say when the parser is leaving your syntax mode.
     */
    public function postConnect()
    {
        $this->Lexer->addExitPattern('</verse>', 'plugin_styler_verse');
    }

    /**
     * Handler to prepare matched data for the rendering process.
     * @param $match String The text matched by the patterns.
     * @param $state Integer The lexer state for the match.
     * @param $pos Integer The character position of the matched text.
     * @param $handler Doku_Handler Reference to the Doku_Handler object.
     * @return array
     */
    public function handle($match, $state, $pos, Doku_Handler $handler)
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
     * Handle the actual output creation.
     * @param $mode String The output format to generate.
     * @param $renderer Doku_Renderer A reference to the renderer object.
     * @param $data Array The data created by the handle() method.
     * @return bool
     */
    public function render($mode, Doku_Renderer $renderer, $data)
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
