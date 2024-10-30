<?php
/**
 * Created by PhpStorm.
 * User: SP
 * Date: 06.03.2019
 * Time: 15:10
 */

namespace plugins\lootly\classes\base;


/**
 * Class Options
 * @package plugins\lootly\classes\base
 *
 * @property int locationsLastUpdateDate
 *
 */

class Options extends Base
{
    /**
     * Delete all plugin specific options from options table
     * @return void
     */
    public function clearOptions()
    {
        $table = lootly()->db->options;
        $query = "DELETE FROM `$table` WHERE option_name LIKE CONCAT ('_lootly_', '%')";
        lootly()->db->query($query);
    }

    /**
     * @param $optionName
     * @return mixed
     */
    public function getOption($optionName)
    {
        $key = "_lootly_" . $optionName;
        return get_option($key);
    }

    /**
     * @param string $optionName
     * @param mixed $optionValue
     */
    public function setOption($optionName, $optionValue)
    {
        $key = "_lootly_" . $optionName;
        update_option($key, $optionValue);
    }

    /**
     * @var Options
     */
    private static $_instance;

    /**
     * @return Options
     */
    public static function instance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Options constructor.
     *
     * @access private
     */
    private function __construct()
    {
    }

    /**
     * @access private
     */
    private function __clone()
    {
    }
}