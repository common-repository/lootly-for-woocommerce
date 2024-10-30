<?php

/**
 * Created by PhpStorm.
 * User: SP
 * Date: 06.03.2019
 * Time: 14:42
 */

namespace plugins\lootly\classes\base;

/**
 * Class Base
 * @package plugins\lootly\classes\base
 */
class Base
{
    protected $_options;
    protected $_api;
    protected $_endpoints;
    protected $_wooClasses;
    /**
     * @return string
     */
    public static function getClass()
    {
        return get_called_class();
    }

    /**
     * @param $property
     * @return mixed
     */
    public function __get($property)
    {
        $method = 'get' . ucwords($property);
        $property = '_'.$property;
        $this->$property = $this->$method();
        return $this->$property;
    }

    protected function getTemplate($template, $data = array(), $print = false)
    {
        $filepath = LOOTLY_PLUGIN_URL.'/templates/'.$template.'.php';
        $output = '';
        if(file_exists($filepath)){
            extract($data);
            ob_start();
            include $filepath;
            $output = ob_get_clean();
        }
        if($print){
            print $output;
        }
        return $output;
    }
}