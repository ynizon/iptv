<?php

namespace Tests\Unit;

use App\Models\Url;
use PHPUnit\Framework\TestCase;

class UrlTest extends TestCase
{
    public function test_the_stream_key_ignores_the_server_and_the_token(): void
    {
        $this->assertSame('movie:198273', Url::streamKey('http://154.70.152.44:80/movie/user/old-token/198273.mkv'));
        $this->assertSame('movie:198273', Url::streamKey('http://other-host.tv/movie/user/new-token/198273.mkv'));
        $this->assertSame('series:42', Url::streamKey('http://host/series/user/token/42.mp4'));
        $this->assertSame('live:1234', Url::streamKey('http://host:8080/user/token/1234'));
        $this->assertSame('live:1234', Url::streamKey('http://host/live/user/token/1234.ts'));
        $this->assertNull(Url::streamKey('https://cdn.example.com/out/v1/abc/index_2.m3u8'));
    }
}
