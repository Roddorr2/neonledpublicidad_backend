<?php

namespace Tests\Unit;

use App\Jobs\BaseJob;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;
use Throwable;

class ConcreteJob extends BaseJob
{
    public function handle(): void
    {
        $this->logInfo('Concrete job executed');
    }
}

class BaseJobTest extends TestCase
{
    public function test_base_job_has_default_properties()
    {
        $job = new ConcreteJob();

        $this->assertEquals(3, $job->tries);
        $this->assertEquals(60, $job->backoff);
        $this->assertEquals(120, $job->timeout);
    }

    public function test_base_job_sets_job_name_correctly()
    {
        $job = new ConcreteJob();

        // Acceder a jobName via reflection
        $reflection = new \ReflectionClass($job);
        $property = $reflection->getProperty('jobName');
        $property->setAccessible(true);

        $this->assertEquals('ConcreteJob', $property->getValue($job));
    }

    public function test_log_info_method_works()
    {
        Log::shouldReceive('info')
            ->once();

        $job = new ConcreteJob();
        $job->handle();
    }

    public function test_log_warning_method()
    {
        Log::shouldReceive('warning')
            ->withArgs(function ($message) {
                return str_contains($message, '[ConcreteJob]');
            })
            ->once();

        $job = new ConcreteJob();
        $reflection = new \ReflectionMethod($job, 'logWarning');
        $reflection->setAccessible(true);
        $reflection->invoke($job, 'Warning test', []);
    }

    public function test_log_error_method()
    {
        Log::shouldReceive('error')
            ->withArgs(function ($message) {
                return str_contains($message, '[ConcreteJob]');
            })
            ->once();

        $job = new ConcreteJob();
        $reflection = new \ReflectionMethod($job, 'logError');
        $reflection->setAccessible(true);
        $reflection->invoke($job, 'Error test', []);
    }

    public function test_log_debug_method()
    {
        Log::shouldReceive('debug')
            ->withArgs(function ($message) {
                return str_contains($message, '[ConcreteJob]');
            })
            ->once();

        $job = new ConcreteJob();
        $reflection = new \ReflectionMethod($job, 'logDebug');
        $reflection->setAccessible(true);
        $reflection->invoke($job, 'Debug test', []);
    }

    public function test_should_retry_returns_true_when_under_limit()
    {
        $job = new ConcreteJob();
        $reflection = new \ReflectionMethod($job, 'shouldRetry');
        $reflection->setAccessible(true);

        $this->assertTrue($reflection->invoke($job, 1));
        $this->assertTrue($reflection->invoke($job, 2));
    }

    public function test_should_retry_returns_false_when_at_limit()
    {
        $job = new ConcreteJob();
        $reflection = new \ReflectionMethod($job, 'shouldRetry');
        $reflection->setAccessible(true);

        $this->assertFalse($reflection->invoke($job, 3));
        $this->assertFalse($reflection->invoke($job, 4));
    }

    public function test_get_retry_delay_returns_backoff()
    {
        $job = new ConcreteJob();
        $reflection = new \ReflectionMethod($job, 'getRetryDelay');
        $reflection->setAccessible(true);

        $this->assertEquals(60, $reflection->invoke($job, 1));
        $this->assertEquals(60, $reflection->invoke($job, 2));
    }

    public function test_get_job_info_returns_array()
    {
        $job = new ConcreteJob();
        $reflection = new \ReflectionMethod($job, 'getJobInfo');
        $reflection->setAccessible(true);

        $info = $reflection->invoke($job);

        $this->assertIsArray($info);
        $this->assertArrayHasKey('name', $info);
        $this->assertArrayHasKey('tries', $info);
        $this->assertArrayHasKey('timeout', $info);
        $this->assertArrayHasKey('backoff', $info);

        $this->assertEquals('ConcreteJob', $info['name']);
        $this->assertEquals(3, $info['tries']);
        $this->assertEquals(120, $info['timeout']);
        $this->assertEquals(60, $info['backoff']);
    }

    public function test_handle_failure_logs_error_with_exception_details()
    {
        Log::shouldReceive('error')
            ->withArgs(function ($message, $context) {
                return str_contains($message, 'Job execution failed') &&
                       isset($context['exception_class']) &&
                       isset($context['message']) &&
                       isset($context['file']);
            })
            ->once();

        $job = new ConcreteJob();
        $reflection = new \ReflectionMethod($job, 'handleFailure');
        $reflection->setAccessible(true);

        $exception = new \Exception('Test exception');
        $reflection->invoke($job, $exception);
    }

    public function test_failed_method_logs_permanently_failed()
    {
        Log::shouldReceive('error')
            ->withArgs(function ($message) {
                return str_contains($message, 'Job permanently failed');
            })
            ->once();

        $job = new ConcreteJob();
        $exception = new \Exception('Permanent failure');
        $job->failed($exception);
    }
}
