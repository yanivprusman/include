<?php
declare (strict_types=1);
namespace G_H_Projects;
// define('GHProjectsDir',"D:/125_xampp/htdocs/include/"); 
use G_H_Projects\G_h_projects_functions;
use G_H_Projects\Html;
include_once ("app/style.php");
class G_h_projects_include{
    public static string $g_h_outer_root;
    public static string $g_h_root=DIRECTORY_SEPARATOR . '101_include' . DIRECTORY_SEPARATOR;
    private static ?G_h_projects_include $instance=null;
    public function __construct(string $host = ''){
        if ($host===''){
            $host = $_SERVER['HTTP_HOST'];
        }
        //   $host = isset($_SERVER['HTTP_HOST'])?$_SERVER['HTTP_HOST']:'';
        $replacement = '';
        static::$g_h_outer_root =  'http://' . $host . str_replace('/var/www', $replacement, static::$g_h_root);
    }
    public static function getInstance(?string $g_h_root=null):?static{
        if (!static::$instance){
            if ($g_h_root) static::$g_h_root = $g_h_root;
            static::$instance = new static();
        }
        return static::$instance;
    }
    public function echo(bool $echo = true){
        ob_start();
        ?><head>
        <script src="https://cdn.jsdelivr.net/npm/js-beautify@1.13.5/js/lib/beautify.js"></script>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script>window.jQuery || document.write('<script src="offlineJS/jquery-3.7.1.js"><\/script>')</script>
        <script src='<?php echo G_h_projects_include::$g_h_outer_root . 'g_h_projects_js.js'?>'></script>
        <script src='<?php echo G_h_projects_include::$g_h_outer_root . 'transaction.js'?>'></script>
        <!-- <link id="g_h_projects_stylesheet" rel="stylesheet" type="text/css" href='<?php echo G_h_projects_include::$g_h_outer_root . 'g_h_projects_css.css'?>'>     -->
        </head>
        <div id="debugConsole" class="pre-box" style="display:none; border: 1px solid black;" contenteditable="true"></div>
        <script>
        document.addEventListener('keydown', function(event) {
        if (event.keyCode === 112 || event.key === 'F1') {
            get("debugConsole").toggle();
            // debug.toggle();
            event.preventDefault();
        };
        });
        function simulateF1(){
        window.addEventListener('load', function() {
                    var event = new KeyboardEvent('keydown', {
                        keyCode: 112,  // Key code for F1
                        key: 'F1'      // Key name for F1
                    });
                    document.dispatchEvent(event);
                });    
        }
        </script>
        <?php 
        $obGetClean = ob_get_clean();
        $html = new Html();
        echo $html->parseCodeWithIndentation(code:$obGetClean,sliceArray:false);
        }
    }
    $output=array();
    function readFiletoArray($filename="File1.php"){
    $file = fopen($filename, "r"); 
    $lines = array();
    $validLines = array();  
    while (!feof($file)) { 
        $line = fgets($file); 
        $lines[] = $line; 
    }
    fclose($file); 
    foreach ($lines as $line) {
        echo $line;
    }
}

