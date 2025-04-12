<?php

namespace WilburHimself\Flame\Tests;

use WilburHimself\Flame\FlameResult;
use WilburHimself\Flame\Flame;
use Mockery;

class FlameResultTest extends TestCase
{
    protected $mockModel;
    
    protected function setUp(): void
    {
        parent::setUp();
        // Use partial mock with allowMockingNonExistentMethods to allow calling undefined methods
        $this->mockModel = Mockery::mock('WilburHimself\\Flame\\Flame')
            ->shouldAllowMockingProtectedMethods()
            ->makePartial()
            ->shouldIgnoreMissing();
    }
    
    public function testConstructorAssignsProperties()
    {
        // Arrange
        $testData = (object)[
            'id' => 1,
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ];
        
        // Act
        $result = new FlameResult($this->mockModel, $testData);
        
        // Assert
        $this->assertEquals(1, $result->id);
        $this->assertEquals('John Doe', $result->name);
        $this->assertEquals('john@example.com', $result->email);
    }
    
    public function testMagicCallInvokesCallable()
    {
        // Arrange
        $testFunc = function() {
            return 'called';
        };
        
        $testData = (object)[
            'id' => 1,
            'testMethod' => $testFunc
        ];
        
        // Act
        $result = new FlameResult($this->mockModel, $testData);
        $returnValue = $result->testMethod();
        
        // Assert
        $this->assertEquals('called', $returnValue);
    }
    
    public function testMagicCallInvokesModelMethod()
    {
        // Arrange
        $testData = (object)[
            'id' => 1
        ];
        
        $this->mockModel->shouldReceive('someModelMethod')
            ->once()
            ->andReturn('model method called');
        
        // Act
        $result = new FlameResult($this->mockModel, $testData);
        $returnValue = $result->someModelMethod();
        
        // Assert
        $this->assertEquals('model method called', $returnValue);
    }
    
    public function testMagicCallReturnsNullForNonExistentMethod()
    {
        // Arrange
        $testData = (object)[
            'id' => 1
        ];
        
        // Configure the mock to return null for nonExistentMethod
        $this->mockModel->shouldReceive('nonExistentMethod')
            ->once()
            ->andReturn(null);
            
        // Act
        $result = new FlameResult($this->mockModel, $testData);
        $returnValue = $result->nonExistentMethod();
        
        // Assert
        $this->assertNull($returnValue);
    }
}
