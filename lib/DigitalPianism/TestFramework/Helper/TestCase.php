<?php

class DigitalPianism_TestFramework_Helper_TestCase extends \PHPUnit_Framework_TestCase
{
    protected $setUp = [];
    protected $tearDown = [];

    public function setUp()
    {
        $this->addSetUpHook(10, 'mage-app', function () {
            Mage::app(static::$code, static::$type, static::$options);
        });

        $this->addSetUpHook(20, 'patch-mage-autoloader', function () {
            self::patchMagentoAutoloader();
        });

        $this->addSetUpHook(30, 'init-session', function () {
            $_SESSION = [];
        });

        $this->addSetUpHook(40, 'stub-mage-app-response', function () {
            Mage::app()->setResponse(new \DigitalPianism_TestFramework_Controller_HttpResponse);
        });

        if (method_exists($this, 'runDatabaseMigrations')) {
            $this->runDatabaseMigrations();
        }

        ksort($this->setUp);

        foreach ($this->setUp as $hooks) {
            foreach ($hooks as $hook) {
                call_user_func($hook);
            }
        }
    }

    public function tearDown()
    {
        $this->addTearDownHook(10, 'mage-reset', function () {
            Mage::reset();
        });

        $this->addTearDownHook(20, 'mage-reset-vars', function () {
            $this->mageResetVars();
        });

        ksort($this->tearDown);

        foreach ($this->tearDown as $hooks) {
            foreach ($hooks as $hook) {
                call_user_func($hook);
            }
        }
    }

    public function addSetUpHook($order, $id, $hook)
    {
        if (!isset($this->setUp[$order])) {
            $this->setUp[$order] = [];
        }

        $this->setUp[$order][$id] = $hook;
    }

    public function addTearDownHook($order, $id, $hook)
    {
        if (!isset($this->tearDown[$order])) {
            $this->tearDown[$order] = [];
        }

        $this->tearDown[$order][$id] = $hook;
    }

    protected function mageResetVars()
    {
        $this->clearProperty('Graffx_CGIM_Helper_Data', 'isEnabled');
        $this->clearProperty('Graffx_CGIM_Helper_Data', 'customerGroupId');
        $this->clearProperty('Graffx_CGIM_Helper_Data', 'inventoryGroupId');
        $this->clearProperty('Graffx_CGIM_Helper_Data', 'cachedInventoryGroupId');
    }

    protected function clearProperty($object, $property)
    {
        $reflect = new ReflectionProperty($object, $property);
        $reflect->setAccessible(true);

        if ($reflect->isStatic()) {
            $reflect->setValue(null);
        } else {
            $reflect->setValue($object, null);
        }
    }

    #
    # Static
    #

    protected static $code = '';
    protected static $type = 'store';
    protected static $options = [];

    public static function setCode($code)
    {
        static::$code = $code;
    }

    public static function setType($type)
    {
        static::$type = $type;
    }

    public static function setOptions($options)
    {
        static::$options = array_merge(
            ['config_model' => DigitalPianism_TestFramework_Model_Config::class],
            $options
        );
    }

    protected static function patchMagentoAutoloader()
    {
        $mageErrorHandler = set_error_handler(
            function () {
                return false;
            }
        );

        set_error_handler(
            function ($errno, $errstr, $errfile) use ($mageErrorHandler) {
                if (substr($errfile, -19) === 'Varien/Autoload.php') {
                    return null;
                }

                return is_callable($mageErrorHandler) ? call_user_func_array(
                    $mageErrorHandler,
                    func_get_args()
                ) : false;
            }
        );
    }
}
