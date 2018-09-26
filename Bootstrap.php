<?php
/**
 */

namespace execut\yii;


use yii\base\Application;
use yii\base\BaseObject;
use yii\base\BootstrapInterface;
use yii\helpers\ArrayHelper;
use yii\i18n\PhpMessageSource;

class Bootstrap extends BaseObject implements BootstrapInterface
{
    protected $_defaultDepends = [];
    protected $isBootstrapI18n = false;

    public function getDefaultDepends() {
        return $this->_defaultDepends;
    }

    protected $_depends = [
        'bootstrap' => [],
        'modules' => [],
        'components' => [],
    ];

    public function getDepends() {
        return ArrayHelper::merge($this->defaultDepends, $this->_depends);
    }

    public function setDepends($depends) {
        $this->_depends = $depends;
    }

    protected static $boostrapped = [];

    /**
     * Bootstrap method to be called during application bootstrap stage.
     * @param Application $app the application currently running
     */
    public function bootstrap($app)
    {
        if ($this->isBootstrapI18n) {
            $this->bootstrapI18n($app);
        }

        $bootstraps = [];
        foreach ($this->getDepends() as $key => $depends) {
            foreach ($depends as $name => $depend) {
                switch ($key) {
                    case 'bootstrap':
                        $bootstraps[] = $depend;
                    break;
                    case 'modules':
                        if (!$app->hasModule($name)) {
                            $app->setModule($name, $depend);
                        }
                    break;
                    case 'components':
                        if (!$app->has($name)) {
                            $app->set($name, $depend);
                        }
                    break;
                }
            }
        }

        foreach ($bootstraps as $bootstrap) {
            if (is_string($bootstrap)) {
                $module = $app->getModule($bootstrap);
                if ($module instanceof BootstrapInterface) {
                    $module->bootstrap($app);
                }
            } else {
                $bootstrapKey = $bootstrap['class'];
                if (array_key_exists('moduleId', $bootstrap)) {
                    $bootstrapKey .= '-' . $bootstrap['moduleId'];
                }

                if (in_array($bootstrapKey, self::$boostrapped)) {
                    continue;
                }

                self::$boostrapped[] = $bootstrap['class'];
                $bootstrap = \yii::createObject($bootstrap);
                $bootstrap->bootstrap($app);
            }
        }
    }

    public function bootstrapI18n($app) {
        $className = static::class;
        $reflector = new \ReflectionClass($className);
        $fileName = $reflector->getFileName();
        $fileName = dirname(dirname($fileName));
        $fileName = pathinfo($fileName, PATHINFO_BASENAME);
        $moduleName = explode('\\', $className)[1];
        $app->i18n->translations['execut/' . $moduleName] = [
            'class' => PhpMessageSource::class,
            'basePath' => '@vendor/execut/' . $fileName . '/messages',
            'sourceLanguage' => 'en-US',
            'fileMap' => [
                'execut/' . $moduleName => $moduleName . '.php',
            ],
        ];
    }
}