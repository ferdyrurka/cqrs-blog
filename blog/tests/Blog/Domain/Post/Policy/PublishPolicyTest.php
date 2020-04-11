<?php
declare(strict_types=1);

namespace App\Tests\Blog\Domain\Post\Policy;

use App\Blog\Domain\Post\Exception\InvalidArgumentException;
use App\Blog\Domain\Post\Exception\RuntimeException;
use App\Blog\Domain\Post\Policy\PublishPolicy;
use App\Blog\Domain\Post\PostInformation;
use App\Blog\Domain\Post\Type\PublishType;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class PublishPolicyTest extends TestCase
{
    private PublishPolicy $publishPolicy;

    protected function setUp(): void
    {
        $this->publishPolicy = new PublishPolicy();
    }

    /**
     * @test
     */
    public function cronTypePlannedPublishAtIsNull(): void
    {
        $postInformation = new PostInformation(PublishType::CRON()->getValue(), null);

        $this->expectException(InvalidArgumentException::class);
        $this->publishPolicy->checkPublishWay($postInformation);
    }

    /**
     * @test
     */
    public function cronTypePlannedPublishAtIsYesterday(): void
    {
        $postInformation = new PostInformation(PublishType::CRON()->getValue(), Carbon::yesterday());

        $this->expectException(RuntimeException::class);
        $this->publishPolicy->checkPublishWay($postInformation);
    }

    /**
     * @test
     */
    public function cronTypePlannedPublishAtIsTomorrow(): void
    {
        $postInformation = new PostInformation(PublishType::CRON()->getValue(), Carbon::tomorrow());

        $postInformation = $this->publishPolicy->checkPublishWay($postInformation);
        $this->assertFalse($postInformation->isPublish());
    }

    /**
     * @test
     */
    public function nowType(): void
    {
        $postInformation = new PostInformation(PublishType::NOW()->getValue());

        $postInformation = $this->publishPolicy->checkPublishWay($postInformation);
        $this->assertTrue($postInformation->isPublish());
    }
}