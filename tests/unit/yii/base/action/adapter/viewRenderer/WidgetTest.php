<?php
/**
 * User: execut
 * Date: 14.07.16
 * Time: 13:33
 */

namespace execut\yii\base\action\adapter\viewRenderer;


use execut\TestCase;

class WidgetTest extends TestCase
{
    public function testRender() {
        $viewRenderer = new \execut\yii\base\action\adapter\viewRenderer\Widget();
        $viewRenderer->widget = [
            'class' => WidgetForTest::className(),
            'out' => 'test',
        ];

        $this->assertEquals('test', $viewRenderer->run());
    }

    public function testSetActionVarsInWidgetOptions() {
        $viewRenderer = new \execut\yii\base\action\adapter\viewRenderer\Widget();
        $viewRenderer->key = 'test';
        $viewRenderer->widget = [
            'key' => '{key}',
        ];
        $options = $viewRenderer->getWidgetOptions();
        $this->assertArrayHasKey('key', $options);
        $this->assertEquals('test', $options['key']);
    }

    public function testSetDefaultOptions() {
        $viewRenderer = $this->getMockBuilder(Widget::className())->setMethods(['getDefaultWidgetOptions'])
            ->getMock();
        $viewRenderer->expects($this->once())->method('getDefaultWidgetOptions')->willReturn([
                'defaultKey' => 'defaultValue'
            ]);

        $viewRenderer->widget = [
            'otherKey' => 'otherValue'
        ];

        $this->assertEquals([
            'defaultKey' => 'defaultValue',
            'otherKey' => 'otherValue'
        ], $viewRenderer->getWidgetOptions());
    }
}

class WidgetForTest extends \yii\base\Widget {
    public $out;
    public function run() {
        return $this->out;
    }
}