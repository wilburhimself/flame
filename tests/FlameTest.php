<?php

namespace WilburHimself\Flame\Tests;

use WilburHimself\Flame\Tests\Fixtures\TestModel;
use WilburHimself\Flame\FlameResult;
use Mockery;
use stdClass;

class FlameTest extends TestCase
{
    /**
     * @var TestModel
     */
    protected $model;
    
    /**
     * @var Mockery\MockInterface
     */
    protected $dbBuilder;
    
    /**
     * @var Mockery\MockInterface
     */
    protected $dbQuery;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Create query builder mock
        $this->dbBuilder = Mockery::mock('CodeIgniter\Database\BaseBuilder');
        
        // Create query result mock
        $this->dbQuery = Mockery::mock('CodeIgniter\Database\ResultInterface');
        
        // Set up database mock expectations
        $this->db->shouldReceive('table')->andReturn($this->dbBuilder);
        
        // Set up builder mock to handle basic operations
        $this->dbBuilder->shouldReceive('select')->andReturnSelf();
        $this->dbBuilder->shouldReceive('where')->andReturnSelf();
        $this->dbBuilder->shouldReceive('whereIn')->andReturnSelf();
        $this->dbBuilder->shouldReceive('whereNotIn')->andReturnSelf();
        $this->dbBuilder->shouldReceive('get')->andReturn($this->dbQuery);
        $this->dbBuilder->shouldReceive('groupStart')->andReturnSelf();
        $this->dbBuilder->shouldReceive('groupEnd')->andReturnSelf();
        $this->dbBuilder->shouldReceive('orWhere')->andReturnSelf();
        $this->dbBuilder->shouldReceive('set')->andReturnSelf();
        
        // Set up query result mock
        $this->dbQuery->shouldReceive('getResult')->andReturn([]);
        $this->dbQuery->shouldReceive('getResultArray')->andReturn([]);
        
        // Create a partial mock of TestModel
        $this->model = Mockery::mock(TestModel::class, [$this->db, $this->validation])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
        
        // Set the builder
        $this->model->setBuilder($this->dbBuilder);
    }
    
    public function testGetReturnsFlameResultObject()
    {
        // Arrange
        $testData = (object)[
            'id' => 1,
            'name' => 'Test User',
            'email' => 'test@example.com'
        ];
        
        $this->model->shouldReceive('find')->with(1)->andReturn($testData);
        
        // Act
        $result = $this->model->get(1);
        
        // Assert
        $this->assertInstanceOf(FlameResult::class, $result);
        $this->assertEquals(1, $result->id);
        $this->assertEquals('Test User', $result->name);
    }
    
    public function testGetListWithConditions()
    {
        // Arrange
        $testResults = [
            (object)['id' => 1, 'name' => 'User 1'],
            (object)['id' => 2, 'name' => 'User 2']
        ];
        
        $this->model->shouldReceive('where')->with('status', 'active')->andReturnSelf();
        $this->model->shouldReceive('findAll')->andReturn($testResults);
        $this->model->shouldReceive('get')->with(1)->andReturn(new FlameResult($this->model, $testResults[0]));
        $this->model->shouldReceive('get')->with(2)->andReturn(new FlameResult($this->model, $testResults[1]));
        
        // Act
        $results = $this->model->get_list(['status' => 'active']);
        
        // Assert
        $this->assertIsArray($results);
        $this->assertCount(2, $results);
        $this->assertInstanceOf(FlameResult::class, $results[0]);
        $this->assertEquals(1, $results[0]->id);
    }
    
    public function testGetByField()
    {
        // Arrange
        $testResults = [
            (object)['id' => 1, 'name' => 'John', 'email' => 'john@example.com'],
            (object)['id' => 2, 'name' => 'John', 'email' => 'john2@example.com']
        ];
        
        $this->model->shouldReceive('where')->with('name', 'John')->andReturnSelf();
        $this->model->shouldReceive('findAll')->andReturn($testResults);
        $this->model->shouldReceive('get')->with(1)->andReturn(new FlameResult($this->model, $testResults[0]));
        $this->model->shouldReceive('get')->with(2)->andReturn(new FlameResult($this->model, $testResults[1]));
        
        // Act
        $results = $this->model->get_by('name', 'John');
        
        // Assert
        $this->assertIsArray($results);
        $this->assertCount(2, $results);
        $this->assertEquals('john@example.com', $results[0]->email);
        $this->assertEquals('john2@example.com', $results[1]->email);
    }
    
    public function testAddMethod()
    {
        // Arrange
        $testData = [
            'name' => 'New User',
            'email' => 'new@example.com'
        ];
        
        $this->model->shouldReceive('insert')->with($testData, true)->andReturn(5);
        
        // Act
        $result = $this->model->add($testData);
        
        // Assert
        $this->assertEquals(5, $result);
    }
    
    public function testUpdateMethod()
    {
        // Arrange
        $testData = [
            'name' => 'Updated User',
            'email' => 'updated@example.com'
        ];
        
        $this->model->shouldReceive('update')->with(1, $testData)->andReturn(true);
        
        // Act
        $result = $this->model->update(1, $testData);
        
        // Assert
        $this->assertTrue($result);
    }
    
    public function testDeleteMethod()
    {
        // Arrange
        $this->model->shouldReceive('delete')->with(1, false)->andReturn(true);
        
        // Act
        $result = $this->model->delete(1);
        
        // Assert
        $this->assertTrue($result);
    }
    
    public function testMagicCallFinderMethod()
    {
        // Arrange
        $this->model->setProperty('fieldNames', ['id', 'name', 'email']);
        
        // Mock the get_by method that will be called by the magic finder
        $this->model->shouldReceive('get_by')->with('email', 'test@example.com')->andReturn('finder result');
        
        // Act
        $result = $this->model->find_by_email('test@example.com');
        
        // Assert
        $this->assertEquals('finder result', $result);
    }
    
    public function testRelationshipMethods()
    {
        // Test belongs_to
        $this->model->belongs_to('department');
        $belongsTo = $this->model->getProperty('belongs_to');
        $this->assertContains('department', $belongsTo);
        
        // Test has_many
        $this->model->has_many('comments');
        $hasMany = $this->model->getProperty('has_many');
        $this->assertContains('comments', $hasMany);
        
        // Test has_and_belongs_to_many
        $this->model->has_and_belongs_to_many('tags');
        $habtm = $this->model->getProperty('has_and_belongs_to_many');
        $this->assertContains('tags', $habtm);
    }
    
    public function testGenerateFromPost()
    {
        // Set up a custom request mock for this test
        $requestMock = Mockery::mock();
        $requestMock->shouldReceive('getMethod')->andReturn('post');
        $requestMock->shouldReceive('getPost')->with('name')->andReturn('Posted Name');
        $requestMock->shouldReceive('getPost')->with('email')->andReturn('posted@example.com');
        $requestMock->shouldReceive('getPost')->with('id')->andReturn('1');
        $requestMock->shouldReceive('getPost')->with('status')->andReturn('active');
        
        // Use our helper method to set the custom mock
        $this->setRequestMock($requestMock);
        
        // Act
        $result = $this->model->generate_from_post();
        
        // Assert
        $this->assertEquals('Posted Name', $result->name);
        $this->assertEquals('posted@example.com', $result->email);
        $this->assertEquals('1', $result->id);
        $this->assertEquals('active', $result->status);
    }
}
