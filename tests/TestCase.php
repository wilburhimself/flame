<?php

namespace WilburHimself\Flame\Tests;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;

/**
 * Base TestCase for Flame library tests
 */
abstract class TestCase extends PHPUnitTestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var \Mockery\MockInterface
     */
    protected $db;

    /**
     * @var \Mockery\MockInterface
     */
    protected $validation;
    
    /**
     * @var \Mockery\MockInterface
     */
    protected $requestMock;

    /**
     * Sets up the test environment
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        // Define the 'helper' function mock
        if (!function_exists('helper')) {
            function_exists('helper') || eval('function helper($name) { return true; }');
        }
        
        // Define the 'singular' function mock
        if (!function_exists('singular')) {
            function_exists('singular') || eval('function singular($word) { return substr($word, 0, -1); }');
        }
        
        // Define the 'plural' function mock
        if (!function_exists('plural')) {
            function_exists('plural') || eval('function plural($word) { return $word . "s"; }');
        }
        
        // Create a request mock that can be overridden by individual tests
        $this->requestMock = Mockery::mock();
        $this->requestMock->shouldReceive('getMethod')->byDefault()->andReturn('post');
        $this->requestMock->shouldReceive('getPost')->byDefault()->andReturnUsing(function($key) {
            $postData = [
                'name' => 'Default Name',
                'email' => 'default@example.com',
                'status' => 'active'
            ];
            return $postData[$key] ?? '';
        });
        
        // Set the global service mock for the request service
        $GLOBALS['_request_mock'] = $this->requestMock;
        
        // Define the 'service' function mock
        if (!function_exists('service')) {
            function_exists('service') || eval('function service($name) { 
                if ($name === "request") {
                    return $GLOBALS["_request_mock"];
                }
                return Mockery::mock();
            }');
        }
        
        // Create mock DB object
        $this->db = Mockery::mock('CodeIgniter\Database\BaseConnection');
        
        // Create mock validation object
        $this->validation = Mockery::mock('CodeIgniter\Validation\Validation');
    }
    
    /**
     * Set a custom request mock for an individual test
     *
     * @param \Mockery\MockInterface $mock
     * @return void
     */
    protected function setRequestMock($mock)
    {
        $GLOBALS['_request_mock'] = $mock;
    }

    /**
     * Clean up the testing environment
     */
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
