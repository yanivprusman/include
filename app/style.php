<?php
class style{
    public static function contextMenu(){
        ?>
        <style>
            .context-menu {
                display: none;
                position: absolute;
                background-color: #f9f9f9;
                border: 1px solid #ccc;
                padding: 8px;
                z-index: 1000;
            }
            .context-menu ul {
                list-style: none;
                padding: 0;
                margin: 0;
            }
            .context-menu ul li {
                margin-bottom: 4px;
            }
            .context-menu ul li a {
                display: block;
                padding: 4px;
                text-decoration: none;
                color: #333;
            }
        </style>
        <?php
    }
}