<?php
/**
* @package     
* @subpackage  debugbar_plugin
* @author      
* @copyright   
* @link        
* @licence     
*/


/**
 * plugin to show all sql queries into the debug bar
 */
class techinfoslogDebugbarPlugin implements jIDebugbarPlugin {

    /**
     * @return string CSS styles
     */
    function getCss() {
        return 'div.techinfoslog_phpinfoItem{ max-width: 600px; }';
    }

    /**
     * @return string Javascript code lines
     */
    function getJavascript() {
        return '';
    }

    /**
     * it should adds content or set some properties on the debugbar
     * to displays some contents.
     * @param debugbarHTMLResponsePlugin $debugbar the debugbar
     */
    function show($debugbar) {
        $info = new debugbarItemInfo('techinfos', 'Technical infos');
        $info->htmlLabel = '<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAItSURBVDjLfVM7bBNBEH27d7alOKfYjsM3gFLjRCAgiAoFBAIhQUNJh0SLqGgpEQW2a6LQ8VGgAAqUBqWk4bAbDEgoNCALJNtJlKDzfZaZ2bNFUJI9zc7d7c57b3ZmlTEGuw3f9x9HUXQjDEOXPMiL9ft99s/UTgDNZnOMAuYLhcL1XG4EAQUhSSC7KaZYLGBp6S3c7YIbjcYlDi6Xywfz+TxWvv8AsyeJQWISAjKICSwIAritViuI4zhLJpsGMtl3u93/JaPT6RJQggsXL8s/l4MnJw+j11sVdsOPYZVGjD+IE6XiGN68foWjlePCzmuigFE5+O68T9sUlKLZTuLZ1tfW8ODWKWH86L8Hq91/5ZpVwFKZlTcWS+PQWkOR6dT4nQFMYhkrMyfl3aRnoFkBfROAhuM4W0ynngcfHjP+9law0KtJWqIgTMujtILjukN28ZwCeVs5y7jw5RE21iNRIQA88YFwCsw4tWdE8rdD4edqlCqwjHfG7yEpWUAmFwCd5sn27ev2HeloRwBsL9hKDRVkMi7u3zwm5QnDCJubgTBksxlKw0j3aWXXYo5MyygKKK+Hy8vvzg4ahXzJ87wprk673Q5IXY5T47jK9AyOHDogivbtnZBm23IX6vX6bQK5Onv6zDnPK+Dli6d/qOZP6Hxm6f/0v13KRmufhwC1Wm2CSvZrbu48Rj2PNsRwHU2g1Y1qtTq6020dXiaS3iH7sLj4/MSg/1PGT7td97+G8aA4FJOt1wAAAABJRU5ErkJggg==" alt="SQL queries" title="SQL queries"/> ';

        $info->popupContent .= '<ul id="jxdb-techinfoslog" class="jxdb-list">';
        $memPeak = memory_get_peak_usage();
        $memPeakHuman = $this->size_readable( $memPeak );
        $info->popupContent .= "<li>Memory peak : $memPeakHuman ($memPeak o)</li>";

        $memLimit = get_cfg_var('memory_limit');
        $info->popupContent .= "<li>Memory limit : $memLimit</li>";
        
        $phpInfoVals = array(
                        INFO_GENERAL => 'phpinfo() general',
                        INFO_CONFIGURATION => 'phpinfo() configuration',
                        INFO_MODULES => 'phpinfo() modules',
                        INFO_ENVIRONMENT => 'phpinfo() environment',
                            );

        foreach( $phpInfoVals as $phpInfoWhat => $phpInfoWhatLabel ) {
            ob_start();
            phpinfo($phpInfoWhat);
            $phpInfoString = ob_get_contents();
            ob_end_clean();

            preg_match ('%<style type="text/css">(.*?)</style>.*?<body>(.*?)</body>%s', $phpInfoString, $matches);

            $phpInfoString = "<div class='phpinfodisplay'><style type='text/css'>\n".
                join( "\n",
                array_map(
                    create_function(
                        '$i',
                        'return ".phpinfodisplay " . preg_replace( "/,/", ",.phpinfodisplay ", $i );'
                    ),
                    preg_split( '/\n/', trim(preg_replace( "/\nbody/", "\n", $matches[1])) )
                )
            ).
            "</style>\n".
            $matches[2].
            "\n</div>\n";

            $info->popupContent .= '<li>
                <h5><a href="#" onclick="jxdb.toggleDetails(this);return false;"><span>'.htmlspecialchars($phpInfoWhatLabel).'</span></a></h5>
                <div class="techinfoslog_phpinfoItem">
                    <p>'.$phpInfoString.'</p>
                </div></li>';
        }

        $info->popupContent .= '</ul>';

        $debugbar->addInfo($info);
    }



    /**
     * Return human readable sizes
     *
     * @author      Aidan Lister <aidan@php.net>
     * @version     1.3.0
     * @link        http://aidanlister.com/2004/04/human-readable-file-sizes/
     * @param       int     $size        size in bytes
     * @param       string  $max         maximum unit
     * @param       string  $system      'si' for SI, 'bi' for binary prefixes
     * @param       string  $retstring   return string format
     */
    public function size_readable($size, $max = null, $system = 'si', $retstring = '%01.2f %s') {
        // Pick units
        $systems['si']['prefix'] = array('o', 'ko', 'Mo', 'Go', 'To', 'Po');
        $systems['si']['size']   = 1000;
        $systems['bi']['prefix'] = array('o', 'Kio', 'Mio', 'Gio', 'Tio', 'Pio');
        $systems['bi']['size']   = 1024;
        $sys = isset($systems[$system]) ? $systems[$system] : $systems['si'];

        // Max unit to display
        $depth = count($sys['prefix']) - 1;
        if ($max && false !== $d = array_search($max, $sys['prefix'])) {
            $depth = $d;
        }

        // Loop
        $i = 0;
        while ($size >= $sys['size'] && $i < $depth) {
            $size /= $sys['size'];
            $i++;
        }

        return str_replace( '.', ',', sprintf($retstring, $size, $sys['prefix'][$i]) );
    }

}
